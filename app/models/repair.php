<?php
// app/models/Repair.php

// Includi il file di configurazione del database per la funzione di connessione
require_once __DIR__ . '/../config/database.php';

class Repair {
    private $conn; // Variabile per la connessione al database
    private $table_name = "repairs"; // Nome della tabella delle riparazioni

    // Proprietà dell'oggetto Repair, corrispondenti alle colonne della tabella 'repairs'
    public $id;
    public $contact_id;
    public $user_id; // ID dell'utente che ha creato la riparazione
    public $device_type;
    public $brand;
    public $model;
    public $serial_number;
    public $problem_description;
    public $accessories;
    public $reception_date;
    public $ddt_number;
    public $ddt_date;
    public $status;
    public $technician_notes;
    public $estimated_cost;
    public $completion_date;
    public $shipping_date;
    public $tracking_code;
    public $created_at;

    // Proprietà extra per join (non colonne della tabella repairs)
    public $contact_first_name;
    public $contact_last_name;
    public $contact_company;
    public $user_username; // Username dell'utente che ha creato la riparazione


    /**
     * Costruttore della classe Repair.
     * Inizializza la connessione al database.
     */
    public function __construct() {
        $this->conn = getDbConnection();
    }

    /**
     * Crea una nuova riparazione nel database.
     * I dati della riparazione devono essere stati assegnati alle proprietà dell'oggetto prima di chiamare questo metodo.
     * @return array Un array con 'success' (bool) e 'error' (string, se presente).
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (
                    contact_id, user_id, device_type, brand, model, serial_number, 
                    problem_description, accessories, reception_date, ddt_number, 
                    ddt_date, status, technician_notes, estimated_cost, 
                    completion_date, shipping_date, tracking_code
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; // Aggiunto user_id
        $stmt = $this->conn->prepare($query);

        // Sanifica i dati. I campi NULLABLE possono essere vuoti, ma se valorizzati vanno sanificati.
        // Utilizziamo un operatore ternario per impostare a NULL le stringhe vuote, 
        // in modo che il bind_param le gestisca correttamente come NULL nel DB.
        $this->contact_id = filter_var($this->contact_id, FILTER_VALIDATE_INT); // Assicurati che sia un intero
        $this->user_id = filter_var($this->user_id, FILTER_VALIDATE_INT) ?: null; // NUOVO: Assicurati che sia int o null
        $this->device_type = !empty($this->device_type) ? htmlspecialchars(strip_tags($this->device_type)) : null;
        $this->brand = !empty($this->brand) ? htmlspecialchars(strip_tags($this->brand)) : null;
        $this->model = !empty($this->model) ? htmlspecialchars(strip_tags($this->model)) : null;
        $this->serial_number = !empty($this->serial_number) ? htmlspecialchars(strip_tags($this->serial_number)) : null;
        $this->problem_description = !empty($this->problem_description) ? htmlspecialchars(strip_tags($this->problem_description)) : null;
        $this->accessories = !empty($this->accessories) ? htmlspecialchars(strip_tags($this->accessories)) : null;
        $this->reception_date = !empty($this->reception_date) ? htmlspecialchars(strip_tags($this->reception_date)) : null;
        $this->ddt_number = !empty($this->ddt_number) ? htmlspecialchars(strip_tags($this->ddt_number)) : null;
        $this->ddt_date = !empty($this->ddt_date) ? htmlspecialchars(strip_tags($this->ddt_date)) : null;
        $this->status = !empty($this->status) ? htmlspecialchars(strip_tags($this->status)) : 'In Attesa'; // Status ha un default
        $this->technician_notes = !empty($this->technician_notes) ? htmlspecialchars(strip_tags($this->technician_notes)) : null;
        $this->estimated_cost = !empty($this->estimated_cost) ? floatval($this->estimated_cost) : null; // Converti a float
        $this->completion_date = !empty($this->completion_date) ? htmlspecialchars(strip_tags($this->completion_date)) : null;
        $this->shipping_date = !empty($this->shipping_date) ? htmlspecialchars(strip_tags($this->shipping_date)) : null;
        $this->tracking_code = !empty($this->tracking_code) ? htmlspecialchars(strip_tags($this->tracking_code)) : null;

        // Collega i parametri allo statement preparato. (17 parametri)
        // Usiamo 'd' per DECIMAL/float, 'i' per INT, 's' per VARCHAR/TEXT/DATE che possono essere stringhe o NULL
        $stmt->bind_param("iisssssssssssdsss", // 2 (int) + 14 (string/null) + 1 (float) = 17 parametri
            $this->contact_id,
            $this->user_id, // NUOVO
            $this->device_type,
            $this->brand,
            $this->model,
            $this->serial_number,
            $this->problem_description,
            $this->accessories,
            $this->reception_date,
            $this->ddt_number,
            $this->ddt_date,
            $this->status, // Obbligatorio, ha un default
            $this->technician_notes,
            $this->estimated_cost,
            $this->completion_date,
            $this->shipping_date,
            $this->tracking_code
        );

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true];
        }
        error_log("Errore creazione riparazione: " . $stmt->error);
        $error_message = $stmt->error;
        $stmt->close();
        return ['success' => false, 'error' => $error_message];
    }

    /**
     * Legge tutte le riparazioni dal database, con i dettagli del contatto e dell'utente associato.
     * Applica il filtro per utente in base al ruolo.
     * @param int|null $current_user_id L'ID dell'utente attualmente loggato (null se non loggato).
     * @param string|null $current_user_role Il ruolo dell'utente attualmente loggato.
     * @param string $search_query Query di ricerca opzionale per filtrare i risultati.
     * @return array Un array di riparazioni.
     */
    public function readAll($current_user_id = null, $current_user_role = null, $search_query = '') {
        // Modificato: LEFT JOIN con users per includere riparazioni senza user_id
        $query = "SELECT r.*, c.first_name, c.last_name, c.company, c.phone, c.email, u.username as user_username FROM " 
                 . $this->table_name . " r 
                 JOIN contacts c ON r.contact_id = c.id
                 LEFT JOIN users u ON r.user_id = u.id"; // CAMBIATO IN LEFT JOIN
        
        $conditions = [];
        $bind_values = [];
        $types = "";

        // Applica il filtro per utente se il ruolo è 'tecnico'
        // I commerciali vedono TUTTE le riparazioni per necessità di informazione generale.
        if ($current_user_id !== null && $current_user_role === 'tecnico') {
            $conditions[] = "r.user_id = ?";
            $types .= "i";
            $bind_values[] = &$current_user_id;
        }

        // Se è presente una query di ricerca, aggiungi le condizioni WHERE
        if (!empty($search_query)) {
            $search_term = "%" . $search_query . "%";
            // Includi username nella ricerca per admin/superadmin, altrimenti cerca solo nei campi visibili a tutti
            if ($current_user_role === 'admin' || $current_user_role === 'superadmin') {
                $search_conditions = "(r.device_type LIKE ? OR r.brand LIKE ? OR r.model LIKE ? OR r.serial_number LIKE ? OR r.status LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR c.company LIKE ? OR u.username LIKE ?)";
                $types .= "sssssssss"; // 9 's' per i 9 LIKE
                $bind_values[] = &$search_term;
                $bind_values[] = &$search_term;
                $bind_values[] = &$search_term;
                $bind_values[] = &$search_term;
                $bind_values[] = &$search_term;
                $bind_values[] = &$search_term;
                $bind_values[] = &$search_term;
                $bind_values[] = &$search_term;
                $bind_values[] = &$search_term;
            } else { // Per tecnico, commerciale, utente standard, non cercare per username
                $search_conditions = "(r.device_type LIKE ? OR r.brand LIKE ? OR r.model LIKE ? OR r.serial_number LIKE ? OR r.status LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR c.company LIKE ?)";
                $types .= "ssssssss"; // 8 's' per gli 8 LIKE
                $bind_values[] = &$search_term;
                $bind_values[] = &$search_term;
                $bind_values[] = &$search_term;
                $bind_values[] = &$search_term;
                $bind_values[] = &$search_term;
                $bind_values[] = &$search_term;
                $bind_values[] = &$search_term;
                $bind_values[] = &$search_term;
            }
            $conditions[] = $search_conditions;
        }


        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $query .= " ORDER BY r.reception_date DESC, r.created_at DESC";

        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Errore nella preparazione della query in RepairModel->readAll: " . $this->conn->error);
            return [];
        }

