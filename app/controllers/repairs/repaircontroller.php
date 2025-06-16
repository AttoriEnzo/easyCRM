<?php
// app/controllers/repairs/RepairController.php

// Includi i modelli necessari
require_once __DIR__ . '/../../models/Repair.php';
require_once __DIR__ . '/../../models/Contact.php';
require_once __DIR__ . '/../../models/Interaction.php';
require_once __DIR__ . '/../../models/RepairServiceItem.php'; // NUOVO: Modello per gli interventi preimpostati
require_once __DIR__ . '/../../models/RepairRepairItem.php';   // NUOVO: Modello per i dettagli degli interventi su una riparazione

class RepairController {
    private $repairModel;
    private $contactModel;
    private $interactionModel;
    private $repairServiceItemModel; // NUOVO
    private $repairRepairItemModel;   // NUOVO

    /**
     * Costruttore del RepairController.
     * @param Repair $repairModel L'istanza del modello Repair.
     * @param Contact $contactModel L'istanza del modello Contact.
     * @param Interaction $interactionModel L'istanza del modello Interaction.
     * @param RepairServiceItem $repairServiceItemModel L'istanza del modello RepairServiceItem. // NUOVO
     * @param RepairRepairItem $repairRepairItemModel L'istanza del modello RepairRepairItem.   // NUOVO
     */
    public function __construct(
        Repair $repairModel, 
        Contact $contactModel, 
        Interaction $interactionModel,
        RepairServiceItem $repairServiceItemModel, // NUOVO
        RepairRepairItem $repairRepairItemModel    // NUOVO
    ) {
        $this->repairModel = $repairModel;
        $this->contactModel = $contactModel;
        $this->interactionModel = $interactionModel;
        $this->repairServiceItemModel = $repairServiceItemModel; // NUOVO
        $this->repairRepairItemModel = $repairRepairItemModel;   // NUOVO
    }

    /**
     * Mostra l'elenco di tutte le riparazioni.
     * Permette anche la ricerca.
     */
    public function index() {
        // PERMESSO: Superadmin, Admin, Tecnici e Commerciali possono visualizzare la lista riparazioni.
        if (!isset($_SESSION['role']) || 
            ($_SESSION['role'] !== 'admin' && 
             $_SESSION['role'] !== 'superadmin' && 
             $_SESSION['role'] !== 'tecnico' &&
             $_SESSION['role'] !== 'commerciale')) {
            $_SESSION['message'] = "Accesso negato. Non hai i permessi per visualizzare le riparazioni.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=dashboard");
            exit();
        }

        $search_query = $_GET['q'] ?? '';

        // Recupera le riparazioni, applicando il filtro per utente in base al ruolo
        $repairs = $this->repairModel->readAll(
            $_SESSION['user_id'] ?? null,
            $_SESSION['role'] ?? null,
            $search_query
        );

