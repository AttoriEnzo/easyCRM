<?php
// app/controllers/ContactController.php

require_once __DIR__ . '/../models/Contact.php';
require_once __DIR__ . '/../models/Interaction.php';

class ContactController {
    private $contactModel;
    private $interactionModel;

    /**
     * Costruttore del ContactController.
     * Inizializza i modelli necessari per le operazioni sui contatti e le interazioni.
     * @param Contact $contactModel L'istanza del modello Contact.
     * @param Interaction $interactionModel L'istanza del modello Interaction.
     */
    public function __construct(Contact $contactModel, Interaction $interactionModel) {
        $this->contactModel = $contactModel;
        $this->interactionModel = $interactionModel;
    }

    public function index() {
        if (!isset($_SESSION['role'])) {
            $_SESSION['message'] = "Devi effettuare il login per accedere a questa sezione.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=login");
            exit();
        }

        $search_query = $_GET['q'] ?? '';
        $contacts = $this->contactModel->readAll($search_query);

        require_once __DIR__ . '/../views/contacts/list.php';
    }

    public function add() {
        if (!isset($_SESSION['role'])) {
            $_SESSION['message'] = "Devi effettuare il login per aggiungere contatti.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=login");
            exit();
        }

        $contact = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $contact_data = [
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'company' => $_POST['company'] ?? '',
                'last_contact_date' => $_POST['last_contact_date'] ?? null,
                'contact_medium' => $_POST['contact_medium'] ?? '',
                'order_executed' => isset($_POST['order_executed']) ? 1 : 0,
                'client_type' => $_POST['client_type'] ?? 'Privato',
                'tax_code' => $_POST['tax_code'] ?? '',
                'vat_number' => $_POST['vat_number'] ?? '',
                'sdi' => $_POST['sdi'] ?? '',
                'company_address' => $_POST['company_address'] ?? '',
                'company_city' => $_POST['company_city'] ?? '',
                'company_zip' => $_POST['company_zip'] ?? '',
                'company_province' => $_POST['company_province'] ?? '',
                'pec' => $_POST['pec'] ?? '',
                'mobile_phone' => $_POST['mobile_phone'] ?? ''
            ];

            $errors = [];
            if (empty($contact_data['first_name'])) {
                $errors[] = "Il nome è obbligatorio.";
            }
            if (empty($contact_data['last_name'])) {
                $errors[] = "Il cognome è obbligatorio.";
            }
            if (empty($contact_data['company'])) {
                $errors[] = "L'azienda è obbligatoria.";
            }
            if (!empty($contact_data['email']) && !filter_var($contact_data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "L'indirizzo email non è valido.";
            }
            $errors = array_merge($errors, $this->contactModel->validateTaxVatFields($contact_data));

            if (empty($errors)) {
                foreach ($contact_data as $key => $value) {
                    if (property_exists($this->contactModel, $key)) {
                        $this->contactModel->$key = $value;
                    }
                }

                $result = $this->contactModel->create();

                if ($result['success']) {
                    $_SESSION['message'] = "Contatto aggiunto con successo!";
                    $_SESSION['message_type'] = "success";
                    header("Location: index.php?page=contacts");
                    exit();
                } else {
                    $_SESSION['message'] = "Errore durante l'aggiunta del contatto: " . ($result['error'] ?? 'Errore sconosciuto.');
                    $_SESSION['message_type'] = "error";
                    $contact = $contact_data;
                    require_once __DIR__ . '/../../views/contacts/add_edit.php';
                    return;
                }
            } else {
                $_SESSION['message'] = "Errore di validazione: " . implode(" ", $errors);
                $_SESSION['message_type'] = "error";
                $contact = $contact_data;
                require_once __DIR__ . '/../views/contacts/add_edit.php';
                return;
            }
        }
        require_once __DIR__ . '/../views/contacts/add_edit.php';
    }

    public function edit($id) {
    if (!isset($_SESSION['role'])) {
        $_SESSION['message'] = "Devi effettuare il login per modificare i contatti.";
        $_SESSION['message_type'] = "error";
        header("Location: index.php?page=login");
        exit();
    }

    if (!$id) {
        $_SESSION['message'] = "ID contatto non specificato.";
        $_SESSION['message_type'] = "error";
        header("Location: index.php?page=contacts");
        exit();
    }

    $contact = $this->contactModel->readOne($id);

    // --- Carica le variabili necessarie per la view ---
    // NESSUNA require_once ClientType.php qui!
    require_once __DIR__ . '/../models/User.php';

    // Tipi di cliente statici
    $client_types = ['Privato', 'Azienda', 'PA'];

    // Usa la connessione dal model Contact tramite getter pubblico
    $conn = $this->contactModel->getConnection();
    $userModel = new User($conn);
    $users_for_assignment = $userModel->getAll();

    if (!$contact) {
        $_SESSION['message'] = "Contatto non trovato.";
        $_SESSION['message_type'] = "error";
        header("Location: index.php?page=contacts");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $contact_data = [
            'id' => $id,
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'company' => $_POST['company'] ?? '',
            'last_contact_date' => $_POST['last_contact_date'] ?? null,
            'contact_medium' => $_POST['contact_medium'] ?? '',
            'order_executed' => isset($_POST['order_executed']) ? 1 : 0,
            'client_type' => $_POST['client_type'] ?? 'Privato',
            'tax_code' => $_POST['tax_code'] ?? '',
            'vat_number' => $_POST['vat_number'] ?? '',
            'sdi' => $_POST['sdi'] ?? '',
            'company_address' => $_POST['company_address'] ?? '',
            'company_city' => $_POST['company_city'] ?? '',
            'company_zip' => $_POST['company_zip'] ?? '',
            'company_province' => $_POST['company_province'] ?? '',
            'pec' => $_POST['pec'] ?? '',
            'mobile_phone' => $_POST['mobile_phone'] ?? ''
        ];

        $errors = [];
        if (empty($contact_data['first_name'])) {
            $errors[] = "Il nome è obbligatorio.";
        }
        if (empty($contact_data['last_name'])) {
            $errors[] = "Il cognome è obbligatorio.";
        }
        if (empty($contact_data['company'])) {
            $errors[] = "L'azienda è obbligatoria.";
        }
        if (!empty($contact_data['email']) && !filter_var($contact_data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'indirizzo email non è valido.";
        }
        $errors = array_merge($errors, $this->contactModel->validateTaxVatFields($contact_data));

        if (empty($errors)) {
            foreach ($contact_data as $key => $value) {
                if (property_exists($this->contactModel, $key)) {
                    $this->contactModel->$key = $value;
                }
            }
            $this->contactModel->id = $id;

            $result = $this->contactModel->update();
            if ($result['success']) {
                $_SESSION['message'] = "Contatto aggiornato con successo!";
                $_SESSION['message_type'] = "success";
                header("Location: index.php?page=contacts&action=view&id=" . htmlspecialchars($id));
                exit();
            } else {
                $_SESSION['message'] = "Errore durante l'aggiornamento del contatto: " . ($result['error'] ?? 'Errore sconosciuto.');
                $_SESSION['message_type'] = "error";
                $contact = array_merge($contact, $contact_data);
                require_once __DIR__ . '/../views/contacts/add_edit.php';
                return;
            }
        } else {
            $_SESSION['message'] = "Errore di validazione: " . implode(" ", $errors);
            $_SESSION['message_type'] = "error";
            $contact = array_merge($contact, $contact_data);
            require_once __DIR__ . '/../views/contacts/add_edit.php';
            return;
        }
    }
    require_once __DIR__ . '/../views/contacts/add_edit.php';
}
    public function delete($id) {
        if (!isset($_SESSION['role'])) {
            $_SESSION['message'] = "Devi effettuare il login per eliminare i contatti.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=login");
            exit();
        }

        if (!$id) {
            $_SESSION['message'] = "ID contatto non specificato.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=contacts");
            exit();
        }

        if ($this->contactModel->delete($id)) {
            $_SESSION['message'] = "Contatto eliminato con successo!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Errore durante l'eliminazione del contatto. Assicurati che non ci siano riparazioni o ordini commerciali associati.";
            $_SESSION['message_type'] = "error";
        }
        ob_end_clean();
        header("Location: index.php?page=contacts");
        exit();
    }

    public function view($id) {
        if (!isset($_SESSION['role'])) {
            $_SESSION['message'] = "Devi effettuare il login per visualizzare i dettagli dei contatti.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=login");
            exit();
        }

        if (!$id) {
            $_SESSION['message'] = "ID contatto non specificato.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=contacts");
            exit();
        }

        $contact = $this->contactModel->readOne($id);
        if (!$contact) {
            $_SESSION['message'] = "Contatto non trovato.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=contacts");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_interaction'])) {
            $interaction_data = [
                'contact_id' => $id,
                'user_id' => $_SESSION['user_id'] ?? null,
                'interaction_date' => $_POST['interaction_date'] ?? date('Y-m-d'),
                'type' => $_POST['type'] ?? '',
                'notes' => $_POST['notes'] ?? ''
            ];

            $errors = [];
            if (empty($interaction_data['type'])) {
                $errors[] = "Il tipo di interazione è obbligatorio.";
            }
            if (empty($interaction_data['interaction_date'])) {
                $errors[] = "La data dell'interazione è obbligatoria.";
            }

            if (empty($errors)) {
                $this->interactionModel->contact_id = $interaction_data['contact_id'];
                $this->interactionModel->user_id = $interaction_data['user_id'];
                $this->interactionModel->interaction_date = $interaction_data['interaction_date'];
                $this->interactionModel->type = $interaction_data['type'];
                $this->interactionModel->notes = $interaction_data['notes'];

                if ($this->interactionModel->create()) {
                    $_SESSION['message'] = "Interazione aggiunta con successo!";
                    $_SESSION['message_type'] = "success";
                    header("Location: index.php?page=contacts&action=view&id=" . htmlspecialchars($id));
                    exit();
                } else {
                    $_SESSION['message'] = "Errore durante l'aggiunta dell'interazione.";
                    $_SESSION['message_type'] = "error";
                }
            } else {
                $_SESSION['message'] = "Errore di validazione interazione: " . implode(" ", $errors);
                $_SESSION['message_type'] = "error";
            }
        }

        $interactions = $this->interactionModel->readByContactId(
            $id,
            $_SESSION['user_id'] ?? null,
            $_SESSION['role'] ?? null
        );

        require_once __DIR__ . '/../views/contacts/view.php';
    }

    public function deleteInteraction($id, $contact_id) {
        if (!isset($_SESSION['role'])) {
            $_SESSION['message'] = "Devi effettuare il login per eliminare interazioni.";
            $_SESSION['message_type'] = "error";
            ob_end_clean();
            header("Location: index.php?page=login");
            exit();
        }

        if (!$id || !$contact_id) {
            $_SESSION['message'] = "ID interazione o contatto non specificato.";
            $_SESSION['message_type'] = "error";
            ob_end_clean();
            header("Location: index.php?page=contacts&action=view&id=" . htmlspecialchars($contact_id));
            exit();
        }
        
        if ($this->interactionModel->delete($id, $contact_id, $_SESSION['user_id'] ?? null, $_SESSION['role'] ?? null)) {
            $_SESSION['message'] = "Interazione eliminata con successo!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Errore durante l'eliminazione dell'interazione o permessi insufficienti.";
            $_SESSION['message_type'] = "error";
        }
        ob_end_clean();
        header("Location: index.php?page=contacts&action=view&id=" . htmlspecialchars($contact_id));
        exit();
    }

    public function export() {
        if (!isset($_SESSION['role'])) {
            $_SESSION['message'] = "Devi effettuare il login per esportare i contatti.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $selected_fields = $_POST['fields'] ?? [];

            if (empty($selected_fields)) {
                $_SESSION['message'] = "Seleziona almeno un campo per l'esportazione.";
                $_SESSION['message_type'] = "error";
                require_once __DIR__ . '/../../views/contacts/export.php';
                return;
            }

            $contacts_data = $this->contactModel->readAll();

            if (empty($contacts_data)) {
                $_SESSION['message'] = "Nessun contatto da esportare.";
                $_SESSION['message_type'] = "info";
                require_once __DIR__ . '/../../views/contacts/export.php';
                return;
            }

            $output = fopen('php://temp', 'r+');

            $header_row = [];
            $all_fields_map = [
                'id' => 'ID Contatto',
                'first_name' => 'Nome',
                'last_name' => 'Cognome',
                'email' => 'Email',
                'phone' => 'Telefono Fisso',
                'mobile_phone' => 'Telefono Cellulare',
                'company' => 'Azienda',
                'client_type' => 'Tipo Cliente',
                'tax_code' => 'Codice Fiscale',
                'vat_number' => 'Partita IVA',
                'sdi' => 'Codice SDI',
                'company_address' => 'Indirizzo Azienda',
                'company_city' => 'Città Azienda',
                'company_zip' => 'CAP Azienda',
                'company_province' => 'Provincia Azienda',
                'pec' => 'PEC',
                'last_contact_date' => 'Data Ultimo Contatto',
                'contact_medium' => 'Mezzo Contatto',
                'order_executed' => 'Ordine Eseguito',
                'created_at' => 'Data Creazione'
            ];

            foreach ($selected_fields as $field) {
                if (isset($all_fields_map[$field])) {
                    $header_row[] = $all_fields_map[$field];
                } else {
                    $header_row[] = ucfirst(str_replace('_', ' ', $field));
                }
            }
            fputcsv($output, $header_row);

            foreach ($contacts_data as $contact) {
                $row = [];
                foreach ($selected_fields as $field) {
                    $value = $contact[$field] ?? '';
                    if ($field === 'order_executed') {
                        $value = ($value == 1) ? 'Sì' : 'No';
                    } elseif (strpos($field, '_date') !== false && !empty($value)) {
                        $value = date('d/m/Y', strtotime($value));
                    }
                    $row[] = $value;
                }
                fputcsv($output, $row);
            }

            $filename = 'contatti_export_' . date('Ymd_His') . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            rewind($output);
            echo stream_get_contents($output);
            fclose($output);
            exit();
        }
        require_once __DIR__ . '/../../views/contacts/export.php';
    }

    public function import() {
        if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
            $_SESSION['message'] = "Accesso negato. Non hai i permessi per importare contatti.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=dashboard");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['message'] = "Errore nel caricamento del file CSV.";
                $_SESSION['message_type'] = "error";
                require_once __DIR__ . '/../views/contacts/import.php';
                return;
            }

            $csv_file = $_FILES['csv_file']['tmp_name'];
            $file_handle = fopen($csv_file, 'r');

            if ($file_handle === FALSE) {
                $_SESSION['message'] = "Impossibile aprire il file CSV.";
                $_SESSION['message_type'] = "error";
                require_once __DIR__ . '/../views/contacts/import.php';
                return;
            }

            $header = fgetcsv($file_handle);

            $imported_count = 0;
            $failed_count = 0;
            $skipped_count = 0;

            while (($row = fgetcsv($file_handle)) !== FALSE) {
                if (empty(array_filter($row))) {
                    continue;
                }

                $contact_data = array_combine($header, $row);

                $mapped_data = [];
                $mapped_data['first_name'] = $contact_data['Nome'] ?? $contact_data['first_name'] ?? '';
                $mapped_data['last_name'] = $contact_data['Cognome'] ?? $contact_data['last_name'] ?? '';
                $mapped_data['email'] = $contact_data['Email'] ?? $contact_data['email'] ?? '';
                $mapped_data['phone'] = $contact_data['Telefono Fisso'] ?? $contact_data['phone'] ?? '';
                $mapped_data['mobile_phone'] = $contact_data['Telefono Cellulare'] ?? $contact_data['mobile_phone'] ?? '';
                $mapped_data['company'] = $contact_data['Azienda'] ?? $contact_data['company'] ?? '';
                $mapped_data['client_type'] = $contact_data['Tipo Cliente'] ?? $contact_data['client_type'] ?? 'Privato';
                $mapped_data['tax_code'] = $contact_data['Codice Fiscale'] ?? $contact_data['tax_code'] ?? '';
                $mapped_data['vat_number'] = $contact_data['Partita IVA'] ?? $contact_data['vat_number'] ?? '';
                $mapped_data['sdi'] = $contact_data['Codice SDI'] ?? $contact_data['sdi'] ?? '';
                $mapped_data['company_address'] = $contact_data['Indirizzo Azienda'] ?? $contact_data['company_address'] ?? '';
                $mapped_data['company_city'] = $contact_data['Città Azienda'] ?? $contact_data['company_city'] ?? '';
                $mapped_data['company_zip'] = $contact_data['CAP Azienda'] ?? $contact_data['company_zip'] ?? '';
                $mapped_data['company_province'] = $contact_data['Provincia Azienda'] ?? $contact_data['company_province'] ?? '';
                $mapped_data['pec'] = $contact_data['PEC'] ?? $contact_data['pec'] ?? '';
                $mapped_data['last_contact_date'] = $contact_data['Data Ultimo Contatto'] ?? $contact_data['last_contact_date'] ?? null;
                $mapped_data['contact_medium'] = $contact_data['Mezzo Contatto'] ?? $contact_data['contact_medium'] ?? '';
                $mapped_data['order_executed'] = ($contact_data['Ordine Eseguito'] ?? $contact_data['order_executed'] ?? 'No') === 'Sì' ? 1 : 0;

                if (!empty($mapped_data['last_contact_date'])) {
                    if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $mapped_data['last_contact_date'])) {
                        $date_parts = explode('/', $mapped_data['last_contact_date']);
                        $mapped_data['last_contact_date'] = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
                    }
                }

                if (empty($mapped_data['first_name']) || empty($mapped_data['last_name']) || empty($mapped_data['company'])) {
                    $failed_count++;
                    error_log("Importazione Contatti: Riga saltata per campi obbligatori mancanti: " . json_encode($mapped_data));
                    continue;
                }

                $validation_errors = [];
                if (!empty($mapped_data['email']) && !filter_var($mapped_data['email'], FILTER_VALIDATE_EMAIL)) {
                    $validation_errors[] = "Email non valida.";
                }
                $validation_errors = array_merge($validation_errors, $this->contactModel->validateTaxVatFields($mapped_data));

                if (!empty($validation_errors)) {
                    $failed_count++;
                    error_log("Importazione Contatti: Riga saltata per errori di validazione (" . implode(", ", $validation_errors) . "): " . json_encode($mapped_data));
                    continue;
                }

                foreach ($mapped_data as $key => $value) {
                    if (property_exists($this->contactModel, $key)) {
                        $this->contactModel->$key = $value;
                    }
                }

                $result = $this->contactModel->create();
                if ($result['success']) {
                    $imported_count++;
                } else {
                    $failed_count++;
                    error_log("Importazione Contatti: Errore DB per riga: " . ($result['error'] ?? 'Errore sconosciuto') . " - Dati: " . json_encode($mapped_data));
                }
            }

            fclose($file_handle);

            $_SESSION['message'] = "Importazione completata: {$imported_count} contatti importati, {$failed_count} falliti/saltati.";
            $_SESSION['message_type'] = ($failed_count > 0) ? "warning" : "success";
            header("Location: index.php?page=contacts");
            exit();

        }
        require_once __DIR__ . '/../views/contacts/import.php';
    }

    public function globalIndex() {
        if (!isset($_SESSION['role'])) {
            $_SESSION['message'] = "Devi effettuare il login per visualizzare le interazioni.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=login");
            exit();
        }

        $search_query = $_GET['q'] ?? '';
        $interactions = $this->interactionModel->readAllGlobal(
            $_SESSION['user_id'] ?? null,
            $_SESSION['role'] ?? null,
            $search_query
        );

        require_once __DIR__ . '/../views/interactions/list.php';
    }
}
?>
