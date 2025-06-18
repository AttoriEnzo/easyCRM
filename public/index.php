<?php
// public/index.php - Punto di ingresso principale dell'applicazione

// --- Avvia il buffering dell'output per prevenire errori "headers already sent" ---
ob_start();

// --- Intestazioni per il controllo della cache (molto importanti per le pagine dinamiche) ---
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Una data nel passato

session_start(); // Inizia la sessione per gestire i messaggi flash e lo stato di login

//DEBUG !!!!!!<br />
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include la configurazione del database. Questo file si occupa anche di creare/aggiornare le tabelle.
require_once __DIR__ . '/../app/config/database.php';

$conn = getDbConnection();
// Include i modelli
require_once __DIR__ . '/../app/models/Contact.php';
require_once __DIR__ . '/../app/models/Interaction.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Repair.php';
require_once __DIR__ . '/../app/models/RepairServiceItem.php';
require_once __DIR__ . '/../app/models/RepairRepairItem.php';
require_once __DIR__ . '/../app/models/Product.php';
require_once __DIR__ . '/../app/models/CommercialOrder.php';
require_once __DIR__ . '/../app/models/CommercialOrderItem.php';
require_once __DIR__ . '/../app/models/CompanySetting.php';

// Include i controller (ATTENZIONE AI NOMI DEI FILE E CLASSI)
require_once __DIR__ . '/../app/controllers/ContactController.php';
require_once __DIR__ . '/../app/controllers/DashboardController.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/users/UserController.php';
require_once __DIR__ . '/../app/controllers/repairs/RepairController.php';
require_once __DIR__ . '/../app/controllers/RepairServiceItemController.php';
require_once __DIR__ . '/../app/controllers/ProductsController.php'; // Usa ProductsController.php
require_once __DIR__ . '/../app/controllers/CommercialOrderController.php';
require_once __DIR__ . '/../app/controllers/CompanySettingsController.php'; // Usa CompanySettingsController.php


// Funzione per visualizzare i messaggi flash
function display_flash_message() {
    if (isset($_SESSION['message'])) {
        $message_type = $_SESSION['message_type'] ?? 'info';
        echo "<div class='flash-message flash-" . htmlspecialchars($message_type) . "'>" . htmlspecialchars($_SESSION['message']) . "</div>";
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}


// Ottieni la pagina e l'azione dalla URL
$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;

// Istanzia tutti i modelli necessari qui
$userModel = new User($conn);
$contactModel = new Contact();
$interactionModel = new Interaction();
$repairModel = new Repair();
$repairServiceItemModel = new RepairServiceItem();
$repairRepairItemModel = new RepairRepairItem();
$productModel = new Product();
$commercialOrderModel = new CommercialOrder();
$commercialOrderItemModel = new CommercialOrderItem();
$companySettingModel = new CompanySetting();


// Istanzia i controller, passando le dipendenze
$authController = new AuthController($userModel);
$userController = new UserController($userModel);
$dashboardController = new DashboardController($contactModel, $interactionModel, $repairModel); 
$contactController = new ContactController($contactModel, $interactionModel);
$repairController = new RepairController($repairModel, $contactModel, $interactionModel, $repairServiceItemModel, $repairRepairItemModel);
$repairServiceItemController = new RepairServiceItemController($repairServiceItemModel);
$productController = new ProductsController($productModel); // Istanzia ProductsController (UN SOLO argomento)
$commercialOrderController = new CommercialOrderController($commercialOrderModel, $commercialOrderItemModel, $contactModel, $productModel, $companySettingModel, $interactionModel);
$companySettingController = new CompanySettingsController($companySettingModel);


// --- Logica di Autenticazione ---
// Pagine accessibili senza login (anche se la pagina di login avrà un HTML completo ora)
$public_pages = ['login', 'logout', 'print_commercial_doc', 'print_technical_doc'];

// Se l'utente non è loggato E la pagina richiesta non è pubblica, reindirizza al login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    if (!in_array($page, $public_pages)) {
        $_SESSION['message'] = "Devi effettuare il login per accedere a questa sezione.";
        $_SESSION['message_type'] = "error";
        // Pulisci il buffer di output prima di reindirizzare
        ob_end_clean();
        header("Location: index.php?page=login");
        exit(); // Termina l'esecuzione dopo il reindirizzamento
    }
}

// *** IMPORTANTE: Gestione dell'inclusione di header/footer basata sulla pagina ***
// Le pagine di login e i documenti stampabili sono autonomi
if (!in_array($page, ['login', 'print_commercial_doc', 'print_technical_doc'])) {
    // Includi l'header della pagina (contiene inizio HTML, HEAD, BODY per pagine normali)
    require_once __DIR__ . '/../app/views/partials/header.php';
    // Visualizza i messaggi flash dopo l'header e prima del contenuto della pagina
    display_flash_message();
}