        if (!empty($bind_values)) {
            $args = array_merge([$types], $bind_values);
            call_user_func_array([$stmt, 'bind_param'], $args);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $repairs = [];
        while ($row = $result->fetch_assoc()) {
            $repairs[] = $row;
        }
        $stmt->close();
        return $repairs;
    }

    /**
     * Legge un numero limitato di riparazioni recenti dal database (per la dashboard).
     * Applica il filtro per utente in base al ruolo.
     * @param int $limit Il numero massimo di riparazioni da restituire.
     * @param int|null $current_user_id L'ID dell'utente attualmente loggato.
     * @param string|null $current_user_role Il ruolo dell'utente attualmente loggato.
     * @return array Un array di riparazioni recenti, inclusi nome e cognome del contatto e username.
     */
    public function readRecent($limit = 5, $current_user_id = null, $current_user_role = null) {
        // Modificato: LEFT JOIN con users per includere riparazioni senza user_id
        $query = "SELECT r.*, c.first_name, c.last_name, c.company, u.username as user_username FROM " 
                 . $this->table_name . " r 
                 JOIN contacts c ON r.contact_id = c.id 
                 LEFT JOIN users u ON r.user_id = u.id"; // CAMBIATO IN LEFT JOIN
        
        $bind_values = [];
        $types = "";
        $conditions = []; // Array per costruire la clausola WHERE

        // Applica il filtro per utente se il ruolo è 'tecnico'.
        // I commerciali vedono TUTTE le riparazioni recenti per coerenza con la richiesta.
        if ($current_user_id !== null && $current_user_role === 'tecnico') {
            $conditions[] = "r.user_id = ?";
            $types .= "i";
            $bind_values[] = &$current_user_id;
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $query .= " ORDER BY r.reception_date DESC LIMIT ?";
        $types .= "i";
        $bind_values[] = &$limit;

        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Errore nella preparazione della query in RepairModel->readRecent: " . $this->conn->error);
            return [];
        }

        $args = array_merge([$types], $bind_values);
        call_user_func_array([$stmt, 'bind_param'], $args);
        
        $stmt->execute();
        $result = $stmt->get_result();
        $recent_repairs = [];
        while ($row = $result->fetch_assoc()) {
            $recent_repairs[] = $row;
        }
        $stmt->close();
        return $recent_repairs;
    }


