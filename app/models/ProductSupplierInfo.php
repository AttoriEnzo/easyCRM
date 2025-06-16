<?php
// app/models/ProductSupplierInfo.php

require_once __DIR__ . '/../config/database.php';

class ProductSupplierInfo {
    private $conn;
    private $table_name = "product_supplier_info";

    public $id;
    public $product_id;
    public $supplier_name;
    public $supplier_product_code;
    public $purchase_price;
    public $purchase_date;
    public $created_at;
    public $updated_at;

    public function __construct() {
        $this->conn = getDbConnection();
    }

    // Metodo per creare una nuova informazione fornitore
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (product_id, supplier_name, supplier_product_code, purchase_price, purchase_date) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        // Sanifica i dati
        $this->product_id = htmlspecialchars(strip_tags($this->product_id));
        $this->supplier_name = htmlspecialchars(strip_tags($this->supplier_name));
        $this->supplier_product_code = htmlspecialchars(strip_tags($this->supplier_product_code));
        $this->purchase_price = htmlspecialchars(strip_tags($this->purchase_price));
        $this->purchase_date = htmlspecialchars(strip_tags($this->purchase_date));

        // Binding dei parametri
        $stmt->bind_param("isdds",
            $this->product_id,
            $this->supplier_name,
            $this->supplier_product_code,
            $this->purchase_price,
            $this->purchase_date
        );

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true];
        }
        error_log("Errore creazione info fornitore prodotto: " . $stmt->error);
        $error_message = $stmt->error;
        $stmt->close();
        return ['success' => false, 'error' => $error_message];
    }

    // Metodo per leggere tutte le informazioni fornitore per un dato prodotto
    public function readByProductId($product_id) {
        $query = "SELECT id, product_id, supplier_name, supplier_product_code, purchase_price, purchase_date, created_at, updated_at FROM " . $this->table_name . " WHERE product_id = ? ORDER BY supplier_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $supplier_info = [];
        while ($row = $result->fetch_assoc()) {
            $supplier_info[] = $row;
        }
        $stmt->close();
        return $supplier_info;
    }
    
    // Metodo per leggere una singola informazione fornitore
    public function readOne($id) {
        $query = "SELECT id, product_id, supplier_name, supplier_product_code, purchase_price, purchase_date, created_at, updated_at FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $info = $result->fetch_assoc();
        $stmt->close();
        return $info;
    }


    // Metodo per aggiornare un'informazione fornitore esistente
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET
                    product_id = ?,
                    supplier_name = ?,
                    supplier_product_code = ?,
                    purchase_price = ?,
                    purchase_date = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        // Sanifica i dati
        $this->product_id = htmlspecialchars(strip_tags($this->product_id));
        $this->supplier_name = htmlspecialchars(strip_tags($this->supplier_name));
        $this->supplier_product_code = htmlspecialchars(strip_tags($this->supplier_product_code));
        $this->purchase_price = htmlspecialchars(strip_tags($this->purchase_price));
        $this->purchase_date = htmlspecialchars(strip_tags($this->purchase_date));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Binding dei parametri
        $stmt->bind_param("isdsi",
            $this->product_id,
            $this->supplier_name,
            $this->supplier_product_code,
            $this->purchase_price,
            $this->purchase_date,
            $this->id
        );

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true];
        }
        error_log("Errore aggiornamento info fornitore prodotto: " . $stmt->error);
        $error_message = $stmt->error;
        $stmt->close();
        return ['success' => false, 'error' => $error_message];
    }

    // Metodo per eliminare un'informazione fornitore
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }
        error_log("Errore eliminazione info fornitore prodotto: " . $stmt->error);
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
