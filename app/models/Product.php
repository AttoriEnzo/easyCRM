<?php
// app/models/Product.php

require_once __DIR__ . '/../config/database.php';

class Product {
    private $conn;
    private $table_name = "products";

    public $id;
    public $product_code;
    public $product_type;
    public $product_name;
    public $description;
    public $default_price_net;
    public $default_price_gross;
    public $amperes;
    public $volts;
    public $other_specs;
    public $is_active;
    public $created_at;
    public $updated_at;

    public function __construct() {
        $this->conn = getDbConnection();
    }

    // Metodo per creare un nuovo prodotto
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (product_code, product_type, product_name, description, default_price_net, default_price_gross, amperes, volts, other_specs, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        // Sanifica i dati
        $this->product_code = htmlspecialchars(strip_tags($this->product_code));
        $this->product_type = htmlspecialchars(strip_tags($this->product_type));
        $this->product_name = htmlspecialchars(strip_tags($this->product_name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->default_price_net = htmlspecialchars(strip_tags($this->default_price_net));
        $this->default_price_gross = htmlspecialchars(strip_tags($this->default_price_gross));
        $this->amperes = htmlspecialchars(strip_tags($this->amperes));
        $this->volts = htmlspecialchars(strip_tags($this->volts));
        $this->other_specs = htmlspecialchars(strip_tags($this->other_specs));
        $this->is_active = htmlspecialchars(strip_tags($this->is_active));

        // Binding dei parametri
        $stmt->bind_param("ssssddddsi",
            $this->product_code,
            $this->product_type,
            $this->product_name,
            $this->description,
            $this->default_price_net,
            $this->default_price_gross,
            $this->amperes,
            $this->volts,
            $this->other_specs,
            $this->is_active
        );

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true];
        }
        error_log("Errore creazione prodotto: " . $stmt->error);
        $error_message = $stmt->error;
        $stmt->close();
        return ['success' => false, 'error' => $error_message];
    }

    // Metodo per leggere tutti i prodotti (opzionalmente solo attivi o con ricerca)
    public function readAll($active_only = false, $search_query = '') {
        $query = "SELECT id, product_code, product_type, product_name, description, default_price_net, default_price_gross, amperes, volts, other_specs, is_active, created_at, updated_at FROM " . $this->table_name;
        $conditions = [];
        $params = [];
        $types = "";

        if ($active_only) {
            $conditions[] = "is_active = 1";
        }

        if (!empty($search_query)) {
            $search_term = "%" . $search_query . "%";
            $conditions[] = "(product_code LIKE ? OR product_type LIKE ? OR product_name LIKE ? OR description LIKE ?)";
            $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
            $types .= "ssss";
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $query .= " ORDER BY product_name ASC";

        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Errore nella preparazione della query in Product->readAll: " . $this->conn->error);
            return [];
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        $stmt->close();
        return $products;
    }

    // Metodo per leggere un singolo prodotto
    public function readOne($id) {
        $query = "SELECT id, product_code, product_type, product_name, description, default_price_net, default_price_gross, amperes, volts, other_specs, is_active, created_at, updated_at FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();
        return $product;
    }

    // Metodo per aggiornare un prodotto esistente
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET
                    product_code = ?,
                    product_type = ?,
                    product_name = ?,
                    description = ?,
                    default_price_net = ?,
                    default_price_gross = ?,
                    amperes = ?,
                    volts = ?,
                    other_specs = ?,
                    is_active = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        // Sanifica i dati
        $this->product_code = htmlspecialchars(strip_tags($this->product_code));
        $this->product_type = htmlspecialchars(strip_tags($this->product_type));
        $this->product_name = htmlspecialchars(strip_tags($this->product_name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->default_price_net = htmlspecialchars(strip_tags($this->default_price_net));
        $this->default_price_gross = htmlspecialchars(strip_tags($this->default_price_gross));
        $this->amperes = htmlspecialchars(strip_tags($this->amperes));
        $this->volts = htmlspecialchars(strip_tags($this->volts));
        $this->other_specs = htmlspecialchars(strip_tags($this->other_specs));
        $this->is_active = htmlspecialchars(strip_tags($this->is_active));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Binding dei parametri
        $stmt->bind_param("ssssdddsii",
            $this->product_code,
            $this->product_type,
            $this->product_name,
            $this->description,
            $this->default_price_net,
            $this->default_price_gross,
            $this->amperes,
            $this->volts,
            $this->other_specs,
            $this->is_active,
            $this->id
        );

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true];
        }
        error_log("Errore aggiornamento prodotto: " . $stmt->error);
        $error_message = $stmt->error;
        $stmt->close();
        return ['success' => false, 'error' => $error_message];
    }

    // Metodo per eliminare un prodotto
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }
        error_log("Errore eliminazione prodotto: " . $stmt->error);
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