    /**
     * Legge una singola riparazione dal database, con i dettagli del contatto e dell'utente associato.
     * @param int $id L'ID della riparazione da leggere.
     * @return array|null Un array contenente i dati della riparazione se trovata, altrimenti null.
     */
    public function readOne($id) {
        // Modificato: LEFT JOIN con users per includere riparazioni senza user_id
        $query = "SELECT r.*, c.first_name, c.last_name, c.company, c.phone, c.email, u.username as user_username FROM " 
                 . $this->table_name . " r 
                 JOIN contacts c ON r.contact_id = c.id 
                 LEFT JOIN users u ON r.user_id = u.id 
                 WHERE r.id = ? LIMIT 0,1"; // CAMBIATO IN LEFT JOIN
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $repair = $result->fetch_assoc();
        $stmt->close();
        return $repair;
    }

    /**
     * Aggiorna una riparazione esistente nel database.
     * I dati aggiornati devono essere stati assegnati alle proprietà dell'oggetto.
     * @return array Un array con 'success' (bool) e 'error' (string, se presente).
     */
    public function update() {
        // Includi user_id nell'aggiornamento
        $query = "UPDATE " . $this->table_name . " SET
                    contact_id = ?, user_id = ?, device_type = ?, brand = ?, model = ?, serial_number = ?, 
                    problem_description = ?, accessories = ?, reception_date = ?, ddt_number = ?, 
                    ddt_date = ?, status = ?, technician_notes = ?, estimated_cost = ?, 
                    completion_date = ?, shipping_date = ?, tracking_code = ?
                WHERE id = ?"; // Aggiunto user_id nella lista dei campi da aggiornare
        $stmt = $this->conn->prepare($query);

        // Sanifica e imposta a NULL le stringhe vuote.
        $this->contact_id = filter_var($this->contact_id, FILTER_VALIDATE_INT);
        $this->user_id = filter_var($this->user_id, FILTER_VALIDATE_INT) ?: null; // NUOVO
        $this->device_type = !empty($this->device_type) ? htmlspecialchars(strip_tags($this->device_type)) : null;
        $this->brand = !empty($this->brand) ? htmlspecialchars(strip_tags($this->brand)) : null;
        $this->model = !empty($this->model) ? htmlspecialchars(strip_tags($this->model)) : null;
        $this->serial_number = !empty($this->serial_number) ? htmlspecialchars(strip_tags($this->serial_number)) : null;
        $this->problem_description = !empty($this->problem_description) ? htmlspecialchars(strip_tags($this->problem_description)) : null;
        $this->accessories = !empty($this->accessories) ? htmlspecialchars(strip_tags($this->accessories)) : null;
        $this->reception_date = !empty($this->reception_date) ? htmlspecialchars(strip_tags($this->reception_date)) : null;
        $this->ddt_number = !empty($this->ddt_number) ? htmlspecialchars(strip_tags($this->ddt_number)) : null;
        $this->ddt_date = !empty($this->ddt_date) ? htmlspecialchars(strip_tags($this->ddt_date)) : null;
        $this->status = !empty($this->status) ? htmlspecialchars(strip_tags($this->status)) : 'In Attesa';
        $this->technician_notes = !empty($this->technician_notes) ? htmlspecialchars(strip_tags($this->technician_notes)) : null;
        $this->estimated_cost = !empty($this->estimated_cost) ? floatval($this->estimated_cost) : null;
        $this->completion_date = !empty($this->completion_date) ? htmlspecialchars(strip_tags($this->completion_date)) : null;
        $this->shipping_date = !empty($this->shipping_date) ? htmlspecialchars(strip_tags($this->shipping_date)) : null;
        $this->tracking_code = !empty($this->tracking_code) ? htmlspecialchars(strip_tags($this->tracking_code)) : null;
        $this->id = filter_var($this->id, FILTER_VALIDATE_INT); // Assicurati che l'ID sia un intero

        // Collega i parametri.
        $stmt->bind_param("iisssssssssssdsssi", // 2 (int) + 14 (string/null) + 1 (float) + 1 (int) = 18 parametri totali
            $this->contact_id,
            $this->user_id, // NUOVO
            $this->device_type,
            $this->brand,
            $this->model,
            $this->serial_number,
            $this->problem_description,
            $this->accessories,
            $this->reception_date,
            $this->ddt_number,
            $this->ddt_date,
            $this->status,
            $this->technician_notes,
            $this->estimated_cost,
            $this->completion_date,
            $this->shipping_date,
            $this->tracking_code,
            $this->id
        );

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true];
        }
        error_log("Errore aggiornamento riparazione: " . $stmt->error);
        $error_message = $stmt->error;
        $stmt->close();
        return ['success' => false, 'error' => $error_message];
    }

    /**
     * Elimina una riparazione specifica dal database.
     * @param int $id L'ID della riparazione da eliminare.
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
        error_log("Errore eliminazione riparazione: " . $stmt->error);
        $stmt->close();
        return false;
    }

    /**
     * Chiude la connessione al database associata a questo oggetto.
     */
    public function closeConnection() {
        if ($this->conn && $this->conn->ping()) {
            $this->conn->close();
        }
    }

    /**
     * Funzione di validazione per i campi della riparazione.
     * @param array $data Dati da validare.
     * @param bool $is_edit_mode Indica se la validazione è per una modifica (per escludere serial_number unique check).
     * @return array Array di stringhe di errore, vuoto se valido.
     */
    public function validate($data, $is_edit_mode = false) {
        $errors = [];

        // Validazione Obbligatori (contact_id e status)
        if (empty($data['contact_id'])) {
            $errors[] = "È obbligatorio associare la riparazione ad un cliente.";
        }
        if (empty($data['status'])) {
            $errors[] = "Lo Stato della riparazione è obbligatorio.";
        } else {
            // Validazione per stati validi
            $valid_statuses = ['In Attesa', 'In Lavorazione', 'Ricambi Ordinati', 'In Test', 'Completata', 'Annullata', 'Ritirata'];
            if (!in_array($data['status'], $valid_statuses)) {
                $errors[] = "Stato della riparazione non valido.";
            }
        }

        // Validazioni di formato e lunghezza per campi non obbligatori ma con restrizioni
        if (!empty($data['serial_number']) && strlen($data['serial_number']) > 100) {
            $errors[] = "La Matricola è troppo lunga (max 100 caratteri).";
        }
        if (!empty($data['device_type']) && strlen($data['device_type']) > 100) {
            $errors[] = "Il Tipo di dispositivo è troppo lungo (max 100 caratteri).";
        }
        if (!empty($data['brand']) && strlen($data['brand']) > 100) {
            $errors[] = "La Marca è troppo lunga (max 100 caratteri).";
        }
        if (!empty($data['model']) && strlen($data['model']) > 100) {
            $errors[] = "Il Modello è troppo lungo (max 100 caratteri).";
        }
        if (!empty($data['ddt_number']) && strlen($data['ddt_number']) > 50) {
            $errors[] = "Il Numero DDT è troppo lungo (max 50 caratteri).";
        }
        if (!empty($data['tracking_code']) && strlen($data['tracking_code']) > 255) {
            $errors[] = "Il Codice di tracciatura è troppo lungo (max 255 caratteri).";
        }
        
        // Validazione UNIQUE per serial_number
        if (!empty($data['serial_number'])) {
            $existing_repair = $this->findBySerialNumber($data['serial_number']);
            if ($existing_repair) {
                // Se siamo in modalità modifica, permetti che la matricola sia la stessa dell'oggetto che stiamo modificando
                if ($is_edit_mode && $existing_repair['id'] == $data['id']) {
                    // OK, è la stessa riparazione che stiamo modificando
                } else {
                    $errors[] = "La Matricola inserita esiste già per un'altra riparazione.";
                }
            }
        }

        // Validazione date (formato YYYY-MM-DD e logica)
        $reception_date = $data['reception_date'] ?? null;
        $ddt_date = $data['ddt_date'] ?? null;
        $completion_date = $data['completion_date'] ?? null;
        $shipping_date = $data['shipping_date'] ?? null;

        if (!empty($reception_date) && !preg_match("/^\d{4}-\d{2}-\d{2}$/", $reception_date)) {
            $errors[] = "La Data di Arrivo non è nel formato corretto (YYYY-MM-DD).";
        }
        if (!empty($ddt_date) && !preg_match("/^\d{4}-\d{2}-\d{2}$/", $ddt_date)) {
            $errors[] = "La Data DDT non è nel formato corretto (YYYY-MM-DD).";
        }
        if (!empty($completion_date) && !preg_match("/^\d{4}-\d{2}-\d{2}$/", $completion_date)) {
            $errors[] = "La Data Termine Lavori non è nel formato corretto (YYYY-MM-DD).";
        }
        if (!empty($shipping_date) && !preg_match("/^\d{4}-\d{2}-\d{2}$/", $shipping_date)) {
            $errors[] = "La Data di Spedizione non può essere precedente alla Data Termine Lavori.";
        }

        // Logica date: data di spedizione non può essere prima della data di completamento
        if (!empty($completion_date) && !empty($shipping_date) && strtotime($shipping_date) < strtotime($completion_date)) {
            $errors[] = "La Data di Spedizione non può essere precedente alla Data Termine Lavori.";
        }

        // Validazione estimated_cost
        if (!empty($data['estimated_cost']) && (!is_numeric($data['estimated_cost']) || floatval($data['estimated_cost']) < 0)) {
            $errors[] = "Il Costo stimato deve essere un numero positivo.";
        }

        return $errors;
    }

    /**
     * Trova una riparazione per numero di matricola.
     * Usata per validare l'unicità del serial_number.
     * @param string $serial_number Il numero di matricola da cercare.
     * @return array|null Un array contenente i dati della riparazione se trovata, altrimenti null.
     */
    public function findBySerialNumber($serial_number) {
        $query = "SELECT id, serial_number FROM " . $this->table_name . " WHERE serial_number = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $serial_number);
        $stmt->execute();
        $result = $stmt->get_result();
        $repair = $result->fetch_assoc();
        $stmt->close();
        return $repair;
    }
}
