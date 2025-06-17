<?php
// app/controllers/ProductsController.php

require_once __DIR__ . '/../models/Product.php';

class ProductsController { // NOME DELLA CLASSE CORRETTO (PLURALE)
    private $productModel;

    public function __construct(Product $productModel) { // Questo costruttore si aspetta UN SOLO argomento
        $this->productModel = $productModel;
    }

    /**
     * Mostra l'elenco di tutti i prodotti.
     * Accessibile solo al Super Amministratore.
     */
    public function index() {
        // PERMESSO: Solo Super Amministratore può accedere a questa pagina.
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
            $_SESSION['message'] = "Accesso negato. Non hai i permessi per gestire il catalogo prodotti.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=dashboard");
            exit();
        }

        $search_query = $_GET['q'] ?? '';
        // Passa false per non filtrare solo per attivi nella vista di gestione
        $products = $this->productModel->readAll(false, $search_query); 

        require_once __DIR__ . '/../views/products/list.php';
    }

    /**
     * Gestisce l'aggiunta di un nuovo prodotto.
     * Accessibile solo al Super Amministratore.
     */
    public function add() {
        // PERMESSO: Solo Super Amministratore può accedere a questa pagina.
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
            $_SESSION['message'] = "Accesso negato. Non hai i permessi per aggiungere prodotti.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=dashboard");
            exit();
        }

        $product = []; // Variabile per pre-popolare il form (vuoto per l'aggiunta)
        $form_title = 'Aggiungi Nuovo Prodotto';
        $submit_button_text = 'Crea Prodotto';
        $action_url = 'index.php?page=products_catalog&action=add';
        $cancel_url = 'index.php?page=products_catalog';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $product_data = [
                'product_code' => $_POST['product_code'] ?? null,
                'product_name' => $_POST['product_name'] ?? null,
                'description' => $_POST['description'] ?? null,
                'default_price_net' => $_POST['default_price_net'] ?? null,
                'default_price_gross' => $_POST['default_price_gross'] ?? null,
                'amperes' => $_POST['amperes'] ?? null,
                'volts' => $_POST['volts'] ?? null,
                'other_specs' => $_POST['other_specs'] ?? null,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            $validation_errors = $this->productModel->validate($product_data);

            if (empty($validation_errors)) {
                foreach ($product_data as $key => $value) {
                    if (property_exists($this->productModel, $key)) {
                        $this->productModel->$key = $value;
                    }
                }

                $result = $this->productModel->create();

                if ($result['success']) {
                    $_SESSION['message'] = "Prodotto aggiunto con successo!";
                    $_SESSION['message_type'] = "success";
                    header("Location: index.php?page=products_catalog");
                    exit();
                } else {
                    $_SESSION['message'] = "Errore durante l'aggiunta del prodotto: " . ($result['error'] ?? 'Errore sconosciuto.');
                    $_SESSION['message_type'] = "error";
                    $product = $product_data; // Pre-popola il form con i dati inviati
                    require_once __DIR__ . '/../views/products/add_edit.php';
                    return;
                }
            } else {
                $_SESSION['message'] = "Errore di validazione: " . implode(" ", $validation_errors);
                $_SESSION['message_type'] = "error";
                $product = $product_data; // Pre-popola il form con i dati inviati<br />
				 $product_data = $product;
                require_once __DIR__ . '/../views/products/add_edit.php';
                return;
            }
        }
//        require_once __DIR__ . '/../views/products/add_edit.php';<br /><br />
		 $product_data = $product;
		require_once __DIR__ . '/../views/products/add_edit.php';
    }

    /**
     * Gestisce la modifica di un prodotto esistente.
     * Accessibile solo al Super Amministratore.
     * @param int $id L'ID del prodotto da modificare.
     */
    public function edit($id) {
        // PERMESSO: Solo Super Amministratore può accedere a questa pagina.
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
            $_SESSION['message'] = "Accesso negato. Non hai i permessi per modificare prodotti.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=dashboard");
            exit();
        }

        if (!$id) {
            $_SESSION['message'] = "ID prodotto non specificato.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=products_catalog");
            exit();
        }

        $product = $this->productModel->readOne($id);
        if (!$product) {
            $_SESSION['message'] = "Prodotto non trovato.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=products_catalog");
            exit();
        }

        $form_title = 'Modifica Prodotto';
        $submit_button_text = 'Aggiorna Prodotto';
        $action_url = 'index.php?page=products_catalog&action=edit&id=' . htmlspecialchars($id);
        $cancel_url = 'index.php?page=products_catalog';


        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $product_data = [
                'id' => $id, // ID necessario per la validazione in modalità modifica
                'product_code' => $_POST['product_code'] ?? null,
                'product_name' => $_POST['product_name'] ?? null,
                'description' => $_POST['description'] ?? null,
                'default_price_net' => $_POST['default_price_net'] ?? null,
                'default_price_gross' => $_POST['default_price_gross'] ?? null,
                'amperes' => $_POST['amperes'] ?? null,
                'volts' => $_POST['volts'] ?? null,
                'other_specs' => $_POST['other_specs'] ?? null,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            $validation_errors = $this->productModel->validate($product_data, true); // Passa true per is_edit_mode

            if (empty($validation_errors)) {
                foreach ($product_data as $key => $value) {
                    if (property_exists($this->productModel, $key)) {
                        $this->productModel->$key = $value;
                    }
                }

                $result = $this->productModel->update();

                if ($result['success']) {
                    $_SESSION['message'] = "Prodotto aggiornato con successo!";
                    $_SESSION['message_type'] = "success";
                    header("Location: index.php?page=products_catalog");
                    exit();
                } else {
                    $_SESSION['message'] = "Errore durante l'aggiornamento del prodotto: " . ($result['error'] ?? 'Errore sconosciuto.');
                    $_SESSION['message_type'] = "error";
                    $product = array_merge($product, $product_data); // Mantiene dati originali e sovrascrive con POST
                    $product_data = $product;
					require_once __DIR__ . '/../views/products/add_edit.php';
                    return;
                }
            } else {
                $_SESSION['message'] = "Errore di validazione: " . implode(" ", $validation_errors);
                $_SESSION['message_type'] = "error";
                $product = array_merge($product, $product_data); // Mantiene dati originali e sovrascrive con POST
                $product_data = $product;
		require_once __DIR__ . '/../views/products/add_edit.php';
                return;
            }
        }
		var_dump($product); exit;<br />
		 $product_data = $product;
        require_once __DIR__ . '/../views/products/add_edit.php';
    }

    /**
     * Gestisce l'eliminazione di un prodotto.
     * Accessibile solo al Super Amministratore.
     * @param int $id L'ID del prodotto da eliminare.
     */
    public function delete($id) {
        // PERMESSO: Solo Super Amministratore può accedere a questa pagina.
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
            $_SESSION['message'] = "Accesso negato. Non hai i permessi per eliminare prodotti.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=dashboard");
            exit();
        }

        if (!$id) {
            $_SESSION['message'] = "ID prodotto non specificato.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=products_catalog");
            exit();
        }

        if ($this->productModel->delete($id)) {
            $_SESSION['message'] = "Prodotto eliminato con successo!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Errore durante l'eliminazione del prodotto. Potrebbe essere in uso in un ordine.";
            $_SESSION['message_type'] = "error";
        }
        ob_end_clean(); // Pulisci il buffer prima del reindirizzamento
        header("Location: index.php?page=products_catalog");
        exit();
    }
}
