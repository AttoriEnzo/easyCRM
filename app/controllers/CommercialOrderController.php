<?php
// app/controllers/CommercialOrderController.php

require_once __DIR__ . '/../models/CommercialOrder.php';
require_once __DIR__ . '/../models/CommercialOrderItem.php';
require_once __DIR__ . '/../models/Contact.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/CompanySetting.php';
require_once __DIR__ . '/../models/Interaction.php';


class CommercialOrderController {
    private $commercialOrderModel;
    private $commercialOrderItemModel;
    private $contactModel;
    private $productModel;
    private $companySettingModel;
    private $interactionModel;

    public function __construct(
        CommercialOrder $commercialOrderModel,
        CommercialOrderItem $commercialOrderItemModel,
        Contact $contactModel,
        Product $productModel,
        CompanySetting $companySettingModel,
        Interaction $interactionModel
    ) {
        $this->commercialOrderModel = $commercialOrderModel;
        $this->commercialOrderItemModel = $commercialOrderItemModel;
        $this->contactModel = $contactModel;
        $this->productModel = $productModel;
        $this->companySettingModel = $companySettingModel;
        $this->interactionModel = $interactionModel;
    }

    /**
     * Mostra l'elenco di tutti gli ordini commerciali.
     */
    public function index() {
        // PERMESSO: Tutti gli utenti loggati possono visualizzare gli ordini, ma con filtri diversi.
        if (!isset($_SESSION['role'])) {
            $_SESSION['message'] = "Devi effettuare il login per accedere a questa sezione.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=login");
            exit();
        }

        $search_query = $_GET['q'] ?? '';

        // Passa l'ID e il ruolo dell'utente per filtrare gli ordini
        $orders = $this->commercialOrderModel->readAll(
            $_SESSION['user_id'] ?? null,
            $_SESSION['role'] ?? null,
            $search_query
        );

