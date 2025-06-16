<?php
// app/config/database.php

// Configurazione del database
// SOSTITUISCI CON LE TUE CREDENZIALI DEL DATABASE
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Il tuo username del database MySQL
define('DB_PASSWORD', '');     // La tua password del database MySQL (spesso vuota per root in XAMPP/MAMP)
define('DB_NAME', 'minimal_crm'); // Il nome del database che hai creato

/**
 * Stabilisce e restituisce una nuova connessione al database.
 * @return mysqli La connessione al database.
 */
function getDbConnection() {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    // Controlla la connessione
    if ($conn->connect_error) {
        // In un'applicazione reale, useresti un meccanismo di logging più robusto
        // e mostreresti un'errore generico all'utente.
        die("<div style='background-color: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; font-weight: 500; margin-bottom: 1rem;'>Errore di connessione al database: " . $conn->connect_error . "</div>");
    }
    return $conn;
}

/**
 * Verifica se una specifica colonna esiste in una data tabella.
 * @param mysqli $conn La connessione al database.
 * @param string $tableName Il nome della tabella.
 * @param string $columnName Il nome della colonna da verificare.
 * @return bool True se la colonna esiste, False altrimenti.
 */
function columnExists($conn, $tableName, $columnName) {
    // Escape del nome della tabella e della colonna per prevenire SQL Injection
    $tableName = $conn->real_escape_string($tableName);
    $columnName = $conn->real_escape_string($columnName);

    $result = $conn->query("SHOW COLUMNS FROM `" . $tableName . "` LIKE '" . $columnName . "'");
    return ($result && $result->num_rows > 0);
}

// --- Inizializzazione dello schema del database ---
// Questa parte viene eseguita ogni volta che il file database.php viene incluso.
// Si assicura che le tabelle e le colonne necessarie esistano.

$conn_init = getDbConnection(); // Usa una connessione separata per l'inizializzazione dello schema

// Creazione della tabella 'contacts' se non esiste, con tutti i campi previsti
$sql_create_contacts_table = "
CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE,
    phone VARCHAR(50),
    company VARCHAR(255),
    last_contact_date DATE, /* Nuovo campo: Data ultimo contatto */
    contact_medium VARCHAR(50), /* Nuovo campo: Mezzo contatto (es. Telefono, Email) */
    order_executed TINYINT(1) DEFAULT 0, /* Nuovo campo: Flag Ordine eseguito (0=No, 1=Sì) */
    -- CAMPI AGGIUNTI PER LA GESTIONE DEL CLIENTE
    client_type VARCHAR(50) DEFAULT 'Privato', /* Tipo di cliente: Privato, Ditta Individuale, Azienda/Società */
    tax_code VARCHAR(16), /* Codice Fiscale (16 alfanumerici o 11 numerici) */
    vat_number VARCHAR(11), /* Partita IVA (11 numerici) */
    -- NUOVI CAMPI AGGIUNTI
    sdi VARCHAR(7), /* Codice SDI (7 caratteri alfanumerici) */
    company_address VARCHAR(255), /* Indirizzo azienda (fino a 255 caratteri) */
    company_city VARCHAR(100), /* Città azienda (fino a 100 caratteri) */
    company_zip VARCHAR(5), /* CAP azienda (5 numerici) */
    company_province VARCHAR(2), /* NUOVO CAMPO: Provincia azienda (2 alfabetici) */
    pec VARCHAR(255), /* PEC (come email) */
    mobile_phone VARCHAR(50), /* Telefono cellulare (nuovo campo) */
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn_init->query($sql_create_contacts_table)) {
    // Logga l'errore per il debugging, invece di uscire bruscamente nell'ambiente di produzione
    error_log("Errore nella creazione della tabella 'contacts': " . $conn_init->error);
}

