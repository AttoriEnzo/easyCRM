<?php
// app/models/CompanySetting.php

require_once __DIR__ . '/../config/database.php';

class CompanySetting {
    private $conn;
    private $table_name = "company_settings";

    public $id;
    public $company_name;
    public $address;
    public $city;
    public $zip;
    public $province;
    public $vat_number;
    public $tax_code;
    public $phone;
    public $email;
    public $pec;
    public $sdi;
    public $logo_url;
    public $created_at;
    public $updated_at;

    public function __construct() {
        $this->conn = getDbConnection();
    }

    // Metodo per leggere le impostazioni dell'azienda (dovrebbe esserci una sola riga, con ID 1)
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = 1 LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $settings = $result->fetch_assoc();
        $stmt->close();
        return $settings;
    }

    // Metodo per creare o aggiornare le impostazioni dell'azienda
    // Dato che dovrebbe esserci una sola riga (ID 1), si userà sempre UPDATE o INSERT se non esiste
    public function save() {
        // Controlla se la riga con ID 1 esiste già
        $existing_settings = $this->read();

        if ($existing_settings) {
            // Se esiste, aggiorna
            $query = "UPDATE " . $this->table_name . " SET
                        company_name = ?,
                        address = ?,
                        city = ?,
                        zip = ?,
                        province = ?,
                        vat_number = ?,
                        tax_code = ?,
                        phone = ?,
                        email = ?,
                        pec = ?,
                        sdi = ?,
                        logo_url = ?,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = 1";
            $stmt = $this->conn->prepare($query);

            // Sanifica i dati
            $this->company_name = htmlspecialchars(strip_tags($this->company_name));
            $this->address = htmlspecialchars(strip_tags($this->address));
            $this->city = htmlspecialchars(strip_tags($this->city));
            $this->zip = htmlspecialchars(strip_tags($this->zip));
            $this->province = htmlspecialchars(strip_tags($this->province));
            $this->vat_number = htmlspecialchars(strip_tags($this->vat_number));
            $this->tax_code = htmlspecialchars(strip_tags($this->tax_code));
            $this->phone = htmlspecialchars(strip_tags($this->phone));
            $this->email = htmlspecialchars(strip_tags($this->email));
            $this->pec = htmlspecialchars(strip_tags($this->pec));
            $this->sdi = htmlspecialchars(strip_tags($this->sdi));
            $this->logo_url = htmlspecialchars(strip_tags($this->logo_url));

            // Binding dei parametri
            $stmt->bind_param("ssssssssssss",
                $this->company_name,
                $this->address,
                $this->city,
                $this->zip,
                $this->province,
                $this->vat_number,
                $this->tax_code,
                $this->phone,
                $this->email,
                $this->pec,
                $this->sdi,
                $this->logo_url
            );
        } else {
            // Se non esiste, crea (inserisci con ID 1)
            $query = "INSERT INTO " . $this->table_name . " (id, company_name, address, city, zip, province, vat_number, tax_code, phone, email, pec, sdi, logo_url) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);

            // Sanifica i dati
            $this->company_name = htmlspecialchars(strip_tags($this->company_name));
            $this->address = htmlspecialchars(strip_tags($this->address));
            $this->city = htmlspecialchars(strip_tags($this->city));
            $this->zip = htmlspecialchars(strip_tags($this->zip));
            $this->province = htmlspecialchars(strip_tags($this->province));
            $this->vat_number = htmlspecialchars(strip_tags($this->vat_number));
            $this->tax_code = htmlspecialchars(strip_tags($this->tax_code));
            $this->phone = htmlspecialchars(strip_tags($this->phone));
            $this->email = htmlspecialchars(strip_tags($this->email));
            $this->pec = htmlspecialchars(strip_tags($this->pec));
            $this->sdi = htmlspecialchars(strip_tags($this->sdi));
            $this->logo_url = htmlspecialchars(strip_tags($this->logo_url));

            // Binding dei parametri
            $stmt->bind_param("ssssssssssss",
                $this->company_name,
                $this->address,
                $this->city,
                $this->zip,
                $this->province,
                $this->vat_number,
                $this->tax_code,
                $this->phone,
                $this->email,
                $this->pec,
                $this->sdi,
                $this->logo_url
            );
        }

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true];
        }
        error_log("Errore salvataggio impostazioni azienda: " . $stmt->error);
        $error_message = $stmt->error;
        $stmt->close();
        return ['success' => false, 'error' => $error_message];
    }

    // Metodo per chiudere la connessione al database
    public function closeConnection() {
        if ($this->conn && $this->conn->ping()) {
            $this->conn->close();
        }
    }
}
