<?php
// app/controllers/AuthController.php

require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;

    /**
     * Costruttore del AuthController.
     * Inizializza il modello User per le operazioni di autenticazione.
     * @param User $userModel L'istanza del modello User.
     */
    public function __construct(User $userModel) {
        $this->userModel = $userModel;
    }

    /**
     * Mostra la pagina di login o tenta di autenticare l'utente.
     */
    public function login() {
        // Se la richiesta è POST, l'utente sta tentando il login
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            // Trova l'utente per username
            $user = $this->userModel->findByUsername($username);

            // Verifica se l'utente esiste e la password è corretta
            if ($user && $this->userModel->verifyPassword($password, $user['password_hash'])) {
                // Autenticazione riuscita: rigenera l'ID di sessione per sicurezza e freschezza
                session_regenerate_id(true);

                // Imposta le variabili di sessione
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role']; // Memorizza il ruolo dell'utente
                $_SESSION['logged_in'] = true;

                $_SESSION['message'] = "Login effettuato con successo!";
                $_SESSION['message_type'] = "success";

                // Forza il salvataggio della sessione prima del reindirizzamento
                session_write_close(); 

                // Reindirizza alla dashboard
                header("Location: index.php?page=dashboard");
                exit();
            } else {
                // Autenticazione fallita
                $_SESSION['message'] = "Username o password non validi.";
                $_SESSION['message_type'] = "error";
                // Reindirizza al form di login con il messaggio di errore
                header("Location: index.php?page=login");
                exit();
            }
        }
        // Per le richieste GET, mostra il form di login
        require_once __DIR__ . '/../views/login.php';
    }

    /**
     * Esegue il logout dell'utente.
     */
    public function logout() {
        // Distruggi tutte le variabili di sessione
        $_SESSION = array();

        // Invalida il cookie di sessione.
        // Questo distruggerà la sessione e non solo i dati di sessione!
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Distruggi la sessione
        session_destroy();

        $_SESSION['message'] = "Logout effettuato con successo.";
        $_SESSION['message_type'] = "info";

        // Reindirizza alla pagina di login
        header("Location: index.php?page=login");
        exit();
    }

    /**
     * Mostra il form per cambiare la password dell'utente loggato
     * o elabora la richiesta di modifica della password.
     */
    public function changePassword() {
        // Assicurati che l'utente sia loggato
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            $_SESSION['message'] = "Devi effettuare il login per cambiare la password.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=login");
            exit();
        }

        // Recupera l'ID dell'utente loggato
        $user_id = $_SESSION['user_id'];
        $user = $this->userModel->readOne($user_id); // Recupera i dati dell'utente per validazioni future (es. ruolo)

        if (!$user) {
            // Questo caso non dovrebbe accadere se l'ID sessione è valido, ma è una precauzione
            $_SESSION['message'] = "Utente non trovato.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=dashboard"); // Reindirizza alla dashboard
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_new_password = $_POST['confirm_new_password'] ?? '';

            $validation_errors = [];

            // Validazione della password attuale
            if (empty($current_password) || !$this->userModel->verifyPassword($current_password, $user['password_hash'])) {
                $validation_errors[] = "La password attuale non è corretta.";
            }

            // Validazione della nuova password
            if (empty($new_password)) {
                $validation_errors[] = "La nuova password non può essere vuota.";
            } elseif (strlen($new_password) < 6) { // Esempio: minimo 6 caratteri
                $validation_errors[] = "La nuova password deve contenere almeno 6 caratteri.";
            }
            
            // Validazione conferma nuova password
            if ($new_password !== $confirm_new_password) {
                $validation_errors[] = "La nuova password e la conferma non corrispondono.";
            }

            if (empty($validation_errors)) {
                // Hash della nuova password
                $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT); // Usa PASSWORD_DEFAULT per l'algoritmo corrente raccomandato

                // Aggiorna la password nel database
                $this->userModel->id = $user_id;
                $this->userModel->username = $user['username']; // Necessario per il metodo update
                $this->userModel->role = $user['role'];       // Necessario per il metodo update
                $this->userModel->password_hash = $hashed_new_password; // Imposta l'hash per l'aggiornamento

                if ($this->userModel->update()) {
                    $_SESSION['message'] = "Password aggiornata con successo!";
                    $_SESSION['message_type'] = "success";
                    // Non reindirizzare immediatamente, mostra il messaggio e il form vuoto
                } else {
                    $_SESSION['message'] = "Errore durante l'aggiornamento della password.";
                    $_SESSION['message_type'] = "error";
                }
            } else {
                $_SESSION['message'] = "Errore di validazione: " . implode(" ", $validation_errors);
                $_SESSION['message_type'] = "error";
            }
        }
        // Includi il form per la modifica della password
        require_once __DIR__ . '/../views/users/change_password.php';
    }
}
?>