// Aggiungi le nuove colonne alla tabella 'contacts' se non esistono già (per aggiornamenti di schema)
if (columnExists($conn_init, 'contacts', 'id')) { // Verifica che la tabella 'contacts' esista prima di alterarla
    if (!columnExists($conn_init, 'contacts', 'last_contact_date')) {
        if (!$conn_init->query("ALTER TABLE contacts ADD COLUMN last_contact_date DATE")) {
            error_log("Errore ALTER TABLE per 'last_contact_date': " . $conn_init->error);
        }
    }
    if (!columnExists($conn_init, 'contacts', 'contact_medium')) {
        if (!$conn_init->query("ALTER TABLE contacts ADD COLUMN contact_medium VARCHAR(50)")) {
            error_log("Errore ALTER TABLE per 'contact_medium': " . $conn_init->error);
        }
    }
    if (!columnExists($conn_init, 'contacts', 'order_executed')) {
        if (!$conn_init->query("ALTER TABLE contacts ADD COLUMN order_executed TINYINT(1) DEFAULT 0")) {
            error_log("Errore ALTER TABLE per 'order_executed': " . $conn_init->error);
        }
    }
    if (!columnExists($conn_init, 'contacts', 'client_type')) {
        if (!$conn_init->query("ALTER TABLE contacts ADD COLUMN client_type VARCHAR(50) DEFAULT 'Privato'")) {
            error_log("Errore ALTER TABLE per 'client_type': " . $conn_init->error);
        }
    }
    if (!columnExists($conn_init, 'contacts', 'tax_code')) {
        if (!$conn_init->query("ALTER TABLE contacts ADD COLUMN tax_code VARCHAR(16)")) {
            error_log("Errore ALTER TABLE per 'tax_code': " . $conn_init->error);
        }
    }
    if (!columnExists($conn_init, 'contacts', 'vat_number')) {
        if (!$conn_init->query("ALTER TABLE contacts ADD COLUMN vat_number VARCHAR(11)")) {
            error_log("Errore ALTER TABLE per 'vat_number': " . $conn_init->error);
        }
    }
    // Aggiungi le nuove colonne che hai richiesto
    if (!columnExists($conn_init, 'contacts', 'sdi')) {
        if (!$conn_init->query("ALTER TABLE contacts ADD COLUMN sdi VARCHAR(7)")) {
            error_log("Errore ALTER TABLE per 'sdi': " . $conn_init->error);
        }
    }
    if (!columnExists($conn_init, 'contacts', 'company_address')) {
        if (!$conn_init->query("ALTER TABLE contacts ADD COLUMN company_address VARCHAR(255)")) {
            error_log("Errore ALTER TABLE per 'company_address': " . $conn_init->error);
        }
    }
    if (!columnExists($conn_init, 'contacts', 'company_city')) {
        if (!$conn_init->query("ALTER TABLE contacts ADD COLUMN company_city VARCHAR(100)")) {
            error_log("Errore ALTER TABLE per 'company_city': " . $conn_init->error);
        }
    }
    if (!columnExists($conn_init, 'contacts', 'company_zip')) {
        if (!$conn_init->query("ALTER TABLE contacts ADD COLUMN company_zip VARCHAR(5)")) {
            error_log("Errore ALTER TABLE per 'company_zip': " . $conn_init->error);
        }
    }
    // NUOVO: Aggiungi la colonna company_province
    if (!columnExists($conn_init, 'contacts', 'company_province')) {
        if (!$conn_init->query("ALTER TABLE contacts ADD COLUMN company_province VARCHAR(2)")) {
            error_log("Errore ALTER TABLE per 'company_province': " . $conn_init->error);
        }
    }
    if (!columnExists($conn_init, 'contacts', 'pec')) {
        if (!$conn_init->query("ALTER TABLE contacts ADD COLUMN pec VARCHAR(255)")) {
            error_log("Errore ALTER TABLE per 'pec': " . $conn_init->error);
        }
    }
    if (!columnExists($conn_init, 'contacts', 'mobile_phone')) {
        if (!$conn_init->query("ALTER TABLE contacts ADD COLUMN mobile_phone VARCHAR(50)")) {
            error_log("Errore ALTER TABLE per 'mobile_phone': " . $conn_init->error);
        }
    }
}


// Creazione della tabella 'interactions' se non esiste
$sql_create_interactions_table = "
CREATE TABLE IF NOT EXISTS interactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contact_id INT NOT NULL,
    user_id INT NULL,
    interaction_date DATE NOT NULL,
    type VARCHAR(50) NOT NULL, /* Tipo di interazione (es. Chiamata, Email, Meeting) */
    notes TEXT, /* Note sull'interazione */
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)";

