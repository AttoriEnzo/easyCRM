<?php
// app/views/contacts/import.php

// Questa vista mostra il form per caricare un file CSV per l'importazione dei contatti.
?>

<h2 class="text-2xl font-semibold mb-4">Importa Contatti da CSV</h2>

<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <p class="mb-4 text-gray-700">Carica un file CSV per importare nuovi contatti nel sistema. Assicurati che il file sia formattato correttamente.</p>
    <p class="mb-4 text-sm text-gray-600">
        Il file CSV dovrebbe avere un'intestazione con i nomi delle colonne che corrispondono ai campi del CRM (es. `first_name`, `last_name`, `email`, `phone`, `company`, `client_type`, `tax_code`, `vat_number`, `sdi`, `company_address`, `company_city`, `company_zip`, `company_province`, `pec`, `mobile_phone`, `last_contact_date`, `contact_medium`, `order_executed`).
        I campi `first_name` e `last_name` sono obbligatori.
    </p>

    <form method="POST" action="index.php?page=contacts&action=import" enctype="multipart/form-data">
        <label for="csv_file" class="block text-gray-700 text-sm font-bold mb-2">Seleziona File CSV:</label>
        <input type="file" id="csv_file" name="csv_file" accept=".csv" required class="mb-4">
        
        <button type="submit" class="btn btn-primary">Carica e Importa</button>
    </form>
</div>

<div class="mt-6">
    <a href="index.php?page=dashboard" class="btn btn-tertiary">Torna alla Dashboard</a>
</div>