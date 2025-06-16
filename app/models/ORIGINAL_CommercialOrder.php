<?php
// app/models/CommercialOrder.php

require_once __DIR__ . '/../config/database.php';

class CommercialOrder {
    private $conn;
    private $table_name = "commercial_orders";

    public $id;
    public $contact_id;
    public $commercial_user_id;
    public $order_date;
    public $status;
    public $expected_shipping_date;
    public $shipping_address;
    public $shipping_city;
    public $shipping_zip;
    public $shipping_province;
    public $carrier;
    public $shipping_costs;
    public $notes_commercial;
    public $notes_technical;
    public $total_amount; // Questo sarà aggiornato dopo l'inserimento degli item
    public $created_at;
    public $updated_at;

    // Proprietà aggiuntive per JOIN (non colonne della tabella commercial_orders)
    public $contact_first_name;
    public $contact_last_name;
    public $contact_company;
    public $commercial_username;
    public $company_address; // Dati cliente
    public $company_city;
    public $company_zip;
    public $company_province;
    public $pec;
    public $vat_number;
    public $tax_code;

    public function __construct() {
        $this->conn = getDbConnection();
    }

    /**
     * Crea un nuovo ordine commerciale.
     * @return array Un array con 'success' (bool) e 'id' (int dell'ordine creato) o 'error' (string).
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (
                    contact_id, commercial_user_id, order_date, status, expected_shipping_date, 
                    shipping_address, shipping_city, shipping_zip, shipping_province, carrier, 
                    shipping_costs, notes_commercial, notes_technical, total_amount
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        // Sanifica i dati, gestendo esplicitamente null e tipi numerici
        $this->contact_id = filter_var($this->contact_id, FILTER_VALIDATE_INT);
        $this->commercial_user_id = filter_var($this->commercial_user_id, FILTER_VALIDATE_INT);

        // Per i campi stringa e data nullable: se vuoti, impostali a null. Altrimenti, sanifica.
        $this->order_date = !empty($this->order_date) ? htmlspecialchars(strip_tags($this->order_date)) : null;
        $this->status = !empty($this->status) ? htmlspecialchars(strip_tags($this->status)) : 'Ordine Inserito';
        $this->expected_shipping_date = !empty($this->expected_shipping_date) ? htmlspecialchars(strip_tags($this->expected_shipping_date)) : null;
        $this->shipping_address = !empty($this->shipping_address) ? htmlspecialchars(strip_tags($this->shipping_address)) : null;
        $this->shipping_city = !empty($this->shipping_city) ? htmlspecialchars(strip_tags($this->shipping_city)) : null;
        $this->shipping_zip = !empty($this->shipping_zip) ? htmlspecialchars(strip_tags($this->shipping_zip)) : null;
        $this->shipping_province = !empty($this->shipping_province) ? htmlspecialchars(strip_tags($this->shipping_province)) : null;
        $this->carrier = !empty($this->carrier) ? htmlspecialchars(strip_tags($this->carrier)) : null;
        $this->notes_commercial = !empty($this->notes_commercial) ? htmlspecialchars(strip_tags($this->notes_commercial)) : null;
        $this->notes_technical = !empty($this->notes_technical) ? htmlspecialchars(strip_tags($this->notes_technical)) : null;

        // Per i campi numerici: converti a float, default a 0.00 se invalido o null
        $this->shipping_costs = filter_var($this->shipping_costs, FILTER_VALIDATE_FLOAT) !== false ? floatval($this->shipping_costs) : 0.00;
        $this->total_amount = filter_var($this->total_amount, FILTER_VALIDATE_FLOAT) !== false ? floatval($this->total_amount) : 0.00;

        // Collega i parametri allo statement preparato.
        // Tipi: 2xINT (contact_id, commercial_user_id), 8xSTRING (dates, addresses, carrier, notes), 1xDOUBLE (shipping_costs), 2xSTRING (notes), 1xDOUBLE (total_amount)
        // Order: contact_id, commercial_user_id, order_date, status, expected_shipping_date, shipping_address, shipping_city, shipping_zip, shipping_province, carrier, shipping_costs, notes_commercial, notes_technical, total_amount
        // Stringa di tipi: i i s s s s s s s s d s s d
        $stmt->bind_param(
            "iisssssssssssd", // 14 parametri: 2 int, 10 string, 2 double
            $this->contact_id,
            $this->commercial_user_id,
            $this->order_date,
            $this->status,
            $this->expected_shipping_date,
            $this->shipping_address,
            $this->shipping_city,
            $this->shipping_zip,
            $this->shipping_province,
            $this->carrier,
            $this->shipping_costs,
            $this->notes_commercial,
            $this->notes_technical,
            $this->total_amount
        );

        if ($stmt->execute()) {
            $last_id = $stmt->insert_id;
            $stmt->close();
            return ['success' => true, 'id' => $last_id];
        }
        error_log("Errore creazione ordine: " . $stmt->error);
        $error_message = $stmt->error;
        $stmt->close();
        return ['success' => false, 'error' => $error_message];
    }

    /**
     * Legge tutti gli ordini commerciali, con filtri di ricerca e stato, e basati sul ruolo utente.
     * @param int|null $current_user_id L'ID dell'utente corrente per filtrare i propri ordini.
     * @param string|null $current_user_role Il ruolo dell'utente corrente.
     * @param string $search_query Query di ricerca.
     * @param string $filter_status Stato da filtrare ('Tutti' per nessun filtro).
     * @return array Array di ordini.
     */
    public function readAll($current_user_id = null, $current_user_role = null, $search_query = '', $filter_status = '') {
        $query = "SELECT co.*, c.first_name as contact_first_name, c.last_name as contact_last_name, c.company, u.username as commercial_username
                  FROM " . $this->table_name . " co
                  JOIN contacts c ON co.contact_id = c.id
                  JOIN users u ON co.commercial_user_id = u.id";
        
        $conditions = [];
        $bind_values = [];
        $types = "";

        // Filtro per commerciale: vede solo i propri ordini
        if ($current_user_role === 'commerciale' && $current_user_id !== null) {
            $conditions[] = "co.commercial_user_id = ?";
            $types .= "i";
            $bind_values[] = &$current_user_id;
        }

        // Filtro per stato
        if (!empty($filter_status) && $filter_status !== 'Tutti') {
            $conditions[] = "co.status = ?";
            $types .= "s";
            $bind_values[] = &$filter_status;
        }

        // Filtro di ricerca testuale
        if (!empty($search_query)) {
            $search_term = "%" . $search_query . "%";
            $search_conditions = "(co.id LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR c.company LIKE ? OR co.status LIKE ? OR u.username LIKE ?)";
            $conditions[] = $search_conditions;
            $types .= "ssssss"; // 6 's' per i LIKE
            $bind_values[] = &$search_term;
            $bind_values[] = &$search_term;
            $bind_values[] = &$search_term;
            $bind_values[] = &$search_term;
            $bind_values[] = &$search_term;
            $bind_values[] = &$search_term;
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $query .= " ORDER BY co.order_date DESC, co.id DESC";

        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Errore nella preparazione della query in CommercialOrder->readAll: " . $this->conn->error);
            return [];
        }

        if (!empty($bind_values)) {
            call_user_func_array([$stmt, 'bind_param'], array_merge([$types], $bind_values));
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        $stmt->close();
        return $orders;
    }

    /**
     * Legge un singolo ordine commerciale, con i dettagli del contatto e dell'utente commerciale.
     * @param int $id L'ID dell'ordine da leggere.
     * @return array|null Un array contenente i dati dell'ordine se trovato, altrimenti null.
     */
    public function readOne($id) {
        $query = "SELECT co.*, c.first_name as contact_first_name, c.last_name as contact_last_name, c.company, c.company_address, c.company_city, c.company_zip, c.company_province, c.pec, c.vat_number, c.tax_code, u.username as commercial_username
                  FROM " . $this->table_name . " co
                  JOIN contacts c ON co.contact_id = c.id
                  JOIN users u ON co.commercial_user_id = u.id
                  WHERE co.id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        $stmt->close();
        return $order;
    }

    /**
     * Aggiorna un ordine commerciale esistente.
     * @return array Un array con 'success' (bool) e 'error' (string, se presente).
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET
                    contact_id = ?, order_date = ?, status = ?, expected_shipping_date = ?, 
                    shipping_address = ?, shipping_city = ?, shipping_zip = ?, shipping_province = ?, 
                    carrier = ?, shipping_costs = ?, notes_commercial = ?, notes_technical = ?, total_amount = ?
                WHERE id = ?"; // commercial_user_id non viene aggiornato qui

        $stmt = $this->conn->prepare($query);

        // Sanifica i dati (stessa logica di create)
        $this->contact_id = filter_var($this->contact_id, FILTER_VALIDATE_INT);
        $this->id = filter_var($this->id, FILTER_VALIDATE_INT); // ID per la clausola WHERE

        $this->order_date = !empty($this->order_date) ? htmlspecialchars(strip_tags($this->order_date)) : null;
        $this->status = !empty($this->status) ? htmlspecialchars(strip_tags($this->status)) : 'Ordine Inserito';
        $this->expected_shipping_date = !empty($this->expected_shipping_date) ? htmlspecialchars(strip_tags($this->expected_shipping_date)) : null;
        $this->shipping_address = !empty($this->shipping_address) ? htmlspecialchars(strip_tags($this->shipping_address)) : null;
        $this->shipping_city = !empty($this->shipping_city) ? htmlspecialchars(strip_tags($this->shipping_city)) : null;
        $this->shipping_zip = !empty($this->shipping_zip) ? htmlspecialchars(strip_tags($this->shipping_zip)) : null;
        $this->shipping_province = !empty($this->shipping_province) ? htmlspecialchars(strip_tags($this->shipping_province)) : null;
        $this->carrier = !empty($this->carrier) ? htmlspecialchars(strip_tags($this->carrier)) : null;
        $this->notes_commercial = !empty($this->notes_commercial) ? htmlspecialchars(strip_tags($this->notes_commercial)) : null;
        $this->notes_technical = !empty($this->notes_technical) ? htmlspecialchars(strip_tags($this->notes_technical)) : null;
        
        $this->shipping_costs = filter_var($this->shipping_costs, FILTER_VALIDATE_FLOAT) !== false ? floatval($this->shipping_costs) : 0.00;
        $this->total_amount = filter_var($this->total_amount, FILTER_VALIDATE_FLOAT) !== false ? floatval($this->total_amount) : 0.00;


        // Collega i parametri allo statement preparato.
        // Tipi: 1xINT (contact_id), 8xSTRING (dates, addresses, carrier, notes), 1xDOUBLE (shipping_costs), 2xSTRING (notes), 1xDOUBLE (total_amount), 1xINT (id)
        // Order: contact_id, order_date, status, expected_shipping_date, shipping_address, shipping_city, shipping_zip, shipping_province, carrier, shipping_costs, notes_commercial, notes_technical, total_amount, id
        // Stringa di tipi: i s s s s s s s s d s s d i
        $stmt->bind_param(
            "issssssssdsdi", // 14 parametri per SET + 1 per WHERE = 15 parametri: 2 int, 10 string, 2 double
            $this->contact_id,
            $this->order_date,
            $this->status,
            $this->expected_shipping_date,
            $this->shipping_address,
            $this->shipping_city,
            $this->shipping_zip,
            $this->shipping_province,
            $this->carrier,
            $this->shipping_costs,
            $this->notes_commercial,
            $this->notes_technical,
            $this->total_amount,
            $this->id
        );

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true];
        }
        error_log("Errore aggiornamento ordine: " . $stmt->error);
        $error_message = $stmt->error;
        $stmt->close();
        return ['success' => false, 'error' => $error_message];
    }

    /**
     * Elimina un ordine commerciale e i suoi item associati (grazie a ON DELETE CASCADE).
     * @param int $id L'ID dell'ordine da eliminare.
     * @return bool True in caso di successo, False in caso di errore.
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }
        error_log("Errore eliminazione ordine: " . $stmt->error);
        $stmt->close();
        return false;
    }

    /**
     * Aggiorna il campo total_amount di un ordine specifico sommando il totale degli item.
     * Chiamato dopo ogni aggiunta/modifica/eliminazione di item.
     * @param int $order_id L'ID dell'ordine da aggiornare.
     * @return bool True in caso di successo, False in caso di errore.
     */
    public function updateTotalAmount($order_id) {
        // Calcola la somma degli item_total per l'ordine
        $query_sum = "SELECT SUM(ordered_quantity * ordered_unit_price) as items_total FROM commercial_order_items WHERE order_id = ?";
        $stmt_sum = $this->conn->prepare($query_sum);
        $stmt_sum->bind_param("i", $order_id);
        $stmt_sum->execute();
        $result_sum = $stmt_sum->get_result();
        $row_sum = $result_sum->fetch_assoc();
        $items_total = $row_sum['items_total'] ?? 0.00;
        $stmt_sum->close();

        // Recupera i costi di spedizione dell'ordine
        $query_shipping = "SELECT shipping_costs FROM " . $this->table_name . " WHERE id = ?";
        $stmt_shipping = $this->conn->prepare($query_shipping);
        $stmt_shipping->bind_param("i", $order_id);
        $stmt_shipping->execute();
        $result_shipping = $stmt_shipping->get_result();
        $row_shipping = $result_shipping->fetch_assoc();
        $shipping_costs = $row_shipping['shipping_costs'] ?? 0.00;
        $stmt_shipping->close();

        // Calcola il totale finale
        $new_total_amount = $items_total + $shipping_costs;

        // Aggiorna il campo total_amount nell'ordine
        $query_update = "UPDATE " . $this->table_name . " SET total_amount = ? WHERE id = ?";
        $stmt_update = $this->conn->prepare($query_update);
        $stmt_update->bind_param("di", $new_total_amount, $order_id);

        if ($stmt_update->execute()) {
            $stmt_update->close();
            return true;
        }
        error_log("Errore aggiornamento total_amount per ordine ID " . $order_id . ": " . $stmt_update->error);
        $stmt_update->close();
        return false;
    }

    /**
     * Valida i campi di un ordine commerciale.
     * @param array $data Dati da validare.
     * @param bool $is_edit_mode True se la validazione è per una modifica.
     * @return array Array di stringhe di errore, vuoto se valido.
     */
    public function validate($data, $is_edit_mode = false) {
        $errors = [];

        if (empty($data['contact_id'])) {
            $errors[] = "Il cliente è obbligatorio.";
        }
        if (empty($data['order_date'])) {
            $errors[] = "La data dell'ordine è obbligatoria.";
        } else if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $data['order_date'])) {
            $errors[] = "La data dell'ordine non è nel formato corretto (YYYY-MM-DD).";
        }
        if (empty($data['status'])) {
            $errors[] = "Lo stato dell'ordine è obbligatorio.";
        } else {
            $valid_statuses = [
                'Ordine Inserito', 'In Preparazione', 'Pronto per Spedizione',
                'Spedito', 'Fatturato', 'Annullato', 'In Attesa di Pagamento', 'Pagato'
            ];
            if (!in_array($data['status'], $valid_statuses)) {
                $errors[] = "Stato dell'ordine non valido.";
            }
        }

        if (!empty($data['expected_shipping_date']) && !preg_match("/^\d{4}-\d{2}-\d{2}$/", $data['expected_shipping_date'])) {
            $errors[] = "La data di spedizione prevista non è nel formato corretto (YYYY-MM-DD).";
        }

        if (!empty($data['shipping_zip']) && !preg_match("/^\d{5}$/", $data['shipping_zip'])) {
            $errors[] = "Il CAP di spedizione deve contenere esattamente 5 cifre numeriche.";
        }
        if (!empty($data['shipping_province']) && !preg_match("/^[A-Z]{2}$/", $data['shipping_province'])) {
            $errors[] = "La provincia di spedizione deve contenere esattamente 2 lettere maiuscole (es. RM).";
        }

        if (isset($data['shipping_costs']) && (!is_numeric($data['shipping_costs']) || floatval($data['shipping_costs']) < 0)) {
            $errors[] = "I costi di spedizione devono essere un numero non negativo.";
        }

        return $errors;
    }

    public function closeConnection() {
        if ($this->conn && $this->conn->ping()) {
            $this->conn->close();
        }
    }
}