if (!$conn_init->query($sql_create_interactions_table)) {
    error_log("Errore nella creazione della tabella 'interactions': " . $conn_init->error);
}

// Aggiungi la colonna user_id alla tabella 'interactions' se non esiste
if (columnExists($conn_init, 'interactions', 'id') && !columnExists($conn_init, 'interactions', 'user_id')) {
    $sql_add_user_id_to_interactions = "ALTER TABLE interactions ADD COLUMN user_id INT NULL AFTER contact_id";
    if (!$conn_init->query($sql_add_user_id_to_interactions)) {
        error_log("Errore ALTER TABLE per 'interactions.user_id': " . $conn_init->error);
    } else {
        $sql_add_fk_to_interactions_user_id = "ALTER TABLE interactions ADD CONSTRAINT fk_interactions_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL";
        if (!$conn_init->query($sql_add_fk_to_interactions_user_id)) {
            error_log("Errore nell'aggiunta della FK 'fk_interactions_user_id': " . $conn_init->error);
        }
    }
}


// Creazione della tabella 'users' per l'autenticazione
$sql_create_users_table = "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'user', /* Ruolo dell'utente (es. 'user', 'admin', 'superadmin', 'tecnico', 'cliente', 'commerciale') */
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn_init->query($sql_create_users_table)) {
    error_log("Errore nella creazione della tabella 'users': " . $conn_init->error);
}

// Creazione della tabella 'repairs' se non esiste
$sql_create_repairs_table = "
CREATE TABLE IF NOT EXISTS repairs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contact_id INT NOT NULL, /* Collega al cliente (obbligatorio) */
    user_id INT NULL, 
    device_type VARCHAR(100) NULL, /* Tipo di dispositivo (es. Saldatrice Inverter) */
    brand VARCHAR(100) NULL, /* Marca del dispositivo */
    model VARCHAR(100) NULL, /* Modello del dispositivo */
    serial_number VARCHAR(100) UNIQUE NULL, /* Numero di matricola (UNICO ma può essere NULL) */
    problem_description TEXT NULL, /* Descrizione del problema */
    accessories VARCHAR(255) NULL, /* Accessori consegnati */
    reception_date DATE NULL, /* Data di arrivo in officina */
    ddt_number VARCHAR(50) NULL, /* Numero DDT */
    ddt_date DATE NULL, /* Data DDT */
    status VARCHAR(50) NOT NULL DEFAULT 'In Attesa', /* Stato della riparazione (obbligatorio con default) */
    technician_notes TEXT NULL, /* Note e diagnosi del tecnico */
    estimated_cost DECIMAL(10, 2) NULL, /* Costo stimato - rimarrà, ma sarà calcolato */
    completion_date DATE NULL, /* Data termine lavori */
    shipping_date DATE NULL, /* Data di spedizione */
    tracking_code VARCHAR(255) NULL, /* Codice di tracciatura spedizione */
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, /* Timestamp di creazione */
    FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE RESTRICT, /* Non eliminare un contatto se ha riparazioni associate */
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL 
)";

if (!$conn_init->query($sql_create_repairs_table)) {
    error_log("Errore nella creazione della tabella 'repairs': " . $conn_init->error);
}

// Aggiungi la colonna user_id alla tabella 'repairs' se non esiste
if (columnExists($conn_init, 'repairs', 'id') && !columnExists($conn_init, 'repairs', 'user_id')) {
    $sql_add_user_id_to_repairs = "ALTER TABLE repairs ADD COLUMN user_id INT NULL AFTER contact_id";
    if (!$conn_init->query($sql_add_user_id_to_repairs)) {
        error_log("Errore ALTER TABLE per 'repairs.user_id': " . $conn_init->error);
    } else {
        $sql_add_fk_to_repairs_user_id = "ALTER TABLE repairs ADD CONSTRAINT fk_repairs_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL";
        if (!$conn_init->query($sql_add_fk_to_repairs_user_id)) {
            error_log("Errore nell'aggiunta della FK 'fk_repairs_user_id': " . $conn_init->error);
        }
    }
}

