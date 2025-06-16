<?php
// app/models/User.php

// Includi il file di configurazione del database per la funzione di connessione
require_once __DIR__ . '/../config/database.php';

class User {
    private $conn; // Variabile per la connessione al database
    private $table_name = "users"; // Nome della tabella degli utenti

    // Proprietà dell'oggetto User, corrispondenti alle colonne della tabella
    public $id;
    public $username;
    public $password_hash;
    public $role; // Questo campo ora supporterà i nuovi ruoli: 'superadmin', 'admin', 'tecnico', 'cliente'
    public $created_at;

    /**
     * Costruttore della classe User.
     * Inizializza la connessione al database.
     */
    public function __construct() {
        // Ottiene una nuova connessione al database utilizzando la funzione definita in database.php
        $this->conn = getDbConnection();
    }

    /**
     * Crea un nuovo utente nel database.
     * @return bool True in caso di successo, False in caso di errore.
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (username, password_hash, role) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        // Sanifica i dati
        $this->username = htmlspecialchars(strip_tags($this->username));
        // La password_hash non va sanificata con strip_tags in quanto è già un hash sicuro.
        $this->role = htmlspecialchars(strip_tags($this->role));

        // Collega i parametri
        $stmt->bind_param("sss", $this->username, $this->password_hash, $this->role);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }
        error_log("Errore creazione utente: " . $stmt->error);
        $stmt->close();
        return false;
    }

    /**
     * Legge tutti gli utenti dal database.
     * @param string $search_query Query di ricerca opzionale per filtrare i risultati.
     * @return array Un array di utenti.
     */
    public function readAll($search_query = '') {
        // NOTA: Non includiamo password_hash qui per motivi di sicurezza/performance,
        // dato che non è necessario per l'elenco degli utenti.
        $query = "SELECT id, username, role, created_at FROM " . $this->table_name;
        // Se è presente una query di ricerca, aggiungi la clausola WHERE
        if (!empty($search_query)) {
            $search_term = "%" . $search_query . "%";
            $query .= " WHERE username LIKE ? OR role LIKE ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ss", $search_term, $search_term);
        } else {
            $stmt = $this->conn->prepare($query);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $users = [];
        // Itera sui risultati e aggiungi ogni riga all'array degli utenti
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        $stmt->close();
        return $users;
    }

    /**
     * Legge un singolo utente dal database.
     * @param int $id L'ID dell'utente da leggere.
     * @return array|null Un array contenente i dati dell'utente se trovato, altrimenti null.
     */
    public function readOne($id) {
        // IMPORTANTE: Includiamo password_hash qui perché è necessario per la verifica della password
        $query = "SELECT id, username, password_hash, role, created_at FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id); // Collega l'ID come parametro intero
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc(); // Ottieni la singola riga di risultato
        $stmt->close();
        return $user;
    }

    /**
     * Aggiorna un utente esistente nel database.
     * La password viene aggiornata solo se $this->password_hash è non-null.
     * @return bool True in caso di successo, False in caso di errore.
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET username = ?, role = ?";
        if ($this->password_hash !== null) { // Aggiungi la password alla query solo se deve essere aggiornata
            $query .= ", password_hash = ?";
        }
        $query .= " WHERE id = ?";

        $stmt = $this->conn->prepare($query);

        // Sanifica i dati
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Collega i parametri dinamicamente a seconda se la password è stata aggiornata
        if ($this->password_hash !== null) {
            $stmt->bind_param("sssi", $this->username, $this->role, $this->password_hash, $this->id);
        } else {
            $stmt->bind_param("ssi", $this->username, $this->role, $this->id);
        }

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }
        error_log("Errore aggiornamento utente: " . $stmt->error);
        $stmt->close();
        return false;
    }

    /**
     * Elimina un utente specifico dal database.
     * @param int $id L'ID dell'utente da eliminare.
     * @return bool True in caso di successo, False in caso di errore.
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id); // Collega l'ID come parametro intero

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }
        error_log("Errore eliminazione utente: " . $stmt->error);
        $stmt->close();
        return false;
    }

    /**
     * Trova un utente per username.
     * @param string $username Lo username da cercare.
     * @return array|null Un array contenente i dati dell'utente se trovato, altrimenti null.
     */
    public function findByUsername($username) {
        $query = "SELECT id, username, password_hash, role, created_at FROM " . $this->table_name . " WHERE username = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        // Sanifica l'username prima di collegarlo
        $username = htmlspecialchars(strip_tags($username));
        $stmt->bind_param("s", $username); // Collega lo username come parametro stringa
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc(); // Ottieni la singola riga di risultato
        $stmt->close();
        return $user;
    }

    /**
     * Verifica la password fornita rispetto all'hash memorizzato.
     * @param string $password La password in chiaro fornita dall'utente.
     * @param string $hashed_password L'hash della password memorizzato nel database.
     * @return bool True se la password corrisponde all'hash, False altrimenti.
     */
    public function verifyPassword($password, $hashed_password) {
        return password_verify($password, $hashed_password);
    }

    /**
     * Chiude la connessione al database associata a questo oggetto.
     */
    public function closeConnection() {
        if ($this->conn && $this->conn->ping()) {
            $this->conn->close();
        }
    }
}
