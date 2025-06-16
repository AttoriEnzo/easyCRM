<?php
// app/models/Interaction.php

// Includi il file di configurazione del database per la funzione di connessione
require_once __DIR__ . '/../config/database.php';

class Interaction {
    private $conn; // Variabile per la connessione al database
    private $table_name = "interactions"; // Nome della tabella delle interazioni

    // Proprietà dell'oggetto Interaction, corrispondenti alle colonne della tabella
    public $id;
    public $contact_id;     // ID del contatto a cui è associata l'interazione
    public $user_id;        // NUOVO: ID dell'utente che ha creato l'interazione
    public $interaction_date; // Data dell'interazione
    public $type;           // Tipo di interazione (es. Chiamata, Email, Meeting)
    public $notes;          // Note sull'interazione
    public $created_at;     // Timestamp di creazione del record

    /**
     * Costruttore della classe Interaction.
     * Inizializza la connessione al database.
     */
    public function __construct() {
        // Ottiene una nuova connessione al database utilizzando la funzione definita in database.php
        $this->conn = getDbConnection();
    }

    /**
     * Crea una nuova interazione nel database.
     * I dati dell'interazione devono essere stati assegnati alle proprietà dell'oggetto prima di chiamare questo metodo.
     * @return bool True in caso di successo, False in caso di errore.
     */
    public function create() {
        // Aggiungi user_id alla query INSERT
        $query = "INSERT INTO " . $this->table_name . " (contact_id, user_id, interaction_date, type, notes) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        // Sanifica i dati in ingresso per prevenire attacchi (XSS)
        $this->contact_id = htmlspecialchars(strip_tags($this->contact_id));
        $this->user_id = filter_var($this->user_id, FILTER_VALIDATE_INT) ?: null; // Assicurati che sia int o null
        $this->interaction_date = htmlspecialchars(strip_tags($this->interaction_date));
        $this->type = htmlspecialchars(strip_tags($this->type));
        $this->notes = htmlspecialchars(strip_tags($this->notes));

        // Collega i parametri allo statement preparato. 'iisss' indica i tipi di dati:
        // integer (contact_id), integer (user_id), string (interaction_date), string (type), string (notes)
        // Le proprietà dell'oggetto PHP sono già riferimenti, quindi bind_param funziona direttamente.
        $stmt->bind_param("iisss",
            $this->contact_id,
            $this->user_id,
            $this->interaction_date,
            $this->type,
            $this->notes
        );

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }
        // Logga l'errore per il debugging in caso di fallimento dell'esecuzione
        error_log("Errore creazione interazione: " . $stmt->error);
        $stmt->close();
        return false;
    }

    /**
     * Legge le interazioni associate a un contatto specifico, applicando filtri basati sul ruolo.
     * @param int $contact_id L'ID del contatto di cui leggere le interazioni.
     * @param int|null $current_user_id L'ID dell'utente attualmente loggato (null se non loggato).
     * @param string|null $current_user_role Il ruolo dell'utente attualmente loggato.
     * @return array Un array di interazioni.
     */
    public function readByContactId($contact_id, $current_user_id = null, $current_user_role = null) {
        // Inizia la query con JOIN su users per ottenere l'username
        $query = "SELECT i.id, i.contact_id, i.interaction_date, i.type, i.notes, i.created_at, i.user_id, u.username as user_username FROM " 
                 . $this->table_name . " i JOIN users u ON i.user_id = u.id WHERE i.contact_id = ?";
        
        $bind_values = []; // Array che conterrà i riferimenti per bind_param
        $types = "i"; // Tipo per contact_id
        $bind_values[] = &$contact_id; // Passa contact_id per riferimento

        // Applica il filtro per utente se il ruolo è 'tecnico' o 'commerciale'
        if ($current_user_id !== null && ($current_user_role === 'tecnico' || $current_user_role === 'commerciale')) {
            $query .= " AND i.user_id = ?";
            $types .= "i"; // Aggiungi 'i' per il tipo di parametro (integer)
            $bind_values[] = &$current_user_id; // Passa current_user_id per riferimento
        }

        $query .= " ORDER BY i.interaction_date DESC";

        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Errore nella preparazione della query in InteractionModel->readByContactId: " . $this->conn->error);
            return [];
        }

        // Costruisci l'array di argomenti per call_user_func_array
        // Il primo elemento è la stringa dei tipi, seguiti dai riferimenti ai valori
        $args = array_merge([$types], $bind_values);
        
        call_user_func_array([$stmt, 'bind_param'], $args);

        $stmt->execute();
        $result = $stmt->get_result();
        $interactions = [];
        // Itera sui risultati e aggiungi ogni riga all'array delle interazioni
        while ($row = $result->fetch_assoc()) {
            $interactions[] = $row;
        }
        $stmt->close();
        return $interactions;
    }

    /**
     * Legge *tutte* le interazioni associate a un contatto specifico, ignorando il filtro per utente.
     * Questo è utile per le viste dove si necessita una panoramica completa (es. dettaglio riparazione per Commerciale).
     * @param int $contact_id L'ID del contatto di cui leggere le interazioni.
     * @return array Un array di interazioni.
     */
    public function readAllByContactIdIgnoringUserFilter($contact_id) {
        $query = "SELECT i.id, i.contact_id, i.interaction_date, i.type, i.notes, i.created_at, i.user_id, u.username as user_username FROM " 
                 . $this->table_name . " i JOIN users u ON i.user_id = u.id WHERE i.contact_id = ? ORDER BY i.interaction_date DESC";
        
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Errore nella preparazione della query in InteractionModel->readAllByContactIdIgnoringUserFilter: " . $this->conn->error);
            return [];
        }

        $stmt->bind_param("i", $contact_id);
        
        $stmt->execute();
        $result = $stmt->get_result();
        $interactions = [];
        while ($row = $result->fetch_assoc()) {
            $interactions[] = $row;
        }
        $stmt->close();
        return $interactions;
    }

    /**
     * Legge un numero limitato di interazioni recenti dal database (per la dashboard).
     * Applica il filtro per utente in base al ruolo.
     * @param int $limit Il numero massimo di interazioni da restituire.
     * @param int|null $current_user_id L'ID dell'utente attualmente loggato.
     * @param string|null $current_user_role Il ruolo dell'utente attualmente loggato.
     * @return array Un array di interazioni recenti, inclusi nome e cognome del contatto e username.
     */
    public function readRecent($limit = 5, $current_user_id = null, $current_user_role = null) {
        $query = "SELECT i.*, c.first_name, c.last_name, c.company, u.username as user_username FROM " 
                 . $this->table_name . " i JOIN contacts c ON i.contact_id = c.id JOIN users u ON i.user_id = u.id";
        
        $bind_values = [];
        $types = "";

        // Applica il filtro per utente se il ruolo è 'tecnico' o 'commerciale'
        if ($current_user_id !== null && ($current_user_role === 'tecnico' || $current_user_role === 'commerciale')) {
            $query .= " WHERE i.user_id = ?";
            $types .= "i"; // Aggiungi 'i' per il tipo di parametro (integer)
            $bind_values[] = &$current_user_id; // Passa current_user_id per riferimento
        }

        $query .= " ORDER BY i.interaction_date DESC LIMIT ?";
        $types .= "i"; // Aggiungi 'i' per il tipo di parametro (integer) del LIMIT
        $bind_values[] = &$limit; // Passa limit per riferimento

        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Errore nella preparazione della query in InteractionModel->readRecent: " . $this->conn->error);
            return [];
        }

        $args = array_merge([$types], $bind_values);
        call_user_func_array([$stmt, 'bind_param'], $args);
        
        $stmt->execute();
        $result = $stmt->get_result();
        $recent_interactions = [];
        while ($row = $result->fetch_assoc()) {
            $recent_interactions[] = $row;
        }
        $stmt->close();
        return $recent_interactions;
    }

    /**
     * Legge tutte le interazioni globalmente, applicando filtri basati sul ruolo dell'utente.
     * Questo metodo è pensato per la vista elenco generale delle interazioni.
     * @param int|null $current_user_id L'ID dell'utente attualmente loggato.
     * @param string|null $current_user_role Il ruolo dell'utente attualmente loggato.
     * @param string $search_query Query di ricerca opzionale per filtrare i risultati.
     * @return array Un array di interazioni, inclusi nome/cognome/azienda del contatto e username.
     */
    public function readAllGlobal($current_user_id = null, $current_user_role = null, $search_query = '') {
        $query = "SELECT i.*, c.first_name, c.last_name, c.company, u.username as user_username FROM " 
                 . $this->table_name . " i 
                 JOIN contacts c ON i.contact_id = c.id 
                 JOIN users u ON i.user_id = u.id";
        
        $conditions = [];
        $bind_values = [];
        $types = "";

        // Applica il filtro per utente se il ruolo è 'tecnico' o 'commerciale'
        if ($current_user_id !== null && ($current_user_role === 'tecnico' || $current_user_role === 'commerciale')) {
            $conditions[] = "i.user_id = ?";
            $types .= "i";
            $bind_values[] = &$current_user_id;
        }

        // Se è presente una query di ricerca, aggiungi le condizioni WHERE
        if (!empty($search_query)) {
            $search_term = "%" . $search_query . "%";
            $search_conditions = "(i.notes LIKE ? OR i.type LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR c.company LIKE ? OR u.username LIKE ?)";
            $conditions[] = $search_conditions;
            $types .= "ssssss"; // 6 's' per i 6 LIKE
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

        $query .= " ORDER BY i.interaction_date DESC, i.created_at DESC";

        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Errore nella preparazione della query in InteractionModel->readAllGlobal: " . $this->conn->error);
            return [];
        }

        // Costruisci l'array di argomenti per call_user_func_array
        if (!empty($bind_values)) { // Binda solo se ci sono valori
            $args = array_merge([$types], $bind_values);
            call_user_func_array([$stmt, 'bind_param'], $args);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $all_interactions = [];
        while ($row = $result->fetch_assoc()) {
            $all_interactions[] = $row;
        }
        $stmt->close();
        return $all_interactions;
    }

    /**
     * Elimina un'interazione specifica dal database.
     * @param int $id L'ID dell'interazione da eliminare.
     * @param int $contact_id L'ID del contatto a cui è associata l'interazione (per sicurezza).
     * @param int|null $current_user_id L'ID dell'utente che tenta di eliminare (per controllo permessi).
     * @param string|null $current_user_role Il ruolo dell'utente che tenta di eliminare.
     * @return bool True in caso di successo, False in caso di errore.
     */
    public function delete($id, $contact_id, $current_user_id = null, $current_user_role = null) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ? AND contact_id = ?";
        $bind_values = [];
        $types = "ii"; // Tipi per id e contact_id
        $bind_values[] = &$id;
        $bind_values[] = &$contact_id;

        // Se l'utente è un tecnico o commerciale, può eliminare solo le proprie interazioni
        if ($current_user_id !== null && ($current_user_role === 'tecnico' || $current_user_role === 'commerciale')) {
            $query .= " AND user_id = ?";
            $types .= "i"; // Aggiungi 'i' per user_id
            $bind_values[] = &$current_user_id;
        }
        
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Errore nella preparazione della query in InteractionModel->delete: " . $this->conn->error);
            return false;
        }

        // Costruisci l'array di argomenti per call_user_func_array
        $args = array_merge([$types], $bind_values);
        call_user_func_array([$stmt, 'bind_param'], $args);

        if ($stmt->execute()) {
            $stmt->close();
            // Verifica se qualche riga è stata effettivamente eliminata
            return $stmt->affected_rows > 0;
        }
        error_log("Errore eliminazione interazione: " . $stmt->error);
        $stmt->close();
        return false;
    }

    /**
     * Chiude la connessione al database associata a questo oggetto.
     * È buona pratica chiudere le connessioni quando non più necessarie.
     */
    public function closeConnection() {
        if ($this->conn && $this->conn->ping()) {
            $this->conn->close();
        }
    }
}