// --- NUOVA TABELLA: repair_service_items (Catalogo degli Interventi Preimpostati) ---
$sql_create_repair_service_items_table = "
CREATE TABLE IF NOT EXISTS repair_service_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE, /* Nome breve dell'intervento (es. Diagnosi, Sostituzione LCD) */
    description TEXT, /* Descrizione più dettagliata */
    default_cost DECIMAL(10, 2) NOT NULL DEFAULT 0.00, /* Costo standard dell'intervento */
    is_active TINYINT(1) NOT NULL DEFAULT 1, /* Flag per attivare/disattivare il servizio */
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn_init->query($sql_create_repair_service_items_table)) {
    error_log("Errore nella creazione della tabella 'repair_service_items': " . $conn_init->error);
}

// --- NUOVA TABELLA: repair_repair_items (Dettaglio Interventi per ogni Riparazione) ---
$sql_create_repair_repair_items_table = "
CREATE TABLE IF NOT EXISTS repair_repair_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    repair_id INT NOT NULL, /* Collega alla riparazione */
    service_item_id INT NULL, /* Collega all'intervento preimpostato (NULL se personalizzato) */
    custom_description VARCHAR(255) NOT NULL, /* Descrizione specifica per questa riga (personalizzata o del servizio) */
    unit_cost DECIMAL(10, 2) NOT NULL, /* Costo di questa istanza dell'intervento */
    quantity INT NOT NULL DEFAULT 1, /* Quantità dell'intervento (sempre 1 per ora) */
    item_total DECIMAL(10, 2) NOT NULL, /* Costo totale per questa riga (unit_cost * quantity) */
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (repair_id) REFERENCES repairs(id) ON DELETE CASCADE,
    FOREIGN KEY (service_item_id) REFERENCES repair_service_items(id) ON DELETE SET NULL
)";

if (!$conn_init->query($sql_create_repair_repair_items_table)) {
    error_log("Errore nella creazione della tabella 'repair_repair_items': " . $conn_init->error);
}


// --- NUOVA TABELLA: products (Catalogo Prodotti per Ordini Commerciali) ---
$sql_create_products_table = "
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_code VARCHAR(100) UNIQUE NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    description TEXT,
    default_price_net DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
if (!$conn_init->query($sql_create_products_table)) {
    error_log("Errore nella creazione della tabella 'products': " . $conn_init->error);
}

// --- NUOVA TABELLA: commercial_orders (Ordini Commerciali Principali) ---
$sql_create_commercial_orders_table = "
CREATE TABLE IF NOT EXISTS commercial_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contact_id INT NOT NULL,
    commercial_user_id INT NOT NULL,
    order_date DATE NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Ordine Inserito',
    expected_shipping_date DATE,
    shipping_address VARCHAR(255),
    shipping_city VARCHAR(100),
    shipping_zip VARCHAR(5),
    shipping_province VARCHAR(2),
    carrier VARCHAR(100),
    shipping_costs DECIMAL(10, 2),
    notes_commercial TEXT,
    notes_technical TEXT,
    total_amount DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE RESTRICT,
    FOREIGN KEY (commercial_user_id) REFERENCES users(id) ON DELETE SET NULL
)";
if (!$conn_init->query($sql_create_commercial_orders_table)) {
    error_log("Errore nella creazione della tabella 'commercial_orders': " . $conn_init->error);
}

// --- NUOVA TABELLA: commercial_order_items (Articoli degli Ordini Commerciali) ---
$sql_create_commercial_order_items_table = "
CREATE TABLE IF NOT EXISTS commercial_order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NULL, /* Collega a products.id se del catalogo; NULL se personalizzato */
    description VARCHAR(255) NOT NULL,
    ordered_quantity INT NOT NULL DEFAULT 1,
    ordered_unit_price DECIMAL(10, 2) NOT NULL,
    actual_shipped_quantity INT NOT NULL DEFAULT 0,
    actual_shipped_serial_number VARCHAR(100),
    notes_item TEXT,
    item_total DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES commercial_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
)";
if (!$conn_init->query($sql_create_commercial_order_items_table)) {
    error_log("Errore nella creazione della tabella 'commercial_order_items': " . $conn_init->error);
}


