<?php
// app/models/CommercialOrder.php

require_once __DIR__ . '/../config/database.php';

class CommercialOrder {
    private $conn;
    private $table_name = "commercial_orders";

    // Proprietà dell'oggetto, allineate alla tabella `commercial_orders` con shipping_address_id
    public $id;
    public $contact_id;
    public $commercial_user_id;
    public $shipping_address_id; // Riferimento all'ID dell'indirizzo di spedizione (dalla tabella contact_addresses)
    public $order_date;
    public $status;
    public $expected_shipping_date;
    public $carrier;
    public $shipping_costs;
    public $notes_commercial;
    public $notes_technical;
    public $total_amount;
    public $created_at;
    public $updated_at;
	
    public function __construct() {
        $this->conn = getDbConnection();
    }

	
    /**
     * Crea un nuovo ordine commerciale nel database.
     * @return array Un array con 'success' (bool) e 'error' (string, se presente).
     */
public function readAll($current_user_id = null, $current_user_role = null, $search_query = '') {
    $query = "SELECT co.*, ca.address as shipping_address_address, ca.city as shipping_address_city, 
              ca.zip as shipping_address_zip, ca.province as shipping_address_province, 
              ca.address_type as shipping_address_type
              FROM commercial_orders co
              JOIN contact_addresses ca ON co.shipping_address_id = ca.id";

    $conditions = [];  // ✅ Ora è dentro la funzione!
    $bind_values = [];
    $types = "";


$conditions = [];
$bind_values = [];
$types = "";

// Filtro per Sales: vede solo i propri ordini
if ($current_user_role === 'Sales' && $current_user_id !== null) {
    $conditions[] = "co.contact_id = ?";
    $types .= "i";
    $bind_values[] = &$current_user_id;
}

// Filtri di ricerca
if (!empty($search_query)) {
    $search_term = "%" . $search_query . "%";
    $search_conditions = "(c.first_name LIKE ? OR co.status LIKE ? OR co.notes_commercial LIKE ? OR co.notes_technical LIKE ?)";
    $conditions[] = $search_conditions;
    $types .= "ssss";
    $bind_values[] = &$search_term;
    $bind_values[] = &$search_term;
    $bind_values[] = &$search_term;
    $bind_values[] = &$search_term;
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

// **Eliminato ORDER BY duplicato**
$query .= " ORDER BY co.order_date DESC, co.created_at DESC";

error_log("Query generata: " . $query);
die("Query generata: " . $query);

$stmt = $this->conn->prepare($query);
if ($stmt === false) {
    error_log("Errore nella preparazione della query: " . $this->conn->error);
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


    /**
     * Aggiorna un ordine commerciale esistente.
     * @return array Un array con 'success' (bool) e 'error' (string, se presente).
     */
 public function update() {
    $query = "UPDATE " . $this->table_name . " SET
                contact_id = ?, 
                shipping_address_id = ?, 
                order_date = ?, 
                status = ?, 
                expected_shipping_date = ?, 
                carrier = ?, 
                shipping_costs = ?, 
                notes_commercial = ?, 
                notes_technical = ?, 
                total_amount = ?
              WHERE id = ?";

    $stmt = $this->conn->prepare($query);
}

        // Sanifica e prepara i dati per il binding.
        $this->contact_id = filter_var($this->contact_id, FILTER_VALIDATE_INT);
        $this->commercial_user_id = filter_var($this->commercial_user_id, FILTER_VALIDATE_INT);
        $this->shipping_address_id = filter_var($this->shipping_address_id, FILTER_VALIDATE_INT) ?: null;
        $this->order_date = htmlspecialchars(strip_tags($this->order_date));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->expected_shipping_date = !empty($this->expected_shipping_date) ? htmlspecialchars(strip_tags($this->expected_shipping_date)) : null;
        $this->carrier = !empty($this->carrier) ? htmlspecialchars(strip_tags($this->carrier)) : null;
        $this->shipping_costs = !empty($this->shipping_costs) ? floatval($this->shipping_costs) : null;
        $this->notes_commercial = !empty($this->notes_commercial) ? htmlspecialchars(strip_tags($this->notes_commercial)) : null;
        $this->notes_technical = !empty($this->notes_technical) ? htmlspecialchars(strip_tags($this->notes_technical)) : null;
        $this->total_amount = floatval($this->total_amount);
        $this->id = filter_var($this->id, FILTER_VALIDATE_INT);

        // Collega i parametri. Tipi: integer (contact_id), integer (commercial_user_id), integer (shipping_address_id, nullable),
        // string (order_date), string (status), string (expected_shipping_date, nullable), string (carrier, nullable),
        // double (shipping_costs, nullable), string (notes_commercial, nullable), string (notes_technical, nullable),
        // double (total_amount), integer (id)
        // Stringa di tipi: 'iiissssdssdi' = 12 parametri
        $stmt->bind_param("iiissssdssdi",
            $this->contact_id,
            $this->commercial_user_id,
            $this->shipping_address_id,
            $this->order_date,
            $this->status,
            $this->expected_shipping_date,
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
        error_log("Errore aggiornamento ordine commerciale: " . $stmt->error);
        $error_message = $stmt->error;
        $stmt->close();
        return ['success' => false, 'error' => $error_message];
    }

    /**
     * Elimina un ordine commerciale e i suoi articoli associati (grazie a ON DELETE CASCADE).
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
        error_log("Errore eliminazione ordine commerciale: " . $stmt->error);
        $stmt->close();
        return false;
    }

    /**
     * Valida i dati di un ordine commerciale.
     * @param array $data Dati da validare.
     * @param bool $is_edit_mode Indica se la validazione è per una modifica.
     * @return array Array di stringhe di errore, vuoto se valido.
     */
    public function validate($data, $is_edit_mode = false) {
        $errors = [];

        if (empty($data['contact_id'])) {
            $errors[] = "È obbligatorio selezionare un cliente per l'ordine.";
        }
        if (empty($data['order_date'])) {
            $errors[] = "La data dell'ordine è obbligatoria.";
        }
        if (empty($data['status'])) {
            $errors[] = "Lo stato dell'ordine è obbligatorio.";
        } else {
            $valid_statuses = ['Ordine Inserito', 'In Preparazione', 'Parzialmente Spedito', 'Pronto per Spedizione', 'Spedito', 'Fatturato', 'Annullato', 'In Attesa di Pagamento', 'Pagato'];
            if (!in_array($data['status'], $valid_statuses)) {
                $errors[] = "Stato dell'ordine non valido.";
            }
        }

        if (!empty($data['shipping_costs']) && (!is_numeric($data['shipping_costs']) || floatval($data['shipping_costs']) < 0)) {
            $errors[] = "I costi di spedizione devono essere un numero positivo.";
        }
        
        if (!empty($data['total_amount']) && (!is_numeric($data['total_amount']) || floatval($data['total_amount']) < 0)) {
            $errors[] = "Il totale dell'ordine deve essere un numero positivo.";
        }
        
        // Validazione date (formato YYYY-MM-DD)
        $date_fields = ['order_date', 'expected_shipping_date'];
        foreach ($date_fields as $field) {
            if (!empty($data[$field]) && !preg_match("/^\d{4}-\d{2}-\d{2}$/", $data[$field])) {
                $errors[] = "Il formato della data per '" . str_replace('_', ' ', $field) . "' non è valido (YYYY-MM-DD).";
            }
        }

        // Logica date: data di spedizione prevista non può essere prima della data ordine
        if (!empty($data['order_date']) && !empty($data['expected_shipping_date']) && strtotime($data['expected_shipping_date']) < strtotime($data['order_date'])) {
            $errors[] = "La data di spedizione prevista non può essere precedente alla data dell'ordine.";
        }

        return $errors;
    }

    /**
     * Ricalcola il total_amount di un ordine basandosi sulla somma degli item.
     * Chiamato dopo l'aggiunta/modifica/eliminazione degli item.
     * @param int $order_id L'ID dell'ordine da ricalcolare.
     */
    public function recalculateTotalAmount($order_id) {
        $query = "UPDATE " . $this->table_name . " co
                  SET co.total_amount = (
                      SELECT SUM(coi.ordered_item_total)
                      FROM commercial_order_items coi
                      WHERE coi.order_id = ?
                  )
                  WHERE co.id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $order_id, $order_id);
        
        if (!$stmt->execute()) {
            error_log("Errore nel ricalcolo del totale ordine per ID " . $order_id . ": " . $stmt->error);
        }
        $stmt->close();
    }


    public function closeConnection() {
        if ($this->conn && $this->conn->ping()) {
            $this->conn->close();
        }
    }
}
