<?php
// app/views/company_settings/form.php

// La variabile $company_settings è passata dal CompanySettingsController->index()
// Contiene i dati esistenti se presenti, o un array vuoto/POST se ci sono stati errori di validazione

// Pre-popola i valori del form. Se $company_settings è vuoto o non impostato, userà stringhe vuote.
// In caso di POST fallito, $_POST conterrà i valori per ripopolare il form.
$company_name = htmlspecialchars($company_settings['company_name'] ?? '');
$address = htmlspecialchars($company_settings['address'] ?? '');
$city = htmlspecialchars($company_settings['city'] ?? '');
$zip = htmlspecialchars($company_settings['zip'] ?? '');
$province = htmlspecialchars($company_settings['province'] ?? '');
$vat_number = htmlspecialchars($company_settings['vat_number'] ?? '');
$tax_code = htmlspecialchars($company_settings['tax_code'] ?? '');
$phone = htmlspecialchars($company_settings['phone'] ?? '');
$email = htmlspecialchars($company_settings['email'] ?? '');
$pec = htmlspecialchars($company_settings['pec'] ?? '');
$sdi = htmlspecialchars($company_settings['sdi'] ?? '');
$logo_url = htmlspecialchars($company_settings['logo_url'] ?? ''); // URL del logo (opzionale)

$form_title = 'Impostazioni Aziendali';
$submit_button_text = 'Salva Impostazioni';
$action_url = 'index.php?page=company_settings'; // L'azione POST va alla stessa pagina

?>

<h2 class="text-2xl font-semibold mb-4"><?php echo $form_title; ?></h2>

<form method="POST" action="<?php echo $action_url; ?>" class="bg-white p-6 rounded-lg shadow-md max-w-2xl mx-auto">
    <p class="text-sm text-gray-600 mb-6">Completa i campi con i dati della tua azienda. Queste informazioni verranno utilizzate per i documenti commerciali.</p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
        <!-- Colonna 1: Dati Anagrafici Aziendali -->
        <div>
            <label for="company_name" class="block text-gray-700 text-sm font-bold mb-2">Nome Azienda: <span class="text-red-500">*</span></label>
            <input type="text" id="company_name" name="company_name" value="<?php echo $company_name; ?>" required class="w-full mb-3">

            <label for="address" class="block text-gray-700 text-sm font-bold mb-2">Indirizzo: <span class="text-red-500">*</span></label>
            <input type="text" id="address" name="address" value="<?php echo $address; ?>" required class="w-full mb-3">

            <label for="city" class="block text-gray-700 text-sm font-bold mb-2">Città: <span class="text-red-500">*</span></label>
            <input type="text" id="city" name="city" value="<?php echo $city; ?>" required class="w-full mb-3">

            <label for="zip" class="block text-gray-700 text-sm font-bold mb-2">CAP (5 cifre): <span class="text-red-500">*</span></label>
            <input type="text" id="zip" name="zip" value="<?php echo $zip; ?>" pattern="^\d{5}$" title="Inserire esattamente 5 cifre numeriche." required class="w-full mb-3">

            <label for="province" class="block text-gray-700 text-sm font-bold mb-2">Provincia (2 lettere maiuscole): <span class="text-red-500">*</span></label>
            <input type="text" id="province" name="province" value="<?php echo $province; ?>" pattern="^[A-Z]{2}$" title="Inserire esattamente 2 lettere maiuscole per la provincia (es. RM, MI)." required class="w-full uppercase mb-3">
        </div>

        <!-- Colonna 2: Dati Fiscali e Contatti -->
        <div>
            <label for="vat_number" class="block text-gray-700 text-sm font-bold mb-2">Partita IVA (11 cifre numeriche):</label>
            <input type="text" id="vat_number" name="vat_number" value="<?php echo $vat_number; ?>" pattern="^\d{11}$" title="Inserire esattamente 11 cifre numeriche." class="w-full mb-3">

            <label for="tax_code" class="block text-gray-700 text-sm font-bold mb-2">Codice Fiscale (16 alfanumerici o 11 numerici):</label>
            <input type="text" id="tax_code" name="tax_code" value="<?php echo $tax_code; ?>" pattern="(^([A-Z0-9]{16})$)|(^(\d{11})$)" title="Inserire 16 caratteri alfanumerici o 11 cifre numeriche." class="w-full mb-3">

            <label for="phone" class="block text-gray-700 text-sm font-bold mb-2">Telefono:</label>
            <input type="tel" id="phone" name="phone" value="<?php echo $phone; ?>" pattern="^[0-9\s\-\(\)\+]{7,20}$" title="Inserire un numero di telefono valido (es. +39 02 1234567)." class="w-full mb-3">

            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo $email; ?>" class="w-full mb-3">

            <label for="pec" class="block text-gray-700 text-sm font-bold mb-2">PEC:</label>
            <input type="email" id="pec" name="pec" value="<?php echo $pec; ?>" class="w-full mb-3">

            <label for="sdi" class="block text-gray-700 text-sm font-bold mb-2">Codice SDI (7 caratteri alfanumerici):</label>
            <input type="text" id="sdi" name="sdi" value="<?php echo $sdi; ?>" pattern="^[A-Z0-9]{7}$" title="Inserire esattamente 7 caratteri alfanumerici (lettere maiuscole o numeri)." class="w-full mb-3">

            <label for="logo_url" class="block text-gray-700 text-sm font-bold mb-2">URL Logo Aziendale (Opzionale):</label>
            <input type="url" id="logo_url" name="logo_url" value="<?php echo $logo_url; ?>" placeholder="Es. http://tuodominio.com/img/logo.png" class="w-full mb-3">
        </div>
    </div> <!-- Fine della griglia dei campi -->

    <div class="flex items-center justify-between mt-6">
        <button type="submit" class="btn btn-primary">
            <?php echo $submit_button_text; ?>
        </button>
        <a href="index.php?page=dashboard" class="btn btn-secondary">Annulla</a>
    </div>
</form>