// --- AGGIORNAMENTI SCHEMA PER LE TABELLE ESISTENTI (ALTER TABLE IF NOT EXISTS COLUMN) ---

// Aggiungi colonne alla tabella 'products'
if (columnExists($conn_init, 'products', 'id')) {
    if (!columnExists($conn_init, 'products', 'product_code')) {
        if (!$conn_init->query("ALTER TABLE products ADD COLUMN product_code VARCHAR(100) UNIQUE NOT NULL")) { error_log("Errore ALTER TABLE per 'products.product_code': " . $conn_init->error); }
    }
    if (!columnExists($conn_init, 'products', 'product_name')) {
        if (!$conn_init->query("ALTER TABLE products ADD COLUMN product_name VARCHAR(255) NOT NULL")) { error_log("Errore ALTER TABLE per 'products.product_name': " . $conn_init->error); }
    }
    if (!columnExists($conn_init, 'products', 'description')) {
        if (!$conn_init->query("ALTER TABLE products ADD COLUMN description TEXT")) { error_log("Errore ALTER TABLE per 'products.description': " . $conn_init->error); }
    }
    if (!columnExists($conn_init, 'products', 'default_price_net')) {
        if (!$conn_init->query("ALTER TABLE products ADD COLUMN default_price_net DECIMAL(10, 2) NOT NULL DEFAULT 0.00")) { error_log("Errore ALTER TABLE per 'products.default_price_net': " . $conn_init->error); }
    }
    if (!columnExists($conn_init, 'products', 'is_active')) {
        if (!$conn_init->query("ALTER TABLE products ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1")) { error_log("Errore ALTER TABLE per 'products.is_active': " . $conn_init->error); }
    }
    // Aggiungi indici UNIQUE se non esistono (solo dopo la creazione della colonna)
    $result_check_unique_code = $conn_init->query("SHOW KEYS FROM products WHERE Key_name = 'product_code'");
    if ($result_check_unique_code->num_rows == 0) {
        if (!$conn_init->query("ALTER TABLE products ADD UNIQUE (product_code)")) { error_log("Errore ALTER TABLE per UNIQUE 'products.product_code': " . $conn_init->error); }
    }
}


