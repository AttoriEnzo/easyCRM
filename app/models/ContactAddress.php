<?php
// app/models/ContactAddress.php

require_once __DIR__ . '/../config/database.php';

class ContactAddress {
    private $conn;
    private $table_name = "contact_addresses";

    public $id;
    public $contact_id;
    public $address_type;
    public $address;
    public $city;
    public $zip;
    public $province;
    public $is_default_shipping;
    public $created_at;
    public $updated_at;

    public function __construct() {
        $this->conn = getDbConnection();
    }

    // Metodo per creare un nuovo indirizzo contatto
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (contact_id, address_type, address, city, zip, province, is_default_shipping) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        // Sanifica i dati
        $this->contact_id = htmlspecialchars(strip_tags($this->contact_id));
        $this->address_type = htmlspecialchars(strip_tags($this->address_type));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->city = htmlspecialchars(strip_tags($this->city));
        $this->zip = htmlspecialchars(strip_tags($this->zip));
        $this->province = htmlspecialchars(strip_tags($this->province));
        $this->is_default_shipping = htmlspecialchars(strip_tags($this->is_default_shipping));

        // Binding dei parametri
        $stmt->bind_param("issssii",
            $this->contact_id,
            $this->address_type,
            $this->address,
            $this->city,
            $this->zip,
            $this->province,
            $this->is_default_shipping
        );

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true];
        }
        error_log("Errore creazione indirizzo contatto: " . $stmt->error);
        $error_message = $stmt->error;
        $stmt->close();
        return ['success' => false, 'error' => $error_message];
    }

    // Metodo per leggere tutti gli indirizzi per un dato contatto
    public function readByContactId($contact_id) {
        $query = "SELECT id, contact_id, address_type, address, city, zip, province, is_default_shipping, created_at, updated_at FROM " . $this->table_name . " WHERE contact_id = ? ORDER BY address_type ASC, id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $contact_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $addresses = [];
        while ($row = $result->fetch_assoc()) {
            $addresses[] = $row;
        }
        $stmt->close();
        return $addresses;
    }

    // Metodo per leggere un singolo indirizzo
    public function readOne($id) {
        $query = "SELECT id, contact_id, address_type, address, city, zip, province, is_default_shipping, created_at, updated_at FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $address = $result->fetch_assoc();
        $stmt->close();
        return $address;
    }

    // Metodo per aggiornare un indirizzo esistente
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET
                    contact_id = ?,
                    address_type = ?,
                    address = ?,
                    city = ?,
                    zip = ?,
                    province = ?,
                    is_default_shipping = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        // Sanifica i dati
        $this->contact_id = htmlspecialchars(strip_tags($this->contact_id));
        $this->address_type = htmlspecialchars(strip_tags($this->address_type));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->city = htmlspecialchars(strip_tags($this->city));
        $this->zip = htmlspecialchars(strip_tags($this->zip));
        $this->province = htmlspecialchars(strip_tags($this->province));
        $this->is_default_shipping = htmlspecialchars(strip_tags($this->is_default_shipping));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Binding dei parametri
        $stmt->bind_param("issssiii",
            $this->contact_id,
            $this->address_type,
            $this->address,
            $this->city,
            $this->zip,
            $this->province,
            $this->is_default_shipping,
            $this->id
        );

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true];
        }
        error_log("Errore aggiornamento indirizzo contatto: " . $stmt->error);
        $error_message = $stmt->error;
        $stmt->close();
        return ['success' => false, 'error' => $error_message];
    }

    // Metodo per eliminare un indirizzo
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }
        error_log("Errore eliminazione indirizzo contatto: " . $stmt->error);
        $stmt->close();
        return false;
    }
    
    /**
     * Metodo per reimpostare tutti gli altri indirizzi di spedizione di un contatto come non predefiniti,
     * tranne quello specificato (o tutti se none specificato).
     * @param int $contact_id L'ID del contatto.
     * @param int|null $exclude_address_id L'ID dell'indirizzo da escludere dalla reimpostazione.
     * @return bool True in caso di successo, False in caso di errore.
     */
    public function resetDefaultShippingForContact($contact_id, $exclude_address_id = null) {
        $query = "UPDATE " . $this->table_name . " SET is_default_shipping = 0 WHERE contact_id = ? AND address_type = 'Spedizione'";
        $params = "i";
        $values = [$contact_id];

        if ($exclude_address_id !== null) {
            $query .= " AND id != ?";
            $params .= "i";
            $values[] = $exclude_address_id;
        }

        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Errore preparazione query resetDefaultShippingForContact: " . $this->conn->error);
            return false;
        }
        
        $stmt->bind_param($params, ...$values);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }
        error_log("Errore reset default shipping for contact: " . $stmt->error);
        $stmt->close();
        return false;
    }


    // Metodo per chiudere la connessione al database
    public function closeConnection() {
        if ($this->conn && $this->conn->ping()) {
            $this->conn->close();
        }
    }
}
