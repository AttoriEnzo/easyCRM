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

    /**
     * Mostra l'elenco di tutti i contatti.
     * Permette anche la ricerca, esportazione e importazione.
     */
    public function index() {
        // Permesso: Tutti gli utenti loggati possono visualizzare i contatti.
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

    /**
     * Mostra il form per aggiungere un nuovo contatto o elabora la sottomissione del form.
     */
    public function add() {
        // Permesso: Tutti gli utenti loggati possono aggiungere contatti.
        if (!isset($_SESSION['role'])) {
            $_SESSION['message'] = "Devi effettuare il login per aggiungere contatti.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=login");
            exit();
        }

        $contact = []; // Array vuoto per pre-popolare il form (per un nuovo contatto)

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Recupera i dati dal form
            $contact_data = [
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'company' => $_POST['company'] ?? '',
                'last_contact_date' => $_POST['last_contact_date'] ?? null,
                'contact_medium' => $_POST['contact_medium'] ?? '',
                'order_executed' => isset($_POST['order_executed']) ? 1 : 0,
                // Campi aggiunti
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

            // Validazione dei dati
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
            
            // Validazione dei campi fiscali e indirizzo aziendale tramite il metodo del modello
            $errors = array_merge($errors, $this->contactModel->validateTaxVatFields($contact_data));

            if (empty($errors)) {
                // Assegna i dati alle proprietà del modello
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
                    // Ricarica il form con i dati inseriti e gli errori
                    $contact = $contact_data; // Pre-popola il form
                    require_once __DIR__ . '/../../views/contacts/add_edit.php';
                    return; // Importante per non caricare due volte la vista
                }
            } else {
                $_SESSION['message'] = "Errore di validazione: " . implode(" ", $errors);
                $_SESSION['message_type'] = "error";
                // Ricarica il form con i dati inseriti e gli errori
                $contact = $contact_data; // Pre-popola il form
                require_once __DIR__ . '/../views/contacts/add_edit.php';
                return;
            }
        }
        // Se la richiesta è GET, mostra il form vuoto
        require_once __DIR__ . '/../views/contacts/add_edit.php';
    }

    /**
     * Mostra il form per modificare un contatto esistente o elabora la sottomissione del form.
     * @param int $id L'ID del contatto da modificare.
     */
    public function edit($id) {
        // Permesso: Tutti gli utenti loggati possono modificare contatti.
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

        $contact = $this->contactModel->readOne($id); // Recupera il contatto dal database
        if (!$contact) {
            $_SESSION['message'] = "Contatto non trovato.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=contacts");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Recupera i dati dal form
            $contact_data = [
                'id' => $id, // Aggiungi l'ID per la validazione in modalità modifica (es. unicità email)
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'company' => $_POST['company'] ?? '',
                'last_contact_date' => $_POST['last_contact_date'] ?? null,
                'contact_medium' => $_POST['contact_medium'] ?? '',
                'order_executed' => isset($_POST['order_executed']) ? 1 : 0,
                // Campi aggiunti
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

            // Validazione dei dati
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

            // Validazione dei campi fiscali e indirizzo aziendale tramite il metodo del modello
            $errors = array_merge($errors, $this->contactModel->validateTaxVatFields($contact_data));

            if (empty($errors)) {
                // Assegna i dati alle proprietà del modello
                foreach ($contact_data as $key => $value) {
                    if (property_exists($this->contactModel, $key)) {
                        $this->contactModel->$key = $value;
                    }
                }
                $this->contactModel->id = $id; // Assicurati che l'ID sia impostato per l'aggiornamento

                $result = $this->contactModel->update();

                if ($result['success']) {
                    $_SESSION['message'] = "Contatto aggiornato con successo!";
                    $_SESSION['message_type'] = "success";
                    header("Location: index.php?page=contacts&action=view&id=" . htmlspecialchars($id));
                    exit();
                } else {
                    $_SESSION['message'] = "Errore durante l'aggiornamento del contatto: " . ($result['error'] ?? 'Errore sconosciuto.');
                    $_SESSION['message_type'] = "error";
                    // Ricarica il form con i dati inviati e gli errori
                    $contact = array_merge($contact, $contact_data); // Mantiene i dati originali e sovrascrive con i dati POST
                    require_once __DIR__ . '/../views/contacts/add_edit.php';
                    return;
                }
            } else {
                $_SESSION['message'] = "Errore di validazione: " . implode(" ", $errors);
                $_SESSION['message_type'] = "error";
                // Ricarica il form con i dati inviati e gli errori
                $contact = array_merge($contact, $contact_data); // Mantiene i dati originali e sovrascrive con i dati POST
                require_once __DIR__ . '/../views/contacts/add_edit.php';
                return;
            }
        }
        // Se la richiesta è GET, mostra il form pre-popolato con i dati del contatto
        require_once __DIR__ . '/../views/contacts/add_edit.php';
    }

    /**
     * Elimina un contatto specifico.
     * @param int $id L'ID del contatto da eliminare.
     */
    public function delete($id) {
        // Permesso: Tutti gli utenti loggati possono eliminare contatti.
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
        // Reindirizza alla lista contatti dopo l'operazione
        ob_end_clean(); // Pulisci il buffer prima del reindirizzamento
        header("Location: index.php?page=contacts");
        exit();
    }

    /**
     * Mostra i dettagli di un singolo contatto e le sue interazioni.
     * Gestisce anche l'aggiunta di nuove interazioni.
     * @param int $id L'ID del contatto da visualizzare.
     */
    public function view($id) {
        // Permesso: Tutti gli utenti loggati possono visualizzare i contatti.
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

        // Gestione dell'aggiunta di una nuova interazione
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_interaction'])) {
            $interaction_data = [
                'contact_id' => $id,
                'user_id' => $_SESSION['user_id'] ?? null, // Associa l'utente loggato all'interazione
                'interaction_date' => $_POST['interaction_date'] ?? date('Y-m-d'),
                'type' => $_POST['type'] ?? '',
                'notes' => $_POST['notes'] ?? ''
            ];

            // Validazione minima
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
                    // Reindirizza per evitare il reinvio del form
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

        // Recupera le interazioni per il contatto
        $interactions = $this->interactionModel->readByContactId(
            $id,
            $_SESSION['user_id'] ?? null,
            $_SESSION['role'] ?? null
        );

        require_once __DIR__ . '/../views/contacts/view.php';
    }

    /**
     * Elimina una specifica interazione dal database.
     * @param int $id L'ID dell'interazione da eliminare.
     * @param int $contact_id L'ID del contatto a cui l'interazione è associata.
     */
    public function deleteInteraction($id, $contact_id) {
        // Permesso: Solo Admin e Superadmin possono eliminare qualsiasi interazione.
        // Tecnici e Commerciali possono eliminare solo le proprie interazioni.
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
        ob_end_clean(); // Pulisci il buffer prima del reindirizzamento
        header("Location: index.php?page=contacts&action=view&id=" . htmlspecialchars($contact_id));
        exit();
    }

    /**
     * Mostra la pagina di esportazione contatti o esegue l'esportazione CSV.
     */
    public function export() {
        // Permesso: Tutti gli utenti loggati possono esportare contatti.
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

            $contacts_data = $this->contactModel->readAll(); // Recupera tutti i contatti

            if (empty($contacts_data)) {
                $_SESSION['message'] = "Nessun contatto da esportare.";
                $_SESSION['message_type'] = "info";
                require_once __DIR__ . '/../../views/contacts/export.php';
                return;
            }

            // Inizia la costruzione del CSV
            $output = fopen('php://temp', 'r+'); // Apri un file temporaneo in memoria

            // Scrivi l'intestazione del CSV usando i campi selezionati
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
                    $header_row[] = ucfirst(str_replace('_', ' ', $field)); // Fallback se il campo non è mappato
                }
            }
            fputcsv($output, $header_row);

            // Scrivi i dati
            foreach ($contacts_data as $contact) {
                $row = [];
                foreach ($selected_fields as $field) {
                    $value = $contact[$field] ?? ''; // Ottieni il valore, o stringa vuota se non esiste
                    if ($field === 'order_executed') { // Converti 0/1 in "No"/"Sì"
                        $value = ($value == 1) ? 'Sì' : 'No';
                    } elseif (strpos($field, '_date') !== false && !empty($value)) { // Formatta le date
                        $value = date('d/m/Y', strtotime($value));
                    }
                    $row[] = $value;
                }
                fputcsv($output, $row);
            }

            // Prepara l'header per il download
            $filename = 'contatti_export_' . date('Ymd_His') . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            rewind($output); // Riporta il puntatore all'inizio del file temporaneo
            echo stream_get_contents($output); // Scrivi il contenuto al browser
            fclose($output); // Chiudi il file temporaneo
            exit(); // Termina lo script per evitare output HTML aggiuntivo
        }
        // Se la richiesta è GET, mostra il form di selezione campi
        require_once __DIR__ . '/../../views/contacts/export.php';
    }

    /**
     * Mostra la pagina di importazione contatti o esegue l'importazione CSV.
     */
    public function import() {
        // Permesso: Solo Admin e Superadmin possono importare contatti.
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

            $header = fgetcsv($file_handle); // Leggi l'intestazione

            $imported_count = 0;
            $failed_count = 0;
            $skipped_count = 0; // Per contatti con campi obbligatori mancanti

            while (($row = fgetcsv($file_handle)) !== FALSE) {
                if (empty(array_filter($row))) { // Salta righe completamente vuote
                    continue;
                }

                $contact_data = array_combine($header, $row); // Associa i dati con le intestazioni

                // Mappa i nomi delle colonne CSV ai nomi delle proprietà del modello, se diversi
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
                // created_at non viene importato, viene generato dal DB

                // Formatta la data se presente e non nel formato YYYY-MM-DD
                if (!empty($mapped_data['last_contact_date'])) {
                    if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $mapped_data['last_contact_date'])) { // Se è DD/MM/YYYY
                        $date_parts = explode('/', $mapped_data['last_contact_date']);
                        $mapped_data['last_contact_date'] = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
                    }
                    // Altrimenti si assume che sia già YYYY-MM-DD o un formato accettabile da strtotime
                }


                // Validazione per i campi obbligatori
                if (empty($mapped_data['first_name']) || empty($mapped_data['last_name']) || empty($mapped_data['company'])) {
                    $failed_count++;
                    error_log("Importazione Contatti: Riga saltata per campi obbligatori mancanti: " . json_encode($mapped_data));
                    continue; // Salta questa riga e passa alla successiva
                }

                // Validazione aggiuntiva usando il modello (es. email valida, campi fiscali validi)
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

                // Assegna i dati al modello e tenta la creazione
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
        // Se la richiesta è GET, mostra il form di importazione
        require_once __DIR__ . '/../views/contacts/import.php';
    }

    /**
     * Mostra l'elenco globale di tutte le interazioni, non filtrate per contatto specifico.
     */
    public function globalIndex() {
        // Permesso: Tutti gli utenti loggati possono visualizzare le interazioni.
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