<?php
// app/controllers/users/UserController.php

require_once __DIR__ . '/../../models/User.php';

class UserController {
    private $userModel;

    /**
     * Costruttore del UserController.
     * Inizializza il modello User.
     * @param User $userModel L'istanza del modello User.
     */
    public function __construct(User $userModel) {
        $this->userModel = $userModel;
    }

    /**
     * Mostra l'elenco di tutti gli utenti.
     * Permette anche la ricerca.
     */
    public function index() {
        // Permesso: Solo Superadmin e Admin possono gestire gli utenti
        if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
            $_SESSION['message'] = "Accesso negato. Non hai i permessi per gestire gli utenti.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=dashboard");
            exit();
        }

        $search_query = $_GET['q'] ?? '';
        $users = $this->userModel->readAll($search_query);

        require_once __DIR__ . '/../../views/users/list.php';
    }

    /**
     * Gestisce l'aggiunta di un nuovo utente.
     * Mostra il form di aggiunta o elabora i dati inviati.
     */
    public function add() {
        // Permesso: Solo Superadmin e Admin possono aggiungere utenti
        if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
            $_SESSION['message'] = "Accesso negato. Non hai i permessi per aggiungere utenti.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=dashboard");
            exit();
        }

        $user = []; // Variabile per pre-popolare il form (vuoto per l'aggiunta)
        $form_title = 'Aggiungi Nuovo Utente';
        $submit_button_text = 'Crea Utente';
        $action_url = 'index.php?page=users&action=add';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'user';

            // Validazione di base
            $validation_errors = [];
            if (empty($username)) {
                $validation_errors[] = "Lo username non può essere vuoto.";
            }
            if (empty($password)) {
                $validation_errors[] = "La password non può essere vuota.";
            } elseif (strlen($password) < 6) {
                $validation_errors[] = "La password deve contenere almeno 6 caratteri.";
            }
            // Verifica unicità username
            if ($this->userModel->findByUsername($username)) {
                $validation_errors[] = "Lo username è già in uso.";
            }

            if (empty($validation_errors)) {
                $this->userModel->username = $username;
                $this->userModel->password_hash = password_hash($password, PASSWORD_DEFAULT);
                $this->userModel->role = $role;

                if ($this->userModel->create()) {
                    $_SESSION['message'] = "Utente aggiunto con successo!";
                    $_SESSION['message_type'] = "success";
                    header("Location: index.php?page=users");
                    exit();
                } else {
                    $_SESSION['message'] = "Errore durante l'aggiunta dell'utente.";
                    $_SESSION['message_type'] = "error";
                    $user = ['username' => $username, 'role' => $role]; // Pre-popola il form
                    require_once __DIR__ . '/../../views/users/user_form.php'; // Usa il nuovo nome
                    return;
                }
            } else {
                $_SESSION['message'] = "Errore di validazione: " . implode(" ", $validation_errors);
                $_SESSION['message_type'] = "error";
                $user = ['username' => $username, 'role' => $role]; // Pre-popola il form
                require_once __DIR__ . '/../../views/users/user_form.php'; // Usa il nuovo nome
                return;
            }
        }
        require_once __DIR__ . '/../../views/users/user_form.php'; // Usa il nuovo nome
    }

    /**
     * Gestisce la modifica di un utente esistente.
     * Mostra il form di modifica (pre-popolato) o elabora i dati aggiornati.
     * @param int $id L'ID dell'utente da modificare.
     */
    public function edit($id) {
        // Permesso: Solo Superadmin e Admin possono modificare utenti
        if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
            $_SESSION['message'] = "Accesso negato. Non hai i permessi per modificare utenti.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=dashboard");
            exit();
        }

        if (!$id) {
            $_SESSION['message'] = "ID utente non specificato.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=users");
            exit();
        }

        $user = $this->userModel->readOne($id);
        if (!$user) {
            $_SESSION['message'] = "Utente non trovato.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=users");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? ''; // Può essere vuota se non modificata
            $role = $_POST['role'] ?? 'user';

            // Validazione
            $validation_errors = [];
            if (empty($username)) {
                $validation_errors[] = "Lo username non può essere vuoto.";
            }
            // Verifica unicità username solo se è cambiato e non è l'utente corrente
            $existing_user = $this->userModel->findByUsername($username);
            if ($existing_user && $existing_user['id'] != $id) {
                $validation_errors[] = "Lo username è già in uso da un altro utente.";
            }

            if (!empty($password) && strlen($password) < 6) {
                $validation_errors[] = "La password deve contenere almeno 6 caratteri se modificata.";
            }

            if (empty($validation_errors)) {
                $this->userModel->id = $id;
                $this->userModel->username = $username;
                $this->userModel->role = $role;

                // Aggiorna la password solo se fornita
                if (!empty($password)) {
                    $this->userModel->password_hash = password_hash($password, PASSWORD_DEFAULT);
                } else {
                    // Mantiene la password esistente
                    $this->userModel->password_hash = null; // Il metodo update del modello User lo gestirà
                }

                if ($this->userModel->update()) {
                    // Se l'utente sta modificando il proprio ruolo, aggiorna la sessione
                    if ($_SESSION['user_id'] == $id) {
                        $_SESSION['username'] = $username;
                        $_SESSION['role'] = $role;
                    }
                    $_SESSION['message'] = "Utente aggiornato con successo!";
                    $_SESSION['message_type'] = "success";
                    header("Location: index.php?page=users");
                    exit();
                } else {
                    $_SESSION['message'] = "Errore durante l'aggiornamento dell'utente.";
                    $_SESSION['message_type'] = "error";
                    $user['username'] = $username; // Aggiorna il valore per ripopolare il form
                    $user['role'] = $role;
                    require_once __DIR__ . '/../../views/users/user_form.php'; // Usa il nuovo nome
                    return;
                }
            } else {
                $_SESSION['message'] = "Errore di validazione: " . implode(" ", $validation_errors);
                $_SESSION['message_type'] = "error";
                $user['username'] = $username; // Aggiorna il valore per ripopolare il form
                $user['role'] = $role;
                require_once __DIR__ . '/../../views/users/user_form.php'; // Usa il nuovo nome
                return;
            }
        }
        require_once __DIR__ . '/../../views/users/user_form.php'; // Usa il nuovo nome
    }

    /**
     * Gestisce l'eliminazione di un utente.
     * @param int $id L'ID dell'utente da eliminare.
     */
    public function delete($id) {
        // Permesso: Solo Superadmin e Admin possono eliminare utenti
        if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
            $_SESSION['message'] = "Accesso negato. Non hai i permessi per eliminare utenti.";
            $_SESSION['message_type'] = "error";
            ob_end_clean(); // Pulisci il buffer prima del reindirizzamento
            header("Location: index.php?page=dashboard");
            exit();
        }

        // Impedisci a un utente di eliminare se stesso
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id) {
            $_SESSION['message'] = "Non puoi eliminare il tuo stesso account!";
            $_SESSION['message_type'] = "error";
            ob_end_clean(); // Pulisci il buffer prima del reindirizzamento
            header("Location: index.php?page=users");
            exit();
        }

        if (!$id) {
            $_SESSION['message'] = "ID utente non specificato.";
            $_SESSION['message_type'] = "error";
            ob_end_clean(); // Pulisci il buffer prima del reindirizzamento
            header("Location: index.php?page=users");
            exit();
        }

        if ($this->userModel->delete($id)) {
            $_SESSION['message'] = "Utente eliminato con successo!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Errore durante l'eliminazione dell'utente.";
            $_SESSION['message_type'] = "error";
        }
        ob_end_clean(); // Pulisci il buffer prima del reindirizzamento
        header("Location: index.php?page=users");
        exit();
    }
}
