<?php
// app/controllers/RepairServiceItemController.php

require_once __DIR__ . '/../models/RepairServiceItem.php';

class RepairServiceItemController {
    private $repairServiceItemModel;

    public function __construct(RepairServiceItem $repairServiceItemModel) {
        $this->repairServiceItemModel = $repairServiceItemModel;
    }

    /**
     * Mostra l'elenco di tutti gli interventi di servizio preimpostati.
     * Accessibile solo al Super Amministratore.
     */
    public function index() {
        // PERMESSO: Solo Super Amministratore può accedere a questa pagina.
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
            $_SESSION['message'] = "Accesso negato. Non hai i permessi per gestire gli interventi di servizio.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=dashboard");
            exit();
        }

        $search_query = $_GET['q'] ?? '';
        $service_items = $this->repairServiceItemModel->readAll(false, $search_query); // false = non solo attivi

        require_once __DIR__ . '/../views/repair_service_items/list.php';
    }

    /**
     * Gestisce l'aggiunta di un nuovo intervento di servizio.
     * Accessibile solo al Super Amministratore.
     */
    public function add() {
        // PERMESSO: Solo Super Amministratore può accedere a questa pagina.
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
            $_SESSION['message'] = "Accesso negato. Non hai i permessi per aggiungere interventi di servizio.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=dashboard");
            exit();
        }

        $item = []; // Variabile per pre-popolare il form (vuoto per l'aggiunta)
        $form_title = 'Aggiungi Nuovo Intervento di Servizio';
        $submit_button_text = 'Crea Intervento';
        $action_url = 'index.php?page=repair_services&action=add';
        $cancel_url = 'index.php?page=repair_services';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $item_data = [
                'name' => $_POST['name'] ?? null,
                'description' => $_POST['description'] ?? null,
                'default_cost' => $_POST['default_cost'] ?? null,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            $validation_errors = $this->repairServiceItemModel->validate($item_data);

            if (empty($validation_errors)) {
                foreach ($item_data as $key => $value) {
                    if (property_exists($this->repairServiceItemModel, $key)) {
                        $this->repairServiceItemModel->$key = $value;
                    }
                }

                $result = $this->repairServiceItemModel->create();

                if ($result['success']) {
                    $_SESSION['message'] = "Intervento di servizio aggiunto con successo!";
                    $_SESSION['message_type'] = "success";
                    header("Location: index.php?page=repair_services");
                    exit();
                } else {
                    $_SESSION['message'] = "Errore durante l'aggiunta dell'intervento: " . ($result['error'] ?? 'Errore sconosciuto.');
                    $_SESSION['message_type'] = "error";
                    $item = $item_data; // Pre-popola il form con i dati inviati
                    require_once __DIR__ . '/../views/repair_service_items/form.php';
                    return;
                }
            } else {
                $_SESSION['message'] = "Errore di validazione: " . implode(" ", $validation_errors);
                $_SESSION['message_type'] = "error";
                $item = $item_data; // Pre-popola il form con i dati inviati
                require_once __DIR__ . '/../views/repair_service_items/form.php';
                return;
            }
        }
        require_once __DIR__ . '/../views/repair_service_items/form.php';
    }

    /**
     * Gestisce la modifica di un intervento di servizio esistente.
     * Accessibile solo al Super Amministratore.
     * @param int $id L'ID dell'intervento da modificare.
     */
    public function edit($id) {
        // PERMESSO: Solo Super Amministratore può accedere a questa pagina.
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
            $_SESSION['message'] = "Accesso negato. Non hai i permessi per modificare interventi di servizio.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=dashboard");
            exit();
        }

        if (!$id) {
            $_SESSION['message'] = "ID intervento non specificato.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=repair_services");
            exit();
        }

        $item = $this->repairServiceItemModel->readOne($id);
        if (!$item) {
            $_SESSION['message'] = "Intervento di servizio non trovato.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=repair_services");
            exit();
        }

        $form_title = 'Modifica Intervento di Servizio';
        $submit_button_text = 'Aggiorna Intervento';
        $action_url = 'index.php?page=repair_services&action=edit&id=' . htmlspecialchars($id);
        $cancel_url = 'index.php?page=repair_services';


        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $item_data = [
                'id' => $id, // ID necessario per la validazione in modalità modifica
                'name' => $_POST['name'] ?? null,
                'description' => $_POST['description'] ?? null,
                'default_cost' => $_POST['default_cost'] ?? null,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            $validation_errors = $this->repairServiceItemModel->validate($item_data, true); // Passa true per is_edit_mode

            if (empty($validation_errors)) {
                foreach ($item_data as $key => $value) {
                    if (property_exists($this->repairServiceItemModel, $key)) {
                        $this->repairServiceItemModel->$key = $value;
                    }
                }

                $result = $this->repairServiceItemModel->update();

                if ($result['success']) {
                    $_SESSION['message'] = "Intervento di servizio aggiornato con successo!";
                    $_SESSION['message_type'] = "success";
                    header("Location: index.php?page=repair_services");
                    exit();
                } else {
                    $_SESSION['message'] = "Errore durante l'aggiornamento dell'intervento: " . ($result['error'] ?? 'Errore sconosciuto.');
                    $_SESSION['message_type'] = "error";
                    $item = array_merge($item, $item_data); // Mantiene dati originali e sovrascrive con POST
                    require_once __DIR__ . '/../views/repair_service_items/form.php';
                    return;
                }
            } else {
                $_SESSION['message'] = "Errore di validazione: " . implode(" ", $validation_errors);
                $_SESSION['message_type'] = "error";
                $item = array_merge($item, $item_data); // Mantiene dati originali e sovrascrive con POST
                require_once __DIR__ . '/../views/repair_service_items/form.php';
                return;
            }
        }
        require_once __DIR__ . '/../views/repair_service_items/form.php';
    }

    /**
     * Gestisce l'eliminazione di un intervento di servizio.
     * Accessibile solo al Super Amministratore.
     * @param int $id L'ID dell'intervento da eliminare.
     */
    public function delete($id) {
        // PERMESSO: Solo Super Amministratore può accedere a questa pagina.
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
            $_SESSION['message'] = "Accesso negato. Non hai i permessi per eliminare interventi di servizio.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=dashboard");
            exit();
        }

        if (!$id) {
            $_SESSION['message'] = "ID intervento non specificato.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=repair_services");
            exit();
        }

        if ($this->repairServiceItemModel->delete($id)) {
            $_SESSION['message'] = "Intervento di servizio eliminato con successo!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Errore durante l'eliminazione dell'intervento di servizio. Potrebbe essere in uso da una riparazione.";
            $_SESSION['message_type'] = "error";
        }
        ob_end_clean(); // Pulisci il buffer prima del reindirizzamento
        header("Location: index.php?page=repair_services");
        exit();
    }
}
