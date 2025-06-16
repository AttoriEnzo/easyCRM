<?php
// app/models/CommercialOrderItem.php

require_once __DIR__ . '/../config/database.php';

class CommercialOrderItem {
    private $conn;
    private $table_name = "commercial_order_items";

    public $id;
    public $order_id;
    public $product_id;
    public $description;
    public $ordered_quantity;
    public $ordered_unit_price;
    public $ordered_item_total;
    public $shipped_quantity;
    public $actual_shipped_serial_numbers;
    public $item_status;
    public $notes_item;
    public $created_at;

    public function __construct() {
        $this->conn = getDbConnection();
    }

    // Metodo per creare una nuova voce d'ordine
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (order_id, product_id, description, ordered_quantity, ordered_unit_price, ordered_item_total, shipped_quantity, actual_shipped_serial_numbers, item_status, notes_item) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        // Sanifica i dati
        $this->order_id = htmlspecialchars(strip_tags($this->order_id));
        $this->product_id = htmlspecialchars(strip_tags($this->product_id));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->ordered_quantity = htmlspecialchars(strip_tags($this->ordered_quantity));
        $this->ordered_unit_price = htmlspecialchars(strip_tags($this->ordered_unit_price));
        $this->ordered_item_total = htmlspecialchars(strip_tags($this->ordered_item_total));
        $this->shipped_quantity = htmlspecialchars(strip_tags($this->shipped_quantity));
        $this->actual_shipped_serial_numbers = htmlspecialchars(strip_tags($this->actual_shipped_serial_numbers));
        $this->item_status = htmlspecialchars(strip_tags($this->item_status));
        $this->notes_item = htmlspecialchars(strip_tags($this->notes_item));

        // Gestisci il campo product_id nullable
        $product_id = $this->product_id === '' ? null : $this->product_id;
        $actual_shipped_serial_numbers = $this->actual_shipped_serial_numbers === '' ? null : $this->actual_shipped_serial_numbers;
        $notes_item = $this->notes_item === '' ? null : $this->notes_item;

        // Binding dei parametri
        $stmt->bind_param("iisiddissi",
            $this->order_id,
            $product_id, // Può essere null
            $this->description,
            $this->ordered_quantity,
            $this->ordered_unit_price,
            $this->ordered_item_total,
            $this->shipped_quantity,
            $actual_shipped_serial_numbers, // Può essere null
            $this->item_status,
            $notes_item // Può essere null
        );

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true];
        }
        error_log("Errore creazione voce ordine commerciale: " . $stmt->error);
        $error_message = $stmt->error;
        $stmt->close();
        return ['success' => false, 'error' => $error_message];
    }

    // Metodo per leggere tutte le voci d'ordine per un dato ordine
    public function readByOrderId($order_id) {
        $query = "
            SELECT
                coi.id, coi.order_id, coi.product_id, coi.description, coi.ordered_quantity,
                coi.ordered_unit_price, coi.ordered_item_total, coi.shipped_quantity,
                coi.actual_shipped_serial_numbers, coi.item_status, coi.notes_item, coi.created_at,
                p.product_code, p.product_type, p.product_name, p.default_price_net, p.default_price_gross, p.amperes, p.volts, p.other_specs
            FROM " . $this->table_name . " coi
            LEFT JOIN products p ON coi.product_id = p.id
            WHERE coi.order_id = ?
            ORDER BY coi.id ASC"; // Ordina per ID per mantenere l'ordine di inserimento
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        $stmt->close();
        return $items;
    }
    
    // Metodo per eliminare tutte le voci d'ordine per un dato ordine (utile in update "delete all, insert all")
    public function deleteByOrderId($order_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE order_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $order_id);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }
        error_log("Errore eliminazione voci ordine commerciale per order_id: " . $order_id . " - " . $stmt->error);
        $stmt->close();
        return false;
    }

    // Metodo per aggiornare una voce d'ordine esistente
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET
                    order_id = ?,
                    product_id = ?,
                    description = ?,
                    ordered_quantity = ?,
                    ordered_unit_price = ?,
                    ordered_item_total = ?,
                    shipped_quantity = ?,
                    actual_shipped_serial_numbers = ?,
                    item_status = ?,
                    notes_item = ?
                WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        // Sanifica i dati
        $this->order_id = htmlspecialchars(strip_tags($this->order_id));
        $this->product_id = htmlspecialchars(strip_tags($this->product_id));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->ordered_quantity = htmlspecialchars(strip_tags($this->ordered_quantity));
        $this->ordered_unit_price = htmlspecialchars(strip_tags($this->ordered_unit_price));
        $this->ordered_item_total = htmlspecialchars(strip_tags($this->ordered_item_total));
        $this->shipped_quantity = htmlspecialchars(strip_tags($this->shipped_quantity));
        $this->actual_shipped_serial_numbers = htmlspecialchars(strip_tags($this->actual_shipped_serial_numbers));
        $this->item_status = htmlspecialchars(strip_tags($this->item_status));
        $this->notes_item = htmlspecialchars(strip_tags($this->notes_item));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Gestisci il campo product_id nullable
        $product_id = $this->product_id === '' ? null : $this->product_id;
        $actual_shipped_serial_numbers = $this->actual_shipped_serial_numbers === '' ? null : $this->actual_shipped_serial_numbers;
        $notes_item = $this->notes_item === '' ? null : $this->notes_item;

        // Binding dei parametri
        $stmt->bind_param("iisiddissii",
            $this->order_id,
            $product_id,
            $this->description,
            $this->ordered_quantity,
            $this->ordered_unit_price,
            $this->ordered_item_total,
            $this->shipped_quantity,
            $actual_shipped_serial_numbers,
            $this->item_status,
            $notes_item,
            $this->id
        );

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true];
        }
        error_log("Errore aggiornamento voce ordine commerciale: " . $stmt->error);
        $error_message = $stmt->error;
        $stmt->close();
        return ['success' => false, 'error' => $error_message];
    }

    // Metodo per eliminare una voce d'ordine specifica
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }
        error_log("Errore eliminazione voce ordine commerciale: " . $stmt->error);
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
