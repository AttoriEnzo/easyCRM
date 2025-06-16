<?php
// app/models/Contact.php

// Includi il file di configurazione del database per la funzione di connessione
require_once __DIR__ . '/../config/database.php';

class Contact {
    private $conn; // Variabile per la connessione al database
    private $table_name = "contacts"; // Nome della tabella dei contatti

    // Proprietà dell'oggetto Contact, corrispondenti alle colonne della tabella
    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $phone; // Telefono Fisso
    public $company;
    public $last_contact_date;
    public $contact_medium;
    public $order_executed;
    // CAMPI GIA' AGGIUNTI IN PRECEDENZA
    public $client_type; // Tipo di cliente: Privato, Ditta Individuale, Azienda/Società
    public $tax_code;    // Codice Fiscale
    public $vat_number;  // Partita IVA
    // NUOVE PROPRIETÀ AGGIUNTE
    public $sdi;            // Codice SDI
    public $company_address; // Indirizzo azienda
    public $company_city;    // Città azienda
    public $company_zip;     // CAP azienda
    public $company_province; // Provincia azienda
    public $pec;             // PEC
    public $mobile_phone;    // Telefono cellulare
    public $created_at;

    /**
     * Costruttore della classe Contact.
     * Inizializza la connessione al database.
     */
    public function __construct() {
        // Ottiene una nuova connessione al database utilizzando la funzione definita in database.php
        $this->conn = getDbConnection();
    }

