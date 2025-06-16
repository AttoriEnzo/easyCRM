<?php
// app/models/RepairRepairItem.php

require_once __DIR__ . '/../config/database.php';

class RepairRepairItem {
    private $conn;
    private $table_name = "repair_repair_items";

    public $id;
    public $repair_id;
    public $service_item_id; // Nullable for custom items
    public $custom_description;
    public $unit_cost;
    public $quantity; // Always 1 for now
    public $item_total;
    public $created_at;

    public function __construct() {
        $this->conn = getDbConnection();
    }

    /**
     * Creates a new repair repair item.
     * @return bool True on success, False on error.
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (repair_id, service_item_id, custom_description, unit_cost, quantity, item_total) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        $this->repair_id = filter_var($this->repair_id, FILTER_VALIDATE_INT);
        $this->service_item_id = filter_var($this->service_item_id, FILTER_VALIDATE_INT) ?: null; // Can be null
        $this->custom_description = htmlspecialchars(strip_tags($this->custom_description));
        $this->unit_cost = filter_var($this->unit_cost, FILTER_VALIDATE_FLOAT);
        $this->quantity = filter_var($this->quantity, FILTER_VALIDATE_INT);
        $this->item_total = filter_var($this->item_total, FILTER_VALIDATE_FLOAT);

        // 'iisdds' -> integer, integer (nullable), string, double, double, double
        $stmt->bind_param("iisdds", $this->repair_id, $this->service_item_id, $this->custom_description, $this->unit_cost, $this->quantity, $this->item_total);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }
        error_log("Error creating repair repair item: " . $stmt->error);
        $stmt->close();
        return false;
    }

    /**
     * Reads all repair repair items for a given repair_id, including service item details if applicable.
     * @param int $repair_id The ID of the repair.
     * @return array An array of repair repair items.
     */
    public function readByRepairId($repair_id) {
        $query = "SELECT rri.*, rsi.name as service_item_name, rsi.description as service_item_description
                  FROM " . $this->table_name . " rri
                  LEFT JOIN repair_service_items rsi ON rri.service_item_id = rsi.id
                  WHERE rri.repair_id = ? ORDER BY rri.id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $repair_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        $stmt->close();
        return $items;
    }

    /**
     * Deletes all repair repair items for a given repair_id.
     * This is useful for clearing and re-inserting all items when updating a repair.
     * @param int $repair_id The ID of the repair whose items should be deleted.
     * @return bool True on success, False on error.
     */
    public function deleteByRepairId($repair_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE repair_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $repair_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }
        error_log("Error deleting repair repair items for repair_id " . $repair_id . ": " . $stmt->error);
        $stmt->close();
        return false;
    }

    public function closeConnection() {
        if ($this->conn && $this->conn->ping()) {
            $this->conn->close();
        }
    }
}
