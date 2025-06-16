<?php
// app/models/RepairServiceItem.php

require_once __DIR__ . '/../config/database.php';

class RepairServiceItem {
    private $conn;
    private $table_name = "repair_service_items";

    public $id;
    public $name;
    public $description;
    public $default_cost;
    public $is_active; // 1 = active, 0 = inactive
    public $created_at;

    public function __construct() {
        $this->conn = getDbConnection();
    }

    /**
     * Creates a new repair service item.
     * @return array An array with 'success' (bool) and 'error' (string, if present).
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (name, description, default_cost, is_active) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = !empty($this->description) ? htmlspecialchars(strip_tags($this->description)) : null;
        $this->default_cost = filter_var($this->default_cost, FILTER_VALIDATE_FLOAT);
        $this->is_active = (int)$this->is_active; // Ensure it's 0 or 1

        $stmt->bind_param("ssdi", $this->name, $this->description, $this->default_cost, $this->is_active);

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true];
        }
        error_log("Error creating repair service item: " . $stmt->error);
        $error_message = $stmt->error;
        $stmt->close();
        return ['success' => false, 'error' => $error_message];
    }

    /**
     * Reads all repair service items, optionally filtered by active status and search query.
     * @param bool $active_only If true, only active items are returned.
     * @param string $search_query Optional search term.
     * @return array An array of service items.
     */
    public function readAll($active_only = false, $search_query = '') {
        $query = "SELECT id, name, description, default_cost, is_active, created_at FROM " . $this->table_name;
        $conditions = [];
        $bind_values = [];
        $types = "";

        if ($active_only) {
            $conditions[] = "is_active = 1";
        }

        if (!empty($search_query)) {
            $search_term = "%" . $search_query . "%";
            $conditions[] = "(name LIKE ? OR description LIKE ?)";
            $types .= "ss";
            $bind_values[] = &$search_term;
            $bind_values[] = &$search_term;
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $query .= " ORDER BY name ASC";

        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error preparing query in RepairServiceItem->readAll: " . $this->conn->error);
            return [];
        }

        if (!empty($bind_values)) {
            call_user_func_array([$stmt, 'bind_param'], array_merge([$types], $bind_values));
        }

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
     * Reads a single repair service item.
     * @param int $id The ID of the item to read.
     * @return array|null An array containing the item data if found, otherwise null.
     */
    public function readOne($id) {
        $query = "SELECT id, name, description, default_cost, is_active, created_at FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        $stmt->close();
        return $item;
    }

    /**
     * Updates an existing repair service item.
     * @return array An array with 'success' (bool) and 'error' (string, if present).
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET name = ?, description = ?, default_cost = ?, is_active = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = !empty($this->description) ? htmlspecialchars(strip_tags($this->description)) : null;
        $this->default_cost = filter_var($this->default_cost, FILTER_VALIDATE_FLOAT);
        $this->is_active = (int)$this->is_active;
        $this->id = filter_var($this->id, FILTER_VALIDATE_INT);

        $stmt->bind_param("ssdii", $this->name, $this->description, $this->default_cost, $this->is_active, $this->id);

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true];
        }
        error_log("Error updating repair service item: " . $stmt->error);
        $error_message = $stmt->error;
        $stmt->close();
        return ['success' => false, 'error' => $error_message];
    }

    /**
     * Deletes a repair service item.
     * @param int $id The ID of the item to delete.
     * @return bool True on success, False on error.
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }
        error_log("Error deleting repair service item: " . $stmt->error);
        $stmt->close();
        return false;
    }

    /**
     * Finds a service item by its name to check for uniqueness.
     * @param string $name The name to check.
     * @param int|null $exclude_id ID to exclude if in edit mode.
     * @return array|null The item data if found, otherwise null.
     */
    public function findByName($name, $exclude_id = null) {
        $query = "SELECT id, name FROM " . $this->table_name . " WHERE name = ?";
        if ($exclude_id !== null) {
            $query .= " AND id != ?";
        }
        $query .= " LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        if ($exclude_id !== null) {
            $stmt->bind_param("si", $name, $exclude_id);
        } else {
            $stmt->bind_param("s", $name);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        $stmt->close();
        return $item;
    }

    /**
     * Validates service item data.
     * @param array $data The data to validate.
     * @param bool $is_edit_mode True if validating for an edit operation.
     * @return array An array of error strings, empty if valid.
     */
    public function validate($data, $is_edit_mode = false) {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = "Il nome dell'intervento è obbligatorio.";
        } else {
            // Check for unique name
            $existing_item = $this->findByName($data['name'], $is_edit_mode ? ($data['id'] ?? null) : null);
            if ($existing_item) {
                $errors[] = "Esiste già un intervento con questo nome.";
            }
        }

        if (!isset($data['default_cost']) || !is_numeric($data['default_cost']) || floatval($data['default_cost']) < 0) {
            $errors[] = "Il costo predefinito deve essere un numero positivo o zero.";
        }

        // is_active is boolean, no specific validation needed here if handled by form control

        return $errors;
    }

    public function closeConnection() {
        if ($this->conn && $this->conn->ping()) {
            $this->conn->close();
        }
    }
}