    /**
     * Crea un nuovo contatto nel database.
     * I dati del contatto devono essere stati assegnati alle proprietà dell'oggetto prima di chiamare questo metodo.
     * @return array Un array con 'success' (bool) e 'error' (string, se presente).
     */
    public function create() {
        // Query SQL per inserire un nuovo contatto con tutti i campi, inclusi i nuovi
        $query = "INSERT INTO " . $this->table_name . " (first_name, last_name, email, phone, company, last_contact_date, contact_medium, order_executed, client_type, tax_code, vat_number, sdi, company_address, company_city, company_zip, company_province, pec, mobile_phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        // Sanifica i dati in ingresso SOLO per rimuovere i tag HTML, NON per convertire entità HTML.
        // La conversione in entità HTML deve avvenire solo in fase di output nelle viste.
        $this->first_name = strip_tags($this->first_name);
        $this->last_name = strip_tags($this->last_name);
        $this->email = strip_tags($this->email);
        $this->phone = strip_tags($this->phone);
        $this->company = strip_tags($this->company);
        $this->last_contact_date = !empty($this->last_contact_date) ? strip_tags($this->last_contact_date) : null;
        $this->contact_medium = strip_tags($this->contact_medium);
        $this->order_executed = strip_tags($this->order_executed);
        $this->client_type = strip_tags($this->client_type);
        $this->tax_code = strip_tags($this->tax_code);
        $this->vat_number = strip_tags($this->vat_number);
        $this->sdi = strip_tags($this->sdi);
        $this->company_address = strip_tags($this->company_address);
        $this->company_city = strip_tags($this->company_city);
        $this->company_zip = strip_tags($this->company_zip);
        $this->company_province = strip_tags($this->company_province);
        $this->pec = strip_tags($this->pec);
        $this->mobile_phone = strip_tags($this->mobile_phone);

        // Collega i parametri allo statement preparato. (18 parametri)
        $stmt->bind_param("sssssssissssssssss", // 18 caratteri
            $this->first_name,
            $this->last_name,
            $this->email,
            $this->phone,
            $this->company,
            $this->last_contact_date,
            $this->contact_medium,
            $this->order_executed, // tinyint(1)
            $this->client_type,
            $this->tax_code,
            $this->vat_number,
            $this->sdi,
            $this->company_address,
            $this->company_city,
            $this->company_zip,
            $this->company_province,
            $this->pec,
            $this->mobile_phone
        );

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true];
        }
        // Logga l'errore per il debugging in caso di fallimento dell'esecuzione
        error_log("Errore creazione contatto: " . $stmt->error);
        $error_message = $stmt->error;
        $stmt->close();
        return ['success' => false, 'error' => $error_message];
    }

    /**
     * Legge tutti i contatti dal database.
     * @param string $search_query Query di ricerca opzionale per filtrare i risultati.
     * @return array Un array di contatti.
     */
    public function readAll($search_query = '') {
        $query = "SELECT id, first_name, last_name, email, phone, company, last_contact_date, contact_medium, order_executed, client_type, tax_code, vat_number, sdi, company_address, company_city, company_zip, company_province, pec, mobile_phone, created_at FROM " . $this->table_name;
        $params = [];
        $types = "";

        // Se è presente una query di ricerca, aggiungi la clausola WHERE
        if (!empty($search_query)) {
            $search_term = "%" . $search_query . "%";
            $query .= " WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR company LIKE ? OR tax_code LIKE ? OR vat_number LIKE ? OR sdi LIKE ? OR company_address LIKE ? OR company_city LIKE ? OR company_province LIKE ? OR pec LIKE ?";
            // Prepara i parametri per il binding
            $params = array_fill(0, 11, $search_term); // 11 parametri per la ricerca
            $types = str_repeat("s", 11);
        }
        
        // Ordina sempre i risultati per company, first_name e last_name
        $query .= " ORDER BY company ASC, first_name ASC, last_name ASC";

        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Errore nella preparazione della query in ContactModel->readAll: " . $this->conn->error);
            return [];
        }

        if (!empty($params)) {
            // Utilizza l'operatore splat (...) per spacchettare l'array $params in argomenti separati
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $contacts = [];
        // Itera sui risultati e aggiungi ogni riga all'array dei contatti
        while ($row = $result->fetch_assoc()) {
            $contacts[] = $row;
        }
        $stmt->close();
        return $contacts;
    }

    /**
     * Legge un singolo contatto dal database.
     * @param int $id L'ID del contatto da leggere.
     * @return array|null Un array contenente i dati del contatto se trovato, altrimenti null.
     */
    public function readOne($id) {
        $query = "SELECT id, first_name, last_name, email, phone, company, last_contact_date, contact_medium, order_executed, client_type, tax_code, vat_number, sdi, company_address, company_city, company_zip, company_province, pec, mobile_phone, created_at FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id); // Collega l'ID come parametro intero
        $stmt->execute();
        $result = $stmt->get_result();
        $contact = $result->fetch_assoc(); // Ottieni la singola riga di risultato
        $stmt->close();
        return $contact;
    }

    /**
     * Aggiorna un contatto esistente nel database.
     * I dati aggiornati devono essere stati assegnati alle proprietà dell'oggetto.
     * @return array Un array con 'success' (bool) e 'error' (string, se presente).
     */
    public function update() {
        // Query SQL per aggiornare un contatto con tutti i campi, inclusi i nuovi
        $query = "UPDATE " . $this->table_name . " SET
                    first_name = ?,
                    last_name = ?,
                    email = ?,
                    phone = ?,
                    company = ?,
                    last_contact_date = ?,
                    contact_medium = ?,
                    order_executed = ?,
                    client_type = ?,
                    tax_code = ?,
                    vat_number = ?,
                    sdi = ?,
                    company_address = ?,
                    company_city = ?,
                    company_zip = ?,
                    company_province = ?,
                    pec = ?,
                    mobile_phone = ?
                WHERE id = ?";

        $stmt = $this->conn->prepare($query);

        // Sanifica i dati in ingresso SOLO per rimuovere i tag HTML
        $this->first_name = strip_tags($this->first_name);
        $this->last_name = strip_tags($this->last_name);
        $this->email = strip_tags($this->email);
        $this->phone = strip_tags($this->phone);
        $this->company = strip_tags($this->company);
        $this->last_contact_date = !empty($this->last_contact_date) ? strip_tags($this->last_contact_date) : null;
        $this->contact_medium = strip_tags($this->contact_medium);
        $this->order_executed = strip_tags($this->order_executed);
        $this->client_type = strip_tags($this->client_type);
        $this->tax_code = strip_tags($this->tax_code);
        $this->vat_number = strip_tags($this->vat_number);
        $this->sdi = strip_tags($this->sdi);
        $this->company_address = strip_tags($this->company_address);
        $this->company_city = strip_tags($this->company_city);
        $this->company_zip = strip_tags($this->company_zip);
        $this->company_province = strip_tags($this->company_province);
        $this->pec = strip_tags($this->pec);
        $this->mobile_phone = strip_tags($this->mobile_phone);
        $this->id = strip_tags($this->id); // Anche l'ID deve essere sanificato

        // Collega i parametri. (18 stringhe, 1 integer per l'ID) = 19 parametri
        $stmt->bind_param("sssssssissssssssssi", // 19 caratteri
            $this->first_name,
            $this->last_name,
            $this->email,
            $this->phone,
            $this->company,
            $this->last_contact_date,
            $this->contact_medium,
            $this->order_executed,
            $this->client_type,
            $this->tax_code,
            $this->vat_number,
            $this->sdi,
            $this->company_address,
            $this->company_city,
            $this->company_zip,
            $this->company_province,
            $this->pec,
            $this->mobile_phone,
            $this->id
        );

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true];
        }
        error_log("Errore aggiornamento contatto: " . $stmt->error);
        $error_message = $stmt->error;
        $stmt->close();
        return ['success' => false, 'error' => $error_message];
    }

    /**
     * Elimina un contatto specifico dal database.
     * @param int $id L'ID del contatto da eliminare.
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
        error_log("Errore eliminazione contatto: " . $stmt->error);
        $stmt->close();
        return false;
    }

    /**
     * Conta il numero totale di contatti nel database.
     * @return int Il numero totale di contatti.
     */
    public function countAll() {
        $query = "SELECT COUNT(*) as total_rows FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['total_rows'];
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

    /**
     * Valida i campi specifici (Codice Fiscale, Partita IVA, SDI, CAP, Provincia, PEC, Telefoni) in base al tipo di cliente.
     * Questa funzione si concentra solo sulla validazione del formato.
     * @param array $data Un array associativo contenente i dati del contatto.
     * @return array Un array di errori di validazione, vuoto se non ci sono errori.
     */
    public function validateTaxVatFields($data) {
        $errors = [];

        $client_type = $data['client_type'] ?? '';
        $tax_code = trim($data['tax_code'] ?? '');
        $vat_number = trim($data['vat_number'] ?? '');
        $sdi = trim($data['sdi'] ?? '');
        $company_address = trim($data['company_address'] ?? '');
        $company_city = trim($data['company_city'] ?? '');
        $company_zip = trim($data['company_zip'] ?? '');
        $company_province = trim($data['company_province'] ?? '');
        $pec = trim($data['pec'] ?? '');
        $phone = trim($data['phone'] ?? ''); // Telefono Fisso
        $mobile_phone = trim($data['mobile_phone'] ?? ''); // Telefono Cellulare


        // Regex per la validazione dei formati (applicate sempre se il campo non è vuoto)
        $regex_cf_privato = '/^[A-Z0-9]{16}$/'; // 16 caratteri alfanumerici (Codice Fiscale Privato)
        $regex_numerico_11 = '/^\d{11}$/';      // 11 cifre numeriche (Partita IVA o CF Azienda)
        // Regex combinata per Codice Fiscale: 16 alfanumerici OPPURE 11 numerici
        $regex_cf_flessibile = '/(^([A-Z0-9]{16})$)|(^(\d{11})$)/i'; // 'i' per case-insensitive sui caratteri alfanumerici


        $regex_sdi = '/^[A-Z0-9]{7}$/i';       // 7 caratteri alfanumerici per SDI
        $regex_cap = '/^\d{5}$/';              // 5 cifre numeriche per CAP
        $regex_province = '/^[A-Z]{2}$/';      // 2 caratteri alfabetici (maiuscoli) per la provincia
        $regex_pec = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/'; // Validazione base email/PEC
        $regex_phone = '/^[0-9\s\-\(\)\+]{7,20}$/'; // Telefono: numeri, spazi, trattini, parentesi, +, min 7 max 20 caratteri

        // Validazione dei campi telefono (formato)
        if (!empty($phone) && !preg_match($regex_phone, $phone)) {
            $errors[] = "Il Telefono Fisso non è nel formato corretto.";
        }
        if (!empty($mobile_phone) && !preg_match($regex_phone, $mobile_phone)) {
            $errors[] = "Il Telefono Cellulare non è nel formato corretto.";
        }

        // Validazione PEC (formato)
        if (!empty($pec) && !preg_match($regex_pec, $pec)) {
            $errors[] = "La PEC non è nel formato email corretto.";
        }

        // Validazione SDI (formato)
        if (!empty($sdi) && !preg_match($regex_sdi, $sdi)) {
            $errors[] = "Il Codice SDI (7 alfanumerici) non è nel formato corretto.";
        }
        // Validazione CAP (formato)
        if (!empty($company_zip) && !preg_match($regex_cap, $company_zip)) {
            $errors[] = "Il CAP Azienda (5 cifre numeriche) non è nel formato corretto.";
        }
        // Validazione Provincia (formato)
        if (!empty($company_province) && !preg_match($regex_province, $company_province)) {
            $errors[] = "La Provincia Azienda (2 lettere maiuscole) non è nel formato corretto.";
        }
        // Validazione lunghezza indirizzo e città
        if (!empty($company_address) && strlen($company_address) > 255) { $errors[] = "L'Indirizzo Azienda supera la lunghezza massima."; }
        if (!empty($company_city) && strlen($company_city) > 100) { $errors[] = "La Città Azienda supera la lunghezza massima."; }



        // Le regole di formato in base al tipo di cliente
        switch ($client_type) {
            case 'Privato':
                // Il Codice Fiscale deve rispettare il formato se presente
                if (!empty($tax_code) && !preg_match($regex_cf_privato, $tax_code)) {
                    $errors[] = "Il Codice Fiscale (16 alfanumerici) non è nel formato corretto per un Utente Privato.";
                }
                // Partita IVA e SDI non hanno regole specifiche di formato in questo caso, se compilati.
                // Vengono applicate le regex generali definite sopra se non sono vuoti.
                break;

            case 'Ditta Individuale':
                // Partita IVA e Codice Fiscale devono rispettare i formati se presenti
                if (!empty($vat_number) && !preg_match($regex_numerico_11, $vat_number)) {
                    $errors[] = "La Partita IVA (11 cifre numeriche) non è nel formato corretto per una Ditta Individuale.";
                }
                if (!empty($tax_code) && !preg_match($regex_cf_privato, $tax_code)) {
                    $errors[] = "Il Codice Fiscale (16 alfanumerici) non è nel formato corretto per una Ditta Individuale.";
                }
                break;

            case 'Azienda/Società':
                // Partita IVA e Codice Fiscale devono rispettare i formati se presenti
                if (!empty($vat_number) && !preg_match($regex_numerico_11, $vat_number)) {
                    $errors[] = "La Partita IVA (11 cifre numeriche) non è nel formato corretto per un'Azienda/Società.";
                }
                if (!empty($tax_code) && !preg_match($regex_numerico_11, $tax_code)) {
                    $errors[] = "Il Codice Fiscale (11 cifre numeriche) non è nel formato corretto per un'Azienda/Società.";
                }
                break;

            case 'Fornitore': // NUOVO TIPO: Fornitore
                // Il Codice Fiscale deve rispettare il formato flessibile (16 alfanumerici O 11 numerici)
                if (!empty($tax_code) && !preg_match($regex_cf_flessibile, $tax_code)) {
                    $errors[] = "Il Codice Fiscale (16 alfanumerici o 11 numerici) non è nel formato corretto per un Fornitore.";
                }
                // La Partita IVA deve rispettare il formato numerico di 11 cifre se presente
                if (!empty($vat_number) && !preg_match($regex_numerico_11, $vat_number)) {
                    $errors[] = "La Partita IVA (11 cifre numeriche) non è nel formato corretto per un Fornitore.";
                }
                break;

            default:
                // Se il tipo di cliente non è valido (solo se non è vuoto)
                if (!empty($client_type)) {
                     $errors[] = "Tipo di cliente non valido.";
                }
                // Tutti i campi fiscali/azienda devono rispettare i formati se compilati, indipendentemente dal tipo non riconosciuto
                if (!empty($tax_code) && !preg_match($regex_cf_flessibile, $tax_code)) { // Usa la regex flessibile
                    $errors[] = "Il Codice Fiscale non è nel formato corretto (16 alfanumerici o 11 numerici).";
                }
                if (!empty($vat_number) && !preg_match($regex_numerico_11, $vat_number)) {
                    $errors[] = "La Partita IVA (11 cifre numeriche) non è nel formato corretto.";
                }
                if (!empty($sdi) && !preg_match($regex_sdi, $sdi)) {
                    $errors[] = "Il Codice SDI (7 alfanumerici) non è nel formato corretto.";
                }
                if (!empty($company_zip) && !preg_match($regex_cap, $company_zip)) {
                    $errors[] = "Il CAP Azienda (5 cifre numeriche) non è nel formato corretto.";
                }
                if (!empty($company_province) && !preg_match($regex_province, $company_province)) {
                    $errors[] = "La Provincia Azienda (2 lettere maiuscole) non è nel formato corretto.";
                }
                if (!empty($pec) && !preg_match($regex_pec, $pec)) {
                    $errors[] = "La PEC non è nel formato email corretto.";
                }
                break;
        }
        return $errors;
    }
}
