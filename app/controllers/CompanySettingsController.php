<?php
// app/controllers/CompanySettingsController.php

require_once __DIR__ . '/../models/CompanySetting.php'; // Includi il modello CompanySetting

class CompanySettingsController {
    private $companySettingModel;

    /**
     * Costruttore del CompanySettingsController.
     * Inizializza il modello CompanySetting.
     * @param CompanySetting $companySettingModel L'istanza del modello CompanySetting.
     */
    public function __construct(CompanySetting $companySettingModel) {
        $this->companySettingModel = $companySettingModel;
    }

    /**
     * Mostra il form delle impostazioni aziendali e gestisce il salvataggio.
     */
    public function index() {
        // PERMESSO: Solo Superadmin può accedere a questa pagina
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
            $_SESSION['message'] = "Accesso negato. Non hai i permessi per gestire le impostazioni aziendali.";
            $_SESSION['message_type'] = "error";
            header("Location: index.php?page=dashboard");
            exit();
        }

        $company_settings = $this->companySettingModel->read(); // Tenta di leggere le impostazioni esistenti

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->companySettingModel->company_name = $_POST['company_name'] ?? '';
            $this->companySettingModel->address = $_POST['address'] ?? '';
            $this->companySettingModel->city = $_POST['city'] ?? '';
            $this->companySettingModel->zip = $_POST['zip'] ?? '';
            $this->companySettingModel->province = $_POST['province'] ?? '';
            $this->companySettingModel->vat_number = $_POST['vat_number'] ?? '';
            $this->companySettingModel->tax_code = $_POST['tax_code'] ?? '';
            $this->companySettingModel->phone = $_POST['phone'] ?? '';
            $this->companySettingModel->email = $_POST['email'] ?? '';
            $this->companySettingModel->pec = $_POST['pec'] ?? '';
            $this->companySettingModel->sdi = $_POST['sdi'] ?? '';
            $this->companySettingModel->logo_url = $_POST['logo_url'] ?? '';

            // Validazione di base (puoi aggiungere regole più specifiche in base alle tue esigenze)
            $validation_errors = [];
            if (empty($this->companySettingModel->company_name) || empty($this->companySettingModel->address) || 
                empty($this->companySettingModel->city) || empty($this->companySettingModel->zip) || 
                empty($this->companySettingModel->province)) {
                $validation_errors[] = "Nome Azienda, Indirizzo, Città, CAP e Provincia sono obbligatori.";
            }
            if (!preg_match('/^\d{5}$/', $this->companySettingModel->zip)) {
                $validation_errors[] = "Il CAP deve essere di 5 cifre numeriche.";
            }
            if (!preg_match('/^[A-Z]{2}$/', $this->companySettingModel->province)) {
                $validation_errors[] = "La Provincia deve essere di 2 lettere maiuscole.";
            }
            if (!empty($this->companySettingModel->vat_number) && !preg_match('/^\d{11}$/', $this->companySettingModel->vat_number)) {
                $validation_errors[] = "La Partita IVA deve essere di 11 cifre numeriche.";
            }
            if (!empty($this->companySettingModel->tax_code) && !preg_match('/(^([A-Z0-9]{16})$)|(^(\d{11})$)/i', $this->companySettingModel->tax_code)) {
                $validation_errors[] = "Il Codice Fiscale non è nel formato corretto (16 alfanumerici o 11 numerici).";
            }
            if (!empty($this->companySettingModel->sdi) && !preg_match('/^[A-Z0-9]{7}$/i', $this->companySettingModel->sdi)) {
                $validation_errors[] = "Il Codice SDI deve essere di 7 caratteri alfanumerici.";
            }
            if (!empty($this->companySettingModel->pec) && !filter_var($this->companySettingModel->pec, FILTER_VALIDATE_EMAIL)) {
                $validation_errors[] = "La PEC non è un indirizzo email valido.";
            }

            if (empty($validation_errors)) {
                $result = $this->companySettingModel->save(); // Questo metodo gestisce sia create che update
                if ($result['success']) {
                    $_SESSION['message'] = "Impostazioni aziendali salvate con successo!";
                    $_SESSION['message_type'] = "success";
                    // Ricarica le impostazioni dopo il salvataggio per mostrare i dati aggiornati
                    $company_settings = $this->companySettingModel->read(); 
                } else {
                    $_SESSION['message'] = "Errore durante il salvataggio delle impostazioni aziendali: " . ($result['error'] ?? 'Errore sconosciuto.');
                    $_SESSION['message_type'] = "error";
                    // Ripopola il form con i dati inviati in caso di errore
                    $company_settings = $_POST; 
                }
            } else {
                $_SESSION['message'] = "Errore di validazione: " . implode(" ", $validation_errors);
                $_SESSION['message_type'] = "error";
                // Ripopola il form con i dati inviati in caso di errore
                $company_settings = $_POST; 
            }
        }
        
        // Includi la vista del form delle impostazioni
        require_once __DIR__ . '/../views/company_settings/form.php';
    }
}
