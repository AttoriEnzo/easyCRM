<?php
// app/controllers/DashboardController.php

require_once __DIR__ . '/../models/Contact.php';
require_once __DIR__ . '/../models/Interaction.php';
require_once __DIR__ . '/../models/Repair.php'; // NUOVO: Includi il modello Repair

class DashboardController {
    private $contactModel;
    private $interactionModel;
    private $repairModel; // NUOVO: Variabile per il modello Repair

    /**
     * Costruttore del DashboardController.
     * Inizializza i modelli necessari per recuperare i dati della dashboard.
     * @param Contact $contactModel L'istanza del modello Contact.
     * @param Interaction $interactionModel L'istanza del modello Interaction.
     * @param Repair $repairModel L'istanza del modello Repair. // NUOVO
     */
    public function __construct(Contact $contactModel, Interaction $interactionModel, Repair $repairModel) { // NUOVO
        $this->contactModel = $contactModel;
        $this->interactionModel = $interactionModel;
        $this->repairModel = $repairModel; // NUOVO: Inizializza il modello Repair
    }

    /**
     * Gestisce la visualizzazione della pagina dashboard.
     * Recupera il totale dei contatti e le interazioni recenti.
     */
    public function index() {
        // Logging diagnostico: Inizio del metodo index() del DashboardController
        error_log("DashboardController: Inizio metodo index().");

        try {
            // Recupera il totale dei contatti dal modello Contact
            $total_contacts = $this->contactModel->countAll();
            // Logging diagnostico: Totale contatti recuperati
            error_log("DashboardController: Total contacts recuperati: " . $total_contacts);

            // Recupera le interazioni recenti dal modello Interaction
            // PASSAGGIO FONDAMENTALE: Passa l'ID dell'utente e il suo ruolo per il filtro di visibilità
            $recent_interactions = $this->interactionModel->readRecent(
                5, // Limite a 5 interazioni recenti
                $_SESSION['user_id'] ?? null, // ID dell'utente loggato
                $_SESSION['role'] ?? null    // Ruolo dell'utente loggato
            );
            // Logging diagnostico: Numero di interazioni recenti recuperate
            error_log("DashboardController: Interazioni recenti recuperate: " . count($recent_interactions) . " elementi.");

            // NUOVO: Recupera le riparazioni recenti dal modello Repair
            $recent_repairs = $this->repairModel->readRecent(
                5, // Limite a 5 riparazioni recenti
                $_SESSION['user_id'] ?? null, // ID dell'utente loggato
                $_SESSION['role'] ?? null    // Ruolo dell'utente loggato
            );
            // Logging diagnostico: Numero di riparazioni recenti recuperate
            error_log("DashboardController: Riparazioni recenti recuperate: " . count($recent_repairs) . " elementi.");


            // INCLUDE LA VISTA DELLA DASHBOARD
            // Le variabili $total_contacts, $recent_interactions e $recent_repairs saranno disponibili nella vista
            // Logging diagnostico: Tentativo di includere la vista dashboard.php
            error_log("DashboardController: Tentativo di includere la vista dashboard.php.");
            require_once __DIR__ . '/../views/dashboard.php';
            // Logging diagnostico: Vista dashboard.php inclusa con successo
            error_log("DashboardController: Vista dashboard.php inclusa con successo.");

        } catch (Throwable $e) { // Cattura tutti i tipi di errori, inclusi i fatali (da PHP 7+)
            // Logging diagnostico: Errore critico con dettagli
            error_log("DashboardController: Errore critico nel metodo index(): " . $e->getMessage() . " in " . $e->getFile() . " alla linea " . $e->getLine());
            // Mostra un messaggio di errore generico all'utente
            echo "<div class='flash-error'>Si è verificato un errore inaspettato durante il caricamento della dashboard. Per favore, controlla i log del server per maggiori dettagli.</div>";
        }
        // Logging diagnostico: Fine del metodo index() del DashboardController
        error_log("DashboardController: Fine metodo index().");
    }
}