        // Includi la vista per l'elenco delle riparazioni
        require_once __DIR__ . '/../../views/repairs/list.php';
    }

    /**
     * Gestisce l'aggiunta di una nuova riparazione.
     * Mostra il form di aggiunta o elabora i dati inviati.
     */
    public function add() {
        // PERMESSO: Solo Superadmin, Admin e Tecnici possono aggiungere riparazioni.
        if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin' && $_SESSION['role'] !== 'tecnico')) {
            $_SESSION['message'] = "Accesso negato. Non hai i permessi per aggiungere riparazioni.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=dashboard");
            exit();
        }

        $repair = []; // Variabile per pre-popolare il form (vuoto per l'aggiunta)
        $form_title = 'Aggiungi Nuova Riparazione';
        $submit_button_text = 'Crea Riparazione';
        $action_url = 'index.php?page=repairs&action=add';
        
        // Recupera i contatti per il dropdown
        $contacts_for_dropdown = $this->contactModel->readAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $repair_data = [
                'contact_id' => $_POST['contact_id'] ?? null,
                'user_id' => $_SESSION['user_id'] ?? null,
                'device_type' => $_POST['device_type'] ?? null,
                'brand' => $_POST['brand'] ?? null,
                'model' => $_POST['model'] ?? null,
                'serial_number' => $_POST['serial_number'] ?? null,
                'problem_description' => $_POST['problem_description'] ?? null,
                'accessories' => $_POST['accessories'] ?? null,
                'reception_date' => $_POST['reception_date'] ?? null,
                'ddt_number' => $_POST['ddt_number'] ?? null,
                'ddt_date' => $_POST['ddt_date'] ?? null,
                'status' => $_POST['status'] ?? null,
                'technician_notes' => $_POST['technician_notes'] ?? null,
                'completion_date' => $_POST['completion_date'] ?? null,
                'shipping_date' => $_POST['shipping_date'] ?? null,
                'tracking_code' => $_POST['tracking_code'] ?? null,
                'estimated_cost' => $_POST['estimated_cost'] ?? null, // Il costo ora arriva dal campo nascosto
                'repair_items_json' => $_POST['repair_items_json'] ?? '[]' // Il JSON degli interventi
            ];

            // Validazione dei dati usando il metodo validate del modello
            $validation_errors = $this->repairModel->validate($repair_data);

            // Validazione aggiuntiva per gli interventi
            $repair_items = json_decode($repair_data['repair_items_json'], true);
            if (empty($repair_items)) {
                $validation_errors[] = "È obbligatorio specificare almeno un intervento di riparazione.";
            }


            if (empty($validation_errors)) {
                // Assegna i dati al modello di riparazione
                foreach ($repair_data as $key => $value) {
                    if (property_exists($this->repairModel, $key) && $key !== 'repair_items_json') { // Non assegnare il JSON direttamente
                        $this->repairModel->$key = $value;
                    }
                }
                // Il campo estimated_cost è già stato popolato dal JS e arriva via POST

                $result = $this->repairModel->create();

                if ($result['success']) {
                    $new_repair_id = $this->repairModel->getInsertId(); // Ottieni l'ID della riparazione appena creata

                    // Salva gli interventi associati alla riparazione
                    foreach ($repair_items as $item) {
                        $this->repairRepairItemModel->repair_id = $new_repair_id;
                        $this->repairRepairItemModel->service_item_id = $item['service_item_id'];
                        $this->repairRepairItemModel->custom_description = $item['custom_description'];
                        $this->repairRepairItemModel->unit_cost = $item['unit_cost'];
                        $this->repairRepairItemModel->quantity = $item['quantity'];
                        $this->repairRepairItemModel->item_total = $item['item_total'];
                        
                        if (!$this->repairRepairItemModel->create()) {
                            // Se fallisce il salvataggio di un intervento, logga l'errore
                            error_log("Errore nel salvataggio del repair_repair_item per riparazione ID: " . $new_repair_id . ", Item: " . json_encode($item));
                            // Potresti voler annullare l'intera operazione o mostrare un errore più grave
                        }
                    }

                    $_SESSION['message'] = "Riparazione aggiunta con successo!";
                    $_SESSION['message_type'] = "success";
                    header("Location: index.php?page=repairs");
                    exit();
                } else {
                    $_SESSION['message'] = "Errore durante l'aggiunta della riparazione: " . ($result['error'] ?? 'Errore sconosciuto.');
                    $_SESSION['message_type'] = "error";
                    $repair = $repair_data; // Pre-popola il form con i dati inviati
                    require_once __DIR__ . '/../../views/repairs/repair_form.php';
                    return;
                }
            } else {
                $_SESSION['message'] = "Errore di validazione: " . implode(" ", $validation_errors);
                $_SESSION['message_type'] = "error";
                $repair = $repair_data; // Pre-popola il form con i dati inviati
                require_once __DIR__ . '/../../views/repairs/repair_form.php';
                return;
            }
        }
        // Per le richieste GET, mostra il form di aggiunta (vuoto)
        require_once __DIR__ . '/../../views/repairs/repair_form.php';
    }

    /**
     * Gestisce la modifica di una riparazione esistente.
     * Mostra il form di modifica (pre-popolato) o elabora i dati aggiornati.
     * @param int $id L'ID della riparazione da modificare.
     */
    public function edit($id) {
        // PERMESSO: Solo Superadmin, Admin e Tecnici possono modificare riparazioni.
        if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin' && $_SESSION['role'] !== 'tecnico')) {
            $_SESSION['message'] = "Accesso negato. Non hai i permessi per modificare riparazioni.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=dashboard");
            exit();
        }

        if (!$id) {
            $_SESSION['message'] = "ID riparazione non specificato.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=repairs");
            exit();
        }

        // Recupera i dati della riparazione per pre-popolare il form
        $repair = $this->repairModel->readOne($id);
        if (!$repair) {
            $_SESSION['message'] = "Riparazione non trovata.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=repairs");
            exit();
        }
        
        // Recupera gli interventi di riparazione già associati a questa riparazione (se in modifica)
        $existing_repair_items = $this->repairRepairItemModel->readByRepairId($id);
        // Aggiungi gli interventi alla variabile $repair in formato JSON per la vista
        $repair['repair_items_json'] = json_encode($existing_repair_items);


        $form_title = 'Modifica Riparazione';
        $submit_button_text = 'Aggiorna Riparazione';
        $action_url = 'index.php?page=repairs&action=edit&id=' . htmlspecialchars($id);
        
        // Recupera i contatti per il dropdown (necessario anche in modifica)
        $contacts_for_dropdown = $this->contactModel->readAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $repair_data = [
                'id' => $id, // ID necessario per la validazione in modalità modifica (serial_number unique)
                'contact_id' => $_POST['contact_id'] ?? null,
                'user_id' => $_SESSION['user_id'] ?? null, // Mantiene l'ID dell'utente loggato come creatore/modificatore
                'device_type' => $_POST['device_type'] ?? null,
                'brand' => $_POST['brand'] ?? null,
                'model' => $_POST['model'] ?? null,
                'serial_number' => $_POST['serial_number'] ?? null,
                'problem_description' => $_POST['problem_description'] ?? null,
                'accessories' => $_POST['accessories'] ?? null,
                'reception_date' => $_POST['reception_date'] ?? null,
                'ddt_number' => $_POST['ddt_number'] ?? null,
                'ddt_date' => $_POST['ddt_date'] ?? null,
                'status' => $_POST['status'] ?? null,
                'technician_notes' => $_POST['technician_notes'] ?? null,
                'completion_date' => $_POST['completion_date'] ?? null,
                'shipping_date' => $_POST['shipping_date'] ?? null,
                'tracking_code' => $_POST['tracking_code'] ?? null,
                'estimated_cost' => $_POST['estimated_cost'] ?? null, // Il costo ora arriva dal campo nascosto
                'repair_items_json' => $_POST['repair_items_json'] ?? '[]' // Il JSON degli interventi
            ];

            // Validazione dei dati usando il metodo validate del modello
            $validation_errors = $this->repairModel->validate($repair_data, true);

            // Validazione aggiuntiva per gli interventi
            $repair_items = json_decode($repair_data['repair_items_json'], true);
            if (empty($repair_items)) {
                $validation_errors[] = "È obbligatorio specificare almeno un intervento di riparazione.";
            }

            if (empty($validation_errors)) {
                // Assegna i dati al modello di riparazione
                foreach ($repair_data as $key => $value) {
                    if (property_exists($this->repairModel, $key) && $key !== 'repair_items_json') {
                        $this->repairModel->$key = $value;
                    }
                }

                $result = $this->repairModel->update();

                if ($result['success']) {
                    // Cancella tutti gli interventi esistenti per questa riparazione e li reinserisci
                    // Questo approccio semplifica l'aggiornamento (delete all, insert all)
                    if (!$this->repairRepairItemModel->deleteByRepairId($id)) {
                        error_log("Errore durante l'eliminazione degli interventi esistenti per la riparazione ID: " . $id);
                        $_SESSION['message'] = "Errore durante l'aggiornamento degli interventi. Si prega di riprovare.";
                        $_SESSION['message_type'] = "error";
                        // Potresti voler ritornare e non reindirizzare per mostrare l'errore sulla pagina
                        require_once __DIR__ . '/../../views/repairs/repair_form.php';
                        return;
                    }

                    foreach ($repair_items as $item) {
                        $this->repairRepairItemModel->repair_id = $id; // L'ID della riparazione esistente
                        $this->repairRepairItemModel->service_item_id = $item['service_item_id'];
                        $this->repairRepairItemModel->custom_description = $item['custom_description'];
                        $this->repairRepairItemModel->unit_cost = $item['unit_cost'];
                        $this->repairRepairItemModel->quantity = $item['quantity'];
                        $this->repairRepairItemModel->item_total = $item['item_total'];
                        
                        if (!$this->repairRepairItemModel->create()) {
                            error_log("Errore nel salvataggio del repair_repair_item durante l'aggiornamento per riparazione ID: " . $id . ", Item: " . json_encode($item));
                            // Continua ma segnala l'errore
                        }
                    }

                    $_SESSION['message'] = "Riparazione aggiornata con successo!";
                    $_SESSION['message_type'] = "success";
                    header("Location: index.php?page=repairs"); // Potremmo reindirizzare alla view singola della riparazione
                    exit();
                } else {
                    $_SESSION['message'] = "Errore durante l'aggiornamento della riparazione: " . ($result['error'] ?? 'Errore sconosciuto.');
                    $_SESSION['message_type'] = "error";
                    $repair = array_merge($repair, $repair_data); // Mantiene dati originali e sovrascrive con POST
                    require_once __DIR__ . '/../../views/repairs/repair_form.php';
                    return;
                }
            } else {
                $_SESSION['message'] = "Errore di validazione: " . implode(" ", $validation_errors);
                $_SESSION['message_type'] = "error";
                $repair = array_merge($repair, $repair_data); // Mantiene dati originali e sovrascrive con POST
                require_once __DIR__ . '/../../views/repairs/repair_form.php';
                return;
            }
        }
        // Per le richieste GET, mostra il form di modifica con i dati della riparazione
        require_once __DIR__ . '/../../views/repairs/repair_form.php';
    }

    /**
     * Gestisce l'eliminazione di una riparazione.
     * @param int $id L'ID della riparazione da eliminare.
     */
    public function delete($id) {
        // PERMESSO: Solo Superadmin e Admin possono eliminare riparazioni.
        if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
            $_SESSION['message'] = "Accesso negato. Non hai i permessi per eliminare riparazioni.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=dashboard");
            exit();
        }

        if (!$id) {
            $_SESSION['message'] = "ID riparazione non specificato.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=repairs");
            exit();
        }

        // Prima di eliminare la riparazione, elimina tutti i repair_repair_items associati
        // (anche se la foreign key ON DELETE CASCADE dovrebbe gestirlo, è una buona pratica esplicitarlo)
        if (!$this->repairRepairItemModel->deleteByRepairId($id)) {
             error_log("Errore durante l'eliminazione dei repair_repair_items per la riparazione ID: " . $id);
             $_SESSION['message'] = "Errore durante l'eliminazione degli interventi della riparazione. Riprova.";
             $_SESSION['message_type'] = "error";
             ob_end_clean();
             header("Location: index.php?page=repairs");
             exit();
        }

        if ($this->repairModel->delete($id)) {
            $_SESSION['message'] = "Riparazione eliminata con successo!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Errore durante l'eliminazione della riparazione.";
            $_SESSION['message_type'] = "error";
        }
        ob_end_clean(); // Pulisci il buffer prima del reindirizzamento
        header("Location: index.php?page=repairs");
        exit();
    }
    
    /**
     * Mostra i dettagli di una singola riparazione.
     * @param int $id L'ID della riparazione da visualizzare.
     */
    public function view($id) {
        // PERMESSO: Superadmin, Admin, Tecnici e Commerciali possono vedere tutte le riparazioni.
        if (!isset($_SESSION['role']) || 
            ($_SESSION['role'] !== 'admin' && 
             $_SESSION['role'] !== 'superadmin' && 
             $_SESSION['role'] !== 'tecnico' && 
             $_SESSION['role'] !== 'commerciale')) { 
             $_SESSION['message'] = "Accesso negato. Non hai i permessi per visualizzare i dettagli delle riparazioni.";
             $_SESSION['message_type'] = "error";
             header("Location: index.php?page=dashboard");
             exit();
        }

        if (!$id) {
            $_SESSION['message'] = "ID riparazione non specificato.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=repairs");
            exit();
        }

        $repair = $this->repairModel->readOne($id);
        if (!$repair) {
            $_SESSION['message'] = "Riparazione non trovata.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=repairs");
            exit();
        }

        // Recupera gli interventi di riparazione associati per la visualizzazione dettagliata
        $repair_items_for_display = $this->repairRepairItemModel->readByRepairId($id);

        // Recupera le interazioni del contatto associato a questa riparazione
        $interactions = [];
        if (isset($repair['contact_id'])) {
            if ($_SESSION['role'] === 'commerciale') {
                // Per i commerciali, vogliamo tutte le interazioni del contatto, ignorando il filtro utente
                $interactions = $this->interactionModel->readAllByContactIdIgnoringUserFilter($repair['contact_id']);
            } else {
                // Per tutti gli altri ruoli (admin, superadmin, tecnico), si applica il filtro utente
                $interactions = $this->interactionModel->readByContactId(
                    $repair['contact_id'],
                    $_SESSION['user_id'] ?? null,
                    $_SESSION['role'] ?? null
                );
            }
        }

        // Passa $repair_items_for_display alla vista
        require_once __DIR__ . '/../../views/repairs/view.php';
    }

    /**
     * Gestisce l'aggiornamento dello stato di una riparazione (tramite AJAX).
     * Restituisce una risposta JSON.
     */
    public function updateStatus() {
        header('Content-Type: application/json'); // Indica che la risposta sarà JSON

        // PERMESSO: Solo Superadmin, Admin e Tecnici possono aggiornare lo stato.
        if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin' && $_SESSION['role'] !== 'tecnico')) {
            echo json_encode(['success' => false, 'message' => 'Accesso negato.']);
            exit();
        }

        // Assicurati che la richiesta sia POST e che i dati siano presenti
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $repair_id = $_POST['id'] ?? null;
            $new_status = $_POST['status'] ?? null;

            // Validazione di base dei dati ricevuti
            if (!$repair_id || !is_numeric($repair_id)) {
                echo json_encode(['success' => false, 'message' => 'ID riparazione non valido.']);
                exit();
            }
            if (empty($new_status)) {
                echo json_encode(['success' => false, 'message' => 'Stato non specificato.']);
                exit();
            }

            // Validazione per stati validi (replica la logica del modello per coerenza)
            $valid_statuses = ['In Attesa', 'In Lavorazione', 'Ricambi Ordinati', 'In Test', 'Completata', 'Annullata', 'Ritirata'];
            if (!in_array($new_status, $valid_statuses)) {
                echo json_encode(['success' => false, 'message' => 'Stato della riparazione non valido.']);
                exit();
            }

            // Recupera la riparazione esistente per aggiornarla
            $repair = $this->repairModel->readOne($repair_id);
            if (!$repair) {
                echo json_encode(['success' => false, 'message' => 'Riparazione non trovata.']);
                exit();
            }

            // Assegna il nuovo stato al modello e tenta l'aggiornamento
            $this->repairModel->id = $repair_id;
            // Popola gli altri campi con i valori esistenti per evitare che l'update li annulli
            $this->repairModel->contact_id = $repair['contact_id'];
            $this->repairModel->user_id = $repair['user_id'];
            $this->repairModel->device_type = $repair['device_type'];
            $this->repairModel->brand = $repair['brand'];
            $this->repairModel->model = $repair['model'];
            $this->repairModel->serial_number = $repair['serial_number'];
            $this->repairModel->problem_description = $repair['problem_description'];
            $this->repairModel->accessories = $repair['accessories'];
            $this->repairModel->reception_date = $repair['reception_date'];
            $this->repairModel->ddt_number = $repair['ddt_number'];
            $this->repairModel->ddt_date = $repair['ddt_date'];
            $this->repairModel->status = $new_status; // Questo è il campo che vogliamo aggiornare
            $this->repairModel->technician_notes = $repair['technician_notes'];
            $this->repairModel->estimated_cost = $repair['estimated_cost']; // Mantiene il costo stimato esistente
            $this->repairModel->completion_date = $repair['completion_date'];
            $this->repairModel->shipping_date = $repair['shipping_date'];
            $this->repairModel->tracking_code = $repair['tracking_code'];
            
            $result = $this->repairModel->update();

            if ($result['success']) {
                echo json_encode(['success' => true, 'message' => 'Stato aggiornato con successo!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Errore durante l\'aggiornamento dello stato: ' . ($result['error'] ?? 'Errore sconosciuto.')]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Metodo di richiesta non valido.']);
        }
        exit();
    }

    /**
     * Mostra la finestra per la selezione degli interventi di riparazione.
     * Questa funzione non è un'azione completa, ma una vista che viene aperta via JavaScript.
     */
    public function selectItems() {
        // PERMESSO: Accessibile solo agli utenti loggati che possono creare/modificare riparazioni
        if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin' && $_SESSION['role'] !== 'tecnico')) {
            // Se l'utente non è autorizzato, potremmo mostrare una pagina di errore o un messaggio semplice
            echo "Accesso negato. Non hai i permessi per selezionare gli interventi di riparazione.";
            exit();
        }

        // Recupera tutti gli interventi di servizio attivi dal catalogo
        $service_items = $this->repairServiceItemModel->readAll(true); // true = solo attivi

        // Recupera gli interventi iniziali già selezionati dalla query string (se presenti)
        $initial_items_json = $_GET['initial_items'] ?? '[]';

        // Carica la vista della finestra di selezione
        require_once __DIR__ . '/../../views/repairs/select_repair_items.php';
    }
}