// Aggiungi colonne alla tabella 'commercial_orders'
if (columnExists($conn_init, 'commercial_orders', 'id')) { // Assicurati che la tabella esista
    if (!columnExists($conn_init, 'commercial_orders', 'expected_shipping_date')) {
        if (!$conn_init->query("ALTER TABLE commercial_orders ADD COLUMN expected_shipping_date DATE")) { error_log("Errore ALTER TABLE per 'commercial_orders.expected_shipping_date': " . $conn_init->error); }
    }
    if (!columnExists($conn_init, 'commercial_orders', 'shipping_address')) {
        if (!$conn_init->query("ALTER TABLE commercial_orders ADD COLUMN shipping_address VARCHAR(255)")) { error_log("Errore ALTER TABLE per 'commercial_orders.shipping_address': " . $conn_init->error); }
    }
    if (!columnExists($conn_init, 'commercial_orders', 'shipping_city')) {
        if (!$conn_init->query("ALTER TABLE commercial_orders ADD COLUMN shipping_city VARCHAR(100)")) { error_log("Errore ALTER TABLE per 'commercial_orders.shipping_city': " . $conn_init->error); }
    }
    if (!columnExists($conn_init, 'commercial_orders', 'shipping_zip')) {
        if (!$conn_init->query("ALTER TABLE commercial_orders ADD COLUMN shipping_zip VARCHAR(5)")) { error_log("Errore ALTER TABLE per 'commercial_orders.shipping_zip': " . $conn_init->error); }
    }
    if (!columnExists($conn_init, 'commercial_orders', 'shipping_province')) {
        if (!$conn_init->query("ALTER TABLE commercial_orders ADD COLUMN shipping_province VARCHAR(2)")) { error_log("Errore ALTER TABLE per 'commercial_orders.shipping_province': " . $conn_init->error); }
    }
    if (!columnExists($conn_init, 'commercial_orders', 'carrier')) {
        if (!$conn_init->query("ALTER TABLE commercial_orders ADD COLUMN carrier VARCHAR(100)")) { error_log("Errore ALTER TABLE per 'commercial_orders.carrier': " . $conn_init->error); }
    }
    if (!columnExists($conn_init, 'commercial_orders', 'shipping_costs')) {
        if (!$conn_init->query("ALTER TABLE commercial_orders ADD COLUMN shipping_costs DECIMAL(10, 2)")) { error_log("Errore ALTER TABLE per 'commercial_orders.shipping_costs': " . $conn_init->error); }
    }
    if (!columnExists($conn_init, 'commercial_orders', 'notes_commercial')) {
        if (!$conn_init->query("ALTER TABLE commercial_orders ADD COLUMN notes_commercial TEXT")) { error_log("Errore ALTER TABLE per 'commercial_orders.notes_commercial': " . $conn_init->error); }
    }
    if (!columnExists($conn_init, 'commercial_orders', 'notes_technical')) {
        if (!$conn_init->query("ALTER TABLE commercial_orders ADD COLUMN notes_technical TEXT")) { error_log("Errore ALTER TABLE per 'commercial_orders.notes_technical': " . $conn_init->error); }
    }
    if (!columnExists($conn_init, 'commercial_orders', 'total_amount')) {
        if (!$conn_init->query("ALTER TABLE commercial_orders ADD COLUMN total_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00")) { error_log("Errore ALTER TABLE per 'commercial_orders.total_amount': " . $conn_init->error); }
    }
    // Assicurati che le FK esistano se le colonne sono presenti
    $result_check_fk_contact = $conn_init->query("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'commercial_orders' AND COLUMN_NAME = 'contact_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
    if ($result_check_fk_contact->num_rows == 0 && columnExists($conn_init, 'commercial_orders', 'contact_id')) {
        if (!$conn_init->query("ALTER TABLE commercial_orders ADD CONSTRAINT fk_commercial_orders_contact FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE RESTRICT")) { error_log("Errore ALTER TABLE per FK 'fk_commercial_orders_contact': " . $conn_init->error); }
    }
    $result_check_fk_user = $conn_init->query("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'commercial_orders' AND COLUMN_NAME = 'commercial_user_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
    if ($result_check_fk_user->num_rows == 0 && columnExists($conn_init, 'commercial_orders', 'commercial_user_id')) {
        if (!$conn_init->query("ALTER TABLE commercial_orders ADD CONSTRAINT fk_commercial_orders_user FOREIGN KEY (commercial_user_id) REFERENCES users(id) ON DELETE SET NULL")) { error_log("Errore ALTER TABLE per FK 'fk_commercial_orders_user': " . $conn_init->error); }
    }
}


// Aggiungi colonne alla tabella 'commercial_order_items'
if (columnExists($conn_init, 'commercial_order_items', 'id')) { // Assicurati che la tabella esista
    if (!columnExists($conn_init, 'commercial_order_items', 'product_id')) {
        if (!$conn_init->query("ALTER TABLE commercial_order_items ADD COLUMN product_id INT NULL AFTER order_id")) { error_log("Errore ALTER TABLE per 'commercial_order_items.product_id': " . $conn_init->error); }
    }
    if (!columnExists($conn_init, 'commercial_order_items', 'description')) {
        if (!$conn_init->query("ALTER TABLE commercial_order_items ADD COLUMN description VARCHAR(255) NOT NULL")) { error_log("Errore ALTER TABLE per 'commercial_order_items.description': " . $conn_init->error); }
    }
    if (!columnExists($conn_init, 'commercial_order_items', 'ordered_quantity')) {
        if (!$conn_init->query("ALTER TABLE commercial_order_items ADD COLUMN ordered_quantity INT NOT NULL DEFAULT 1")) { error_log("Errore ALTER TABLE per 'commercial_order_items.ordered_quantity': " . $conn_init->error); }
    }
    if (!columnExists($conn_init, 'commercial_order_items', 'ordered_unit_price')) {
        if (!$conn_init->query("ALTER TABLE commercial_order_items ADD COLUMN ordered_unit_price DECIMAL(10, 2) NOT NULL")) { error_log("Errore ALTER TABLE per 'commercial_order_items.ordered_unit_price': " . $conn_init->error); }
    }
    if (!columnExists($conn_init, 'commercial_order_items', 'actual_shipped_quantity')) {
        if (!$conn_init->query("ALTER TABLE commercial_order_items ADD COLUMN actual_shipped_quantity INT NOT NULL DEFAULT 0")) { error_log("Errore ALTER TABLE per 'commercial_order_items.actual_shipped_quantity': " . $conn_init->error); }
    }
    if (!columnExists($conn_init, 'commercial_order_items', 'actual_shipped_serial_number')) {
        if (!$conn_init->query("ALTER TABLE commercial_order_items ADD COLUMN actual_shipped_serial_number VARCHAR(100)")) { error_log("Errore ALTER TABLE per 'commercial_order_items.actual_shipped_serial_number': " . $conn_init->error); }
    }
    if (!columnExists($conn_init, 'commercial_order_items', 'notes_item')) {
        if (!$conn_init->query("ALTER TABLE commercial_order_items ADD COLUMN notes_item TEXT")) { error_log("Errore ALTER TABLE per 'commercial_order_items.notes_item': " . $conn_init->error); }
    }
    if (!columnExists($conn_init, 'commercial_order_items', 'item_total')) {
        if (!$conn_init->query("ALTER TABLE commercial_order_items ADD COLUMN item_total DECIMAL(10, 2) NOT NULL")) { error_log("Errore ALTER TABLE per 'commercial_order_items.item_total': " . $conn_init->error); }
    }
    // Assicurati che le FK esistano se le colonne sono presenti
    $result_check_fk_order = $conn_init->query("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'commercial_order_items' AND COLUMN_NAME = 'order_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
    if ($result_check_fk_order->num_rows == 0 && columnExists($conn_init, 'commercial_order_items', 'order_id')) {
        if (!$conn_init->query("ALTER TABLE commercial_order_items ADD CONSTRAINT fk_commercial_order_items_order FOREIGN KEY (order_id) REFERENCES commercial_orders(id) ON DELETE CASCADE")) { error_log("Errore ALTER TABLE per FK 'fk_commercial_order_items_order': " . $conn_init->error); }
    }
    $result_check_fk_product = $conn_init->query("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'commercial_order_items' AND COLUMN_NAME = 'product_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
    if ($result_check_fk_product->num_rows == 0 && columnExists($conn_init, 'commercial_order_items', 'product_id')) {
        if (!$conn_init->query("ALTER TABLE commercial_order_items ADD CONSTRAINT fk_commercial_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL")) { error_log("Errore ALTER TABLE per FK 'fk_commercial_order_items_product': " . $conn_init->error); }
    }
}


// NUOVO: Aggiungi un utente admin predefinito SE LA TABELLA UTENTI È COMPLETAMENTE VUOTA
$check_any_user_query = "SELECT COUNT(*) as total FROM users"; // Conta TUTTI gli utenti
$result_any_user_check = $conn_init->query($check_any_user_query);
$row_any_user_check = $result_any_user_check->fetch_assoc();

if ($row_any_user_check['total'] == 0) { // Crea l'admin predefinito SOLO se non ci sono utenti
    $default_admin_username = 'admin';
    // Password hashata per 'password123'.
    $default_admin_password_hash = password_hash('password123', PASSWORD_BCRYPT); 
    $default_admin_role = 'admin';

    $stmt_insert_admin = $conn_init->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
    $stmt_insert_admin->bind_param("sss", $default_admin_username, $default_admin_password_hash, $default_admin_role);

    if ($stmt_insert_admin->execute()) {
        error_log("Utente 'admin' predefinito creato con successo (tabella utenti vuota).");
    } else {
        error_log("Errore durante la creazione dell'utente 'admin' predefinito: " . $stmt_init->error);
    }
    $stmt_insert_admin->close();
}


$conn_init->close(); // Chiudi la connessione usata per l'inizializzazione dello schema

// Non chiudiamo la connessione principale qui, verrà gestita dai modelli/controller
?>