// Routing principale
switch ($page) {
    case 'login':
        $authController->login();
        break;
    case 'logout':
        ob_end_clean(); // Pulisci il buffer prima di reindirizzare (per il logout)
        $authController->logout();
        break;
    case 'dashboard':
        $dashboardController->index();
        break;
    case 'contacts':
        switch ($action) {
            case 'add':
                $contactController->add();
                break;
            case 'edit':
                $contactController->edit($id);
                break;
            case 'delete':
                ob_end_clean(); // Pulisci il buffer prima di reindirizzare
                $contactController->delete($id);
                break;
            case 'view':
                $contactController->view($id);
                break;
            case 'delete_interaction': // Azione per eliminare interazioni
                ob_end_clean(); // Pulisci il buffer prima di reindirizzare
                $contactController->deleteInteraction($id, $_GET['contact_id'] ?? null); // id = interaction_id
                break;
            case 'export': // Azione per mostrare il form di esportazione o generare il CSV
                $contactController->export(); // Questo metodo gestirà GET e POST
                break;
            case 'import': // Azione per mostrare il form di importazione o elaborare il CSV
                $contactController->import(); // Questo metodo gestirà GET e POST
                break;
            default: // Visualizza tutti i contatti
                $contactController->index();
                break;
        }
        break;
    case 'users': // Rotte per la gestione degli utenti
        switch ($action) {
            case 'add':
                $userController->add();
                break;
            case 'edit':
                $userController->edit($id);
                break;
            case 'delete':
                ob_end_clean(); // Pulisci il buffer prima di reindirizzare
                $userController->delete($id);
                break;
            default: // Visualizza tutti gli utenti
                $userController->index();
                break;
        }
        break;
    case 'my_profile': // Rotte per il profilo utente (es. cambio password)
        switch ($action) {
            case 'change_password':
                $authController->changePassword();
                break;
            default:
                $_SESSION['message'] = "Azione non valida per il profilo utente.";
                $_SESSION['message_type'] = "error";
                header("Location: index.php?page=dashboard");
                exit();
        }
        break;
    case 'repairs': // Rotte per la gestione delle riparazioni
        switch ($action) {
            case 'add':
                $repairController->add();
                break;
            case 'edit':
                $repairController->edit($id);
                break;
            case 'delete':
                ob_end_clean();
                $repairController->delete($id);
                break;
            case 'view':
                $repairController->view($id);
                break;
            case 'update_status': // Azione per aggiornare lo stato via AJAX
                $repairController->updateStatus();
                break;
            case 'select_items': // Azione per aprire la finestra di selezione interventi
                $repairController->selectItems();
                break;
            default: // Visualizza tutte le riparazioni
                $repairController->index();
                break;
        }
        break;
    case 'repair_services': // Rotte per la gestione del catalogo servizi riparazione
        switch ($action) {
            case 'add':
                $repairServiceItemController->add();
                break;
            case 'edit':
                $repairServiceItemController->edit($id);
                break;
            case 'delete':
                ob_end_clean();
                $repairServiceItemController->delete($id);
                break;
            default:
                $repairServiceItemController->index();
                break;
        }
        break;
    case 'products_catalog': // Rotte per il catalogo prodotti (commerciale)
        switch ($action) {
            case 'add':
                $productController->add();
                break;
            case 'edit':
                $productController->edit($id);
                break;
            case 'delete':
                ob_end_clean();
                $productController->delete($id);
                break;
            default:
                $productController->index();
                break;
        }
        break;
    case 'commercial_orders': // Rotte per gli ordini commerciali
        switch ($action) {
            case 'add':
                $commercialOrderController->add();
                break;
            case 'edit':
                $commercialOrderController->edit($id);
                break;
            case 'delete':
                ob_end_clean();
                $commercialOrderController->delete($id);
                break;
            case 'view':
                $commercialOrderController->view($id);
                break;
            case 'add_item': // Aggiunta voce a ordine
                $commercialOrderController->addItem($_GET['order_id'] ?? null);
                break;
            case 'edit_item': // Modifica voce ordine
                $commercialOrderController->editItem($id, $_GET['order_id'] ?? null);
                break;
            case 'delete_item': // Elimina voce ordine
                ob_end_clean();
                $commercialOrderController->deleteItem($id, $_GET['order_id'] ?? null);
                break;
            case 'print_commercial_doc': // Stampa conferma ordine
                $commercialOrderController->printCommercialDoc($id);
                break;
            case 'print_technical_doc': // Stampa documento tecnico
                $commercialOrderController->printTechnicalDoc($id);
                break;
            default:
                $commercialOrderController->index();
                break;
        }
        break;
    case 'company_settings': // Rotte per le impostazioni aziendali
        $companySettingController->index(); // Questo metodo gestirà sia GET che POST
        break;
    case 'interactions': // Rotta per la lista globale delle interazioni
        $contactController->globalIndex();
        break;
    default:
        // Qui si potrebbe includere una vista di errore 404
        echo "<div class='flash-error'>Pagina non trovata.</div>";
        break;
}

// *** IMPORTANTE: Gestione dell'inclusione del footer basata sulla pagina ***
if (!in_array($page, ['login', 'print_commercial_doc', 'print_technical_doc'])) {
    // Includi il footer della pagina (contiene chiusura BODY e HTML per pagine normali)
    require_once __DIR__ . '/../app/views/partials/footer.php';
}

// Chiudi le connessioni ai database di TUTTI i modelli.
// Nota: La connessione $conn_init in database.php viene chiusa subito dopo l'inizializzazione dello schema.
$contactModel->closeConnection();
$interactionModel->closeConnection();
$userModel->closeConnection();
$repairModel->closeConnection();
$repairServiceItemModel->closeConnection();
$repairRepairItemModel->closeConnection();
$productModel->closeConnection();
$commercialOrderModel->closeConnection();
$commercialOrderItemModel->closeConnection();
$companySettingModel->closeConnection();

ob_end_flush(); // Termina il buffering dell'output e invia tutto al browser
?>