        // Includi la vista per l'elenco degli ordini
        require_once __DIR__ . '/../views/commercial_orders/list.php'; 
    }

    /**
     * Gestisce l'aggiunta di un nuovo ordine commerciale.
     */
    public function add() {
        // PERMESSO: Solo Commerciale e Superadmin possono aggiungere ordini.
        if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'commerciale' && $_SESSION['role'] !== 'superadmin')) {
            $_SESSION['message'] = "Accesso negato. Non hai i permessi per aggiungere ordini commerciali.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=dashboard");
            exit();
        }

        $order = []; // Variabile per pre-popolare il form (vuoto per l'aggiunta)
        $form_title = 'Crea Nuovo Ordine Commerciale';
        $submit_button_text = 'Salva Ordine';
        $action_url = 'index.php?page=commercial_orders&action=add';
        $cancel_url = 'index.php?page=commercial_orders';

        // Recupera i contatti per il dropdown di selezione cliente
        $contacts_for_dropdown = $this->contactModel->readAll();
        // Recupera i prodotti attivi per la selezione degli articoli nell'ordine
        $active_products = $this->productModel->readAll(true); // true per solo prodotti attivi

        // Imposta la data dell'ordine di default alla data odierna per il nuovo ordine
        if (empty($order['order_date'])) {
            $order['order_date'] = date('Y-m-d');
        }
        // Inizializza il JSON degli articoli
        $order['order_items_json'] = '[]';
        $order['total_amount'] = '0.00';


        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $order_data = [
                'contact_id' => $_POST['contact_id'] ?? null,
                'commercial_user_id' => $_SESSION['user_id'] ?? null, // Assegna l'utente loggato
                'order_date' => $_POST['order_date'] ?? null,
                'status' => $_POST['status'] ?? 'Ordine Inserito',
                'expected_shipping_date' => $_POST['expected_shipping_date'] ?? null,
                'shipping_address' => $_POST['shipping_address'] ?? null,
                'shipping_city' => $_POST['shipping_city'] ?? null,
                'shipping_zip' => $_POST['shipping_zip'] ?? null,
                'shipping_province' => $_POST['shipping_province'] ?? null,
                'carrier' => $_POST['carrier'] ?? null,
                'shipping_costs' => $_POST['shipping_costs'] ?? null,
                'notes_commercial' => $_POST['notes_commercial'] ?? null,
                'notes_technical' => $_POST['notes_technical'] ?? null,
                'total_amount' => $_POST['total_amount'] ?? '0.00', // Campo inviato dal JS
                'order_items_json' => $_POST['order_items_json'] ?? '[]' // JSON degli articoli dell'ordine
            ];

            $validation_errors = $this->commercialOrderModel->validate($order_data);

            // Validazione aggiuntiva per gli articoli dell'ordine
            $order_items = json_decode($order_data['order_items_json'], true);
            if (empty($order_items)) {
                $validation_errors[] = "È obbligatorio specificare almeno un articolo per l'ordine.";
            }

            if (empty($validation_errors)) {
                foreach ($order_data as $key => $value) {
                    if (property_exists($this->commercialOrderModel, $key) && $key !== 'order_items_json') {
                        $this->commercialOrderModel->$key = $value;
                    }
                }

                $result = $this->commercialOrderModel->create();

                if ($result['success']) {
                    $new_order_id = $this->commercialOrderModel->getInsertId(); // Ottieni l'ID dell'ordine appena creato

                    // Salva gli articoli associati all'ordine
                    foreach ($order_items as $item) {
                        $this->commercialOrderItemModel->order_id = $new_order_id;
                        $this->commercialOrderItemModel->product_id = $item['product_id'];
                        $this->commercialOrderItemModel->description = $item['description'];
                        $this->commercialOrderItemModel->ordered_quantity = $item['ordered_quantity'];
                        $this->commercialOrderItemModel->ordered_unit_price = $item['ordered_unit_price'];
                        $this->commercialOrderItemModel->ordered_item_total = $item['ordered_item_total'];
                        $this->commercialOrderItemModel->actual_shipped_quantity = $item['actual_shipped_quantity']; // Inizialmente uguale a ordered_quantity o 0
                        $this->commercialOrderItemModel->actual_shipped_serial_number = $item['actual_shipped_serial_number'];
                        $this->commercialOrderItemModel->notes_item = $item['notes_item'];

                        if (!$this->commercialOrderItemModel->create()) {
                            error_log("Errore nel salvataggio del commercial_order_item per ordine ID: " . $new_order_id . ", Item: " . json_encode($item));
                            // Potrebbe essere necessario un rollback o una notifica più robusta
                        }
                    }

                    $_SESSION['message'] = "Ordine aggiunto con successo!";
                    $_SESSION['message_type'] = "success";
                    header("Location: index.php?page=commercial_orders");
                    exit();
                } else {
                    $_SESSION['message'] = "Errore durante l'aggiunta dell'ordine: " . ($result['error'] ?? 'Errore sconosciuto.');
                    $_SESSION['message_type'] = "error";
                    $order = $order_data; // Pre-popola il form con i dati inviati
                    // Se gli order_items_json sono presenti, devono essere decodificati per la vista
                    $order['order_items_data'] = json_decode($order['order_items_json'], true);
					$order['order_items_data'] = $existing_order_items;
                    require_once __DIR__ . '/../views/commercial_orders/form.php'; // ✅ CORRETTO: Add - POST Error
                    return; 
                }
            } else {
                $_SESSION['message'] = "Errore di validazione: " . implode(" ", $validation_errors);
                $_SESSION['message_type'] = "error";
                $order = $order_data; // Pre-popola il form con i dati inviati
                $order['order_items_data'] = json_decode($order['order_items_json'], true);
				$order['order_items_data'] = $existing_order_items;
                require_once __DIR__ . '/../views/commercial_orders/form.php'; // ✅ CORRETTO: Add - POST Validation Error
                return;
            }
        }
        // Per le richieste GET, mostra il form di aggiunta (vuoto)<br />
		$order['order_items_data'] = $existing_order_items;
        require_once __DIR__ . '/../views/commercial_orders/form.php'; // ✅ CORRETTO: Add - GET
    }

    /**
     * Gestisce la modifica di un ordine commerciale esistente.
     */
    public function edit($id) {
        // PERMESSO: Commerciale e Superadmin possono modificare ordini.
        // I tecnici possono modificare solo alcune parti, gestito nella logica del form.
        if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'commerciale' && $_SESSION['role'] !== 'superadmin' && $_SESSION['role'] !== 'tecnico')) {
            $_SESSION['message'] = "Accesso negato. Non hai i permessi per modificare ordini commerciali.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=dashboard");
            exit();
        }

        if (!$id) {
            $_SESSION['message'] = "ID ordine non specificato.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=commercial_orders");
            exit();
        }

        $order = $this->commercialOrderModel->readOne($id);
        if (!$order) {
            $_SESSION['message'] = "Ordine non trovato.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=commercial_orders");
            exit();
        }
        
        // Recupera gli articoli dell'ordine associati a questo ordine
        $existing_order_items = $this->commercialOrderItemModel->readByOrderId($id);
        // Aggiungi gli articoli alla variabile $order in formato JSON per la vista
        $order['order_items_json'] = json_encode($existing_order_items);


        $form_title = 'Modifica Ordine Commerciale';
        $submit_button_text = 'Aggiorna Ordine';
        $action_url = 'index.php?page=commercial_orders&action=edit&id=' . htmlspecialchars($id);
        $cancel_url = 'index.php?page=commercial_orders';

        $contacts_for_dropdown = $this->contactModel->readAll();
        $active_products = $this->productModel->readAll(true);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $order_data = [
                'id' => $id,
                'contact_id' => $_POST['contact_id'] ?? null,
                'commercial_user_id' => $order['commercial_user_id'], // Mantiene l'utente commerciale originale
                'order_date' => $_POST['order_date'] ?? null,
                'status' => $_POST['status'] ?? 'Ordine Inserito',
                'expected_shipping_date' => $_POST['expected_shipping_date'] ?? null,
                'shipping_address' => $_POST['shipping_address'] ?? null,
                'shipping_city' => $_POST['shipping_city'] ?? null,
                'shipping_zip' => $_POST['shipping_zip'] ?? null,
                'shipping_province' => $_POST['shipping_province'] ?? null,
                'carrier' => $_POST['carrier'] ?? null,
                'shipping_costs' => $_POST['shipping_costs'] ?? null,
                'notes_commercial' => $_POST['notes_commercial'] ?? null,
                'notes_technical' => $_POST['notes_technical'] ?? null,
                'total_amount' => $_POST['total_amount'] ?? '0.00', // Campo inviato dal JS
                'order_items_json' => $_POST['order_items_json'] ?? '[]' // JSON degli articoli dell'ordine
            ];

            // Applica controlli di permesso specifici per la modifica da parte di un tecnico
            $current_user_role = $_SESSION['role'] ?? null;
            $current_user_id = $_SESSION['user_id'] ?? null;

            if ($current_user_role === 'tecnico') {
                // I tecnici non possono modificare contact_id, order_date, total_amount, o articoli non tecnici
                $order_data['contact_id'] = $order['contact_id']; // Forziamo il mantenimento
                $order_data['order_date'] = $order['order_date'];
                $order_data['total_amount'] = $order['total_amount']; // Il tecnico non cambia il totale
                // Le note commerciali non vengono cambiate dal tecnico
                $order_data['notes_commercial'] = $order['notes_commercial']; 
                // Il tecnico può modificare solo lo stato, le date di spedizione, il tracking, e le note tecniche, e le quantità/seriali negli item
            } elseif ($current_user_role === 'commerciale' && $order['commercial_user_id'] !== $current_user_id && $_SESSION['role'] !== 'superadmin') {
                $_SESSION['message'] = "Accesso negato. Non puoi modificare ordini di altri commerciali.";
                $_SESSION['message_type'] = "error";
                header("Location: index.php?page=commercial_orders");
                exit();
            }

            $validation_errors = $this->commercialOrderModel->validate($order_data, true); // true per is_edit_mode

            $order_items = json_decode($order_data['order_items_json'], true);
            if (empty($order_items)) {
                $validation_errors[] = "È obbligatorio specificare almeno un articolo per l'ordine.";
            }

            if (empty($validation_errors)) {
                foreach ($order_data as $key => $value) {
                    if (property_exists($this->commercialOrderModel, $key) && $key !== 'order_items_json') {
                        $this->commercialOrderModel->$key = $value;
                    }
                }

                $result = $this->commercialOrderModel->update();

                if ($result['success']) {
                    // Gestione degli order_items: più complessa in modifica
                    // Il tecnico può modificare solo `actual_shipped_quantity`, `actual_shipped_serial_number`, `notes_item`.
                    // Commerciale/Superadmin possono modificare tutto (tranne `actual_shipped_quantity` e `actual_shipped_serial_number` che sono tecnici)
                    
                    // Recupera gli item esistenti per confronto
                    $existing_items_map = [];
                    foreach ($existing_order_items as $item) {
                        $existing_items_map[$item['id']] = $item;
                    }

                    // Itera sugli item ricevuti dal form
                    foreach ($order_items as $item_from_form) {
                        if (isset($item_from_form['id']) && isset($existing_items_map[$item_from_form['id']])) {
                            // Questo è un item esistente, aggiornalo
                            $original_item = $existing_items_map[$item_from_form['id']];

                            // Popola il modello con i dati originali per i campi che il ruolo attuale non può modificare
                            $this->commercialOrderItemModel->id = $item_from_form['id'];
                            $this->commercialOrderItemModel->order_id = $id;
                            $this->commercialOrderItemModel->product_id = $original_item['product_id'];
                            $this->commercialOrderItemModel->description = $original_item['description'];
                            $this->commercialOrderItemModel->ordered_quantity = $original_item['ordered_quantity'];
                            $this->commercialOrderItemModel->ordered_unit_price = $original_item['ordered_unit_price'];
                            $this->commercialOrderItemModel->ordered_item_total = $original_item['ordered_item_total'];
                            
                            // Campi che i tecnici possono modificare
                            if ($current_user_role === 'tecnico' || $current_user_role === 'superadmin' || $current_user_role === 'admin') {
                                $this->commercialOrderItemModel->actual_shipped_quantity = $item_from_form['actual_shipped_quantity'];
                                $this->commercialOrderItemModel->actual_shipped_serial_number = $item_from_form['actual_shipped_serial_number'];
                                $this->commercialOrderItemModel->notes_item = $item_from_form['notes_item'];
                            } else {
                                // Mantiene i valori originali per altri ruoli se non possono modificare
                                $this->commercialOrderItemModel->actual_shipped_quantity = $original_item['actual_shipped_quantity'];
                                $this->commercialOrderItemModel->actual_shipped_serial_number = $original_item['actual_shipped_serial_number'];
                                $this->commercialOrderItemModel->notes_item = $original_item['notes_item'];
                            }

                            // Se è commerciale o superadmin, possono modificare anche i campi "commerciali" dell'item
                            if ($current_user_role === 'commerciale' || $current_user_role === 'superadmin') {
                                $this->commercialOrderItemModel->product_id = $item_from_form['product_id'];
                                $this->commercialOrderItemModel->description = $item_from_form['description'];
                                $this->commercialOrderItemModel->ordered_quantity = $item_from_form['ordered_quantity'];
                                $this->commercialOrderItemModel->ordered_unit_price = $item_from_form['ordered_unit_price'];
                                $this->commercialOrderItemModel->ordered_item_total = $item_from_form['ordered_item_total'];
                            }


                            if (!$this->commercialOrderItemModel->update()) {
                                error_log("Errore aggiornamento commercial_order_item per ordine ID: " . $id . ", Item: " . json_encode($item_from_form));
                            }
                            unset($existing_items_map[$item_from_form['id']]); // Rimuovi dall'elenco degli esistenti
                        } else {
                            // Questo è un nuovo item, crealo (solo se commerciale o superadmin)
                            if ($current_user_role === 'commerciale' || $current_user_role === 'superadmin') {
                                $this->commercialOrderItemModel->order_id = $id;
                                $this->commercialOrderItemModel->product_id = $item_from_form['product_id'];
                                $this->commercialOrderItemModel->description = $item_from_form['description'];
                                $this->commercialOrderItemModel->ordered_quantity = $item_from_form['ordered_quantity'];
                                $this->commercialOrderItemModel->ordered_unit_price = $item_from_form['ordered_unit_price'];
                                $this->commercialOrderItemModel->ordered_item_total = $item_from_form['ordered_item_total'];
                                $this->commercialOrderItemModel->actual_shipped_quantity = $item_from_form['ordered_quantity']; // Nuovo item: spedito = ordinato
                                $this->commercialOrderItemModel->actual_shipped_serial_number = $item_from_form['actual_shipped_serial_number'] ?? null;
                                $this->commercialOrderItemModel->notes_item = $item_from_form['notes_item'] ?? null;
                                if (!$this->commercialOrderItemModel->create()) {
                                    error_log("Errore creazione nuovo commercial_order_item durante update per ordine ID: " . $id . ", Item: " . json_encode($item_from_form));
                                }
                            }
                        }
                    }

                    // Elimina gli item che erano presenti ma non sono più nel form
                    foreach ($existing_items_map as $item_to_delete) {
                        // Solo commerciale o superadmin possono eliminare item completamente
                        if ($current_user_role === 'commerciale' || $current_user_role === 'superadmin') {
                            if (!$this->commercialOrderItemModel->delete($item_to_delete['id'])) {
                                error_log("Errore eliminazione commercial_order_item per ordine ID: " . $id . ", Item ID: " . $item_to_delete['id']);
                            }
                        }
                    }


                    $_SESSION['message'] = "Ordine aggiornato con successo!";
                    $_SESSION['message_type'] = "success";
                    header("Location: index.php?page=commercial_orders&action=view&id=" . htmlspecialchars($id));
                    exit();
                } else {
                    $_SESSION['message'] = "Errore durante l'aggiornamento dell'ordine: " . ($result['error'] ?? 'Errore sconosciuto.');
                    $_SESSION['message_type'] = "error";
                    $order = array_merge($order, $order_data); // Mantiene dati originali e sovrascrive con POST
                    $order['order_items_data'] = json_decode($order['order_items_json'], true);
					$order['order_items_data'] = $existing_order_items;
                    require_once __DIR__ . '/../views/commercial_orders/form.php'; // ✅ CORRETTO: Edit - POST Error
                    return;
                }
            } else {
                $_SESSION['message'] = "Errore di validazione: " . implode(" ", $validation_errors);
                $_SESSION['message_type'] = "error";
                $order = array_merge($order, $order_data); // Mantiene dati originali e sovrascrive con POST
                $order['order_items_data'] = json_decode($order['order_items_json'], true);
				$order['order_items_data'] = $existing_order_items;
		echo '<pre>'; print_r($existing_order_items); echo '</pre>';
                require_once __DIR__ . '/../views/commercial_orders/form.php'; // ✅ CORRETTO: Edit - POST Validation Error
                return;
            }
        }
        // Per le richieste GET, mostra il form di modifica con i dati dell'ordine<br />
		$order['order_items_data'] = $existing_order_items;
        require_once __DIR__ . '/../views/commercial_orders/form.php'; // ✅ CORRETTO: Edit - GET
    }

    /**
     * Gestisce l'eliminazione di un ordine.
     */
    public function delete($id) {
        // PERMESSO: Solo Superadmin e Admin possono eliminare ordini.
        if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
            $_SESSION['message'] = "Accesso negato. Non hai i permessi per eliminare ordini.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=dashboard");
            exit();
        }

        if (!$id) {
            $_SESSION['message'] = "ID ordine non specificato.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=commercial_orders");
            exit();
        }
        
        // La chiave esterna con ON DELETE CASCADE dovrebbe gestire l'eliminazione degli item.
        // Se la FK non è impostata, bisognerebbe eliminare manualmente gli item qui:
        // $this->commercialOrderItemModel->deleteByOrderId($id);

        if ($this->commercialOrderModel->delete($id)) {
            $_SESSION['message'] = "Ordine eliminato con successo!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Errore durante l'eliminazione dell'ordine.";
            $_SESSION['message_type'] = "error";
        }
        ob_end_clean();
        header("Location: index.php?page=commercial_orders");
        exit();
    }

    /**
     * Mostra i dettagli di un singolo ordine.
     */
    public function view($id) {
        // PERMESSO: Tutti gli utenti loggati possono visualizzare gli ordini, ma con filtri diversi.
        if (!isset($_SESSION['role'])) {
            $_SESSION['message'] = "Devi effettuare il login per accedere a questa sezione.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=login");
            exit();
        }

        if (!$id) {
            $_SESSION['message'] = "ID ordine non specificato.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=commercial_orders");
            exit();
        }

        $order = $this->commercialOrderModel->readOne($id);
        if (!$order) {
            $_SESSION['message'] = "Ordine non trovato.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=commercial_orders");
            exit();
        }

        // Controllo aggiuntivo per i Commerciali: possono vedere solo i propri ordini
        if ($_SESSION['role'] === 'commerciale' && $order['commercial_user_id'] !== $_SESSION['user_id']) {
            $_SESSION['message'] = "Accesso negato. Non hai i permessi per visualizzare questo ordine.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=commercial_orders");
            exit();
        }

        // Recupera gli articoli dell'ordine
        $order_items = $this->commercialOrderItemModel->readByOrderId($id);

        // Recupera le interazioni del contatto associato all'ordine
        $interactions = [];
        if (isset($order['contact_id'])) {
            // Per i commerciali e i superadmin/admin, mostra tutte le interazioni del contatto
            if ($_SESSION['role'] === 'commerciale' || $_SESSION['role'] === 'superadmin' || $_SESSION['role'] === 'admin') {
                $interactions = $this->interactionModel->readAllByContactIdIgnoringUserFilter($order['contact_id']);
            } else {
                // Per altri ruoli (es. tecnico), applica il filtro utente
                $interactions = $this->interactionModel->readByContactId(
                    $order['contact_id'],
                    $_SESSION['user_id'] ?? null,
                    $_SESSION['role'] ?? null
                );
            }
        }
        
        require_once __DIR__ . '/../views/commercial_orders/view.php';
    }

    /**
     * Gestisce l'aggiunta di un articolo a un ordine esistente.
     * Normalmente avviene tramite la form di modifica dell'ordine.
     * Questa funzione potrebbe essere usata per un'azione AJAX o un form specifico.
     * Per ora, la logica di aggiunta item è integrata nel metodo edit() dell'ordine principale.
     */
    public function addItem($order_id) {
        // Questa logica sarà gestita principalmente dal JS nel form di modifica dell'ordine
        // Non è un'azione indipendente per ora.
        $_SESSION['message'] = "Funzione non implementata come azione diretta.";
        $_SESSION['message_type'] = "info";
        header("Location: index.php?page=commercial_orders&action=edit&id=" . htmlspecialchars($order_id));
        exit();
    }

    /**
     * Gestisce la modifica di un articolo di un ordine esistente.
     * Anche questa logica sarà gestita principalmente dal JS nel form di modifica dell'ordine.
     */
    public function editItem($item_id, $order_id) {
        // Anche questa logica sarà gestita principalmente dal JS nel form di modifica dell'ordine
        // Non è un'azione indipendente per ora.
        $_SESSION['message'] = "Funzione non implementata come azione diretta.";
        $_SESSION['message_type'] = "info";
        header("Location: index.php?page=commercial_orders&action=edit&id=" . htmlspecialchars($order_id));
        exit();
    }

    /**
     * Gestisce l'eliminazione di un articolo da un ordine esistente.
     * Anche questa logica sarà gestita principalmente dal JS nel form di modifica dell'ordine.
     */
    public function deleteItem($item_id, $order_id) {
        // PERMESSO: Solo Commerciale e Superadmin possono eliminare articoli dall'ordine.
        if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'commerciale' && $_SESSION['role'] !== 'superadmin')) {
            $_SESSION['message'] = "Accesso negato. Non hai i permessi per eliminare articoli dell'ordine.";
            $_SESSION['message_type'] = "error";
            ob_end_clean();
            header("Location: index.php?page=commercial_orders&action=edit&id=" . htmlspecialchars($order_id));
            exit();
        }

        if (!$item_id || !$order_id) {
            $_SESSION['message'] = "ID articolo o ordine non specificato.";
            $_SESSION['message_type'] = "error";
            ob_end_clean();
            header("Location: index.php?page=commercial_orders&action=edit&id=" . htmlspecialchars($order_id));
            exit();
        }

        if ($this->commercialOrderItemModel->delete($item_id)) {
            // Aggiorna il total_amount dell'ordine dopo l'eliminazione
            $this->commercialOrderModel->recalculateTotalAmount($order_id);

            $_SESSION['message'] = "Articolo ordine eliminato con successo!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Errore durante l'eliminazione dell'articolo dell'ordine.";
            $_SESSION['message_type'] = "error";
        }
        ob_end_clean();
        header("Location: index.php?page=commercial_orders&action=edit&id=" . htmlspecialchars($order_id));
        exit();
    }

    /**
     * Genera e mostra il documento stampabile (es. Conferma d'Ordine).
     */
    public function printCommercialDoc($id) {
        // Permesso: Tutti gli utenti loggati possono stampare il documento.
        // Controlli più fini possono essere aggiunti se necessario.
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            $_SESSION['message'] = "Devi effettuare il login per accedere a questa sezione.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=login");
            exit();
        }

        if (!$id) {
            $_SESSION['message'] = "ID ordine non specificato per la stampa.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=commercial_orders");
            exit();
        }

        $order = $this->commercialOrderModel->readOne($id);
        if (!$order) {
            $_SESSION['message'] = "Ordine non trovato.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=commercial_orders");
            exit();
        }

        // Recupera gli articoli dell'ordine
        $order_items = $this->commercialOrderItemModel->readByOrderId($id);
        
        // Recupera le impostazioni aziendali per i dati nel documento
        $company_settings = $this->companySettingModel->getSettings();

        // Includi la vista del documento stampabile (questa vista sarà autonoma, senza header/footer del CRM)
        require_once __DIR__ . '/../views/commercial_orders/print_commercial_doc.php';
        exit(); // Termina l'esecuzione per assicurare che non venga renderizzato altro HTML
    }

    /**
     * Genera e mostra il documento tecnico stampabile (es. Documento di Lavoro per il Tecnico).
     */
    public function printTechnicalDoc($id) {
        // Permesso: Commerciale, Tecnico, Admin, Superadmin.
        if (!isset($_SESSION['role']) || 
            ($_SESSION['role'] !== 'commerciale' && 
             $_SESSION['role'] !== 'tecnico' &&
             $_SESSION['role'] !== 'admin' &&
             $_SESSION['role'] !== 'superadmin')) {
            $_SESSION['message'] = "Accesso negato. Non hai i permessi per stampare il documento tecnico.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=dashboard");
            exit();
        }

        if (!$id) {
            $_SESSION['message'] = "ID ordine non specificato per la stampa del documento tecnico.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=commercial_orders");
            exit();
        }

        $order = $this->commercialOrderModel->readOne($id);
        if (!$order) {
            $_SESSION['message'] = "Ordine non trovato.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=commercial_orders");
            exit();
        }

        // Recupera gli articoli dell'ordine
        $order_items = $this->commercialOrderItemModel->readByOrderId($id);
        
        // Recupera le impostazioni aziendali per i dati nel documento
        $company_settings = $this->companySettingModel->getSettings();

        // Includi la vista del documento stampabile (questa vista sarà autonoma, senza header/footer del CRM)
        require_once __DIR__ . '/../views/commercial_orders/print_technical_doc.php';
        exit(); // Termina l'esecuzione per assicurare che non venga renderizzato altro HTML
    }
}
