<?php
// app/views/contacts/add_edit.php

// Le variabili $contact (se in modalità modifica), $form_title, $submit_button_text, $action_url
// sono passate dal ContactController->add() o ContactController->edit()

// Determina se siamo in modalità aggiunta o modifica
$is_edit_mode = isset($contact) && $contact !== null;
$form_title = $is_edit_mode ? 'Modifica Contatto' : 'Aggiungi Nuovo Contatto';
$submit_button_text = $is_edit_mode ? 'Aggiorna Contatto' : 'Salva Contatto';
$action_url = $is_edit_mode ? "index.php?page=contacts&action=edit&id=" . htmlspecialchars($contact['id']) : "index.php?page=contacts&action=add";
$cancel_url = $is_edit_mode ? "index.php?page=contacts&action=view&id=" . htmlspecialchars($contact['id']) : "index.php?page=contacts";

// Pre-popola i valori del form in base alla modalità
$first_name = $is_edit_mode ? htmlspecialchars($contact['first_name']) : '';
$last_name = $is_edit_mode ? htmlspecialchars($contact['last_name']) : '';
$email = $is_edit_mode ? htmlspecialchars($contact['email']) : '';
$phone = $is_edit_mode ? htmlspecialchars($contact['phone'] ?? '') : ''; // Telefono Fisso
$company = $is_edit_mode ? htmlspecialchars($contact['company'] ?? '') : ''; // Assicurati che sia 'N/D' se null, ma qui è per form
$last_contact_date = !empty($is_edit_mode) ? htmlspecialchars($contact['last_contact_date'] ?? '') : ''; // Modificato per gestire anche null
$contact_medium = $is_edit_mode ? htmlspecialchars($contact['contact_medium'] ?? '') : '';
$order_executed_checked = $is_edit_mode && ($contact['order_executed'] == 1) ? 'checked' : '';

// CAMPI AGGIUNTI: Pre-popola anche questi
$client_type = $is_edit_mode ? htmlspecialchars($contact['client_type'] ?? 'Privato') : 'Privato';
$tax_code = $is_edit_mode ? htmlspecialchars($contact['tax_code'] ?? '') : '';
$vat_number = $is_edit_mode ? htmlspecialchars($contact['vat_number'] ?? '') : '';

// NUOVI CAMPI AGGIUNTI: Pre-popola anche questi
$sdi = $is_edit_mode ? htmlspecialchars($contact['sdi'] ?? '') : '';
$company_address = $is_edit_mode ? htmlspecialchars($contact['company_address'] ?? '') : '';
$company_city = $is_edit_mode ? htmlspecialchars($contact['company_city'] ?? '') : '';
$company_zip = $is_edit_mode ? htmlspecialchars($contact['company_zip'] ?? '') : '';
$company_province = $is_edit_mode ? htmlspecialchars($contact['company_province'] ?? '') : '';
$pec = $is_edit_mode ? htmlspecialchars($contact['pec'] ?? '') : '';
$mobile_phone = $is_edit_mode ? htmlspecialchars($contact['mobile_phone'] ?? '') : '';

?>

<h2 class="text-2xl font-semibold mb-4"><?php echo $form_title; ?></h2>
<form method="POST" action="<?php echo $action_url; ?>">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4">
        <!-- Colonna 1: Dati base -->
        <div>
            <label for="first_name" class="block text-gray-700 text-sm font-bold mb-1">Nome:</label>
            <input type="text" id="first_name" name="first_name" value="<?php echo $first_name; ?>" class="mb-3">

            <label for="last_name" class="block text-gray-700 text-sm font-bold mb-1">Cognome:</label>
            <input type="text" id="last_name" name="last_name" value="<?php echo $last_name; ?>" class="mb-3">

            <label for="email" class="block text-gray-700 text-sm font-bold mb-1">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo $email; ?>" class="mb-3">

            <label for="phone" class="block text-gray-700 text-sm font-bold mb-1">Telefono Fisso:</label>
            <input type="tel" id="phone" name="phone" value="<?php echo $phone; ?>" pattern="^[0-9\s\-\(\)\+]{7,20}$" title="Inserire un numero di telefono valido (es. 02 1234567)." class="mb-3">

            <label for="mobile_phone" class="block text-gray-700 text-sm font-bold mb-1">Telefono Cellulare:</label>
            <input type="tel" id="mobile_phone" name="mobile_phone" value="<?php echo $mobile_phone; ?>" pattern="^[0-9\s\-\(\)\+]{7,20}$" title="Inserire un numero di telefono cellulare valido (es. +39 333 1234567)." class="mb-3">
        </div>

        <!-- Colonna 2: Dati Aziendali / Tipo -->
        <div>
            <label for="company" class="block text-gray-700 text-sm font-bold mb-1">Azienda: <span class="text-red-500">*</span></label>
            <input type="text" id="company" name="company" value="<?php echo $company; ?>" class="mb-3"> <!-- 'required' gestito dal controller -->

            <label for="client_type" class="block text-gray-700 text-sm font-bold mb-1">Tipo:</label>
            <select id="client_type" name="client_type" class="mb-3">
                <option value="Privato" <?php echo ($client_type == 'Privato') ? 'selected' : ''; ?>>Privato</option>
                <option value="Ditta Individuale" <?php echo ($client_type == 'Ditta Individuale') ? 'selected' : ''; ?>>Ditta Individuale</option>
                <option value="Azienda/Società" <?php echo ($client_type == 'Azienda/Società') ? 'selected' : ''; ?>>Azienda/Società</option>
                <option value="Fornitore" <?php echo ($client_type == 'Fornitore') ? 'selected' : ''; ?>>Fornitore</option> <!-- NUOVA OPZIONE -->
            </select>

            <div id="vat_number_field" class="hidden">
                <label for="vat_number" class="block text-gray-700 text-sm font-bold mb-1">Partita IVA (11 cifre numeriche):</label>
                <input type="text" id="vat_number" name="vat_number" value="<?php echo $vat_number; ?>" pattern="\d{11}" title="Inserire esattamente 11 cifre numeriche." class="mb-3">
            </div>

            <div id="tax_code_field" class="hidden">
                <label for="tax_code" class="block text-gray-700 text-sm font-bold mb-1">Codice Fiscale:</label>
                <input type="text" id="tax_code" name="tax_code" value="<?php echo $tax_code; ?>" class="mb-1">
                <p id="tax_code_hint" class="text-xs text-gray-500 mb-3"></p>
            </div>
            
            <label for="pec" class="block text-gray-700 text-sm font-bold mb-1">PEC:</label>
            <input type="email" id="pec" name="pec" value="<?php echo $pec; ?>" title="Inserire un indirizzo PEC valido." class="mb-3">
        </div>

        <!-- Colonna 3: Dettagli Aziendali Aggiuntivi -->
        <div>
            <label for="sdi" class="block text-gray-700 text-sm font-bold mb-1">Codice SDI (7 caratteri alfanumerici):</label>
            <input type="text" id="sdi" name="sdi" value="<?php echo $sdi; ?>" pattern="^[A-Z0-9]{7}$" title="Inserire esattamente 7 caratteri alfanumerici (lettere maiuscole o numeri)." class="mb-3">

            <label for="company_address" class="block text-gray-700 text-sm font-bold mb-1">Indirizzo Azienda:</label>
            <input type="text" id="company_address" name="company_address" value="<?php echo $company_address; ?>" maxlength="255" class="mb-3">

            <label for="company_city" class="block text-gray-700 text-sm font-bold mb-1">Città Azienda:</label>
            <input type="text" id="company_city" name="company_city" value="<?php echo $company_city; ?>" maxlength="100" class="mb-3">

            <label for="company_zip" class="block text-gray-700 text-sm font-bold mb-1">CAP Azienda (5 cifre numeriche):</label>
            <input type="text" id="company_zip" name="company_zip" value="<?php echo $company_zip; ?>" pattern="^\d{5}$" title="Inserire esattamente 5 cifre numeriche." class="mb-3">

            <label for="company_province" class="block text-gray-700 text-sm font-bold mb-1">Provincia Azienda (2 lettere):</label>
            <input type="text" id="company_province" name="company_province" value="<?php echo $company_province; ?>" pattern="^[A-Z]{2}$" title="Inserire esattamente 2 lettere maiuscole per la provincia (es. RM, MI)." class="mb-3">

            <label for="last_contact_date" class="block text-gray-700 text-sm font-bold mb-1">Data Ultimo Contatto:</label>
            <input type="date" id="last_contact_date" name="last_contact_date" value="<?php echo $last_contact_date; ?>" class="mb-3">

            <label for="contact_medium" class="block text-gray-700 text-sm font-bold mb-1">Mezzo Contatto:</label>
            <select id="contact_medium" name="contact_medium" class="mb-3">
                <option value="" <?php echo ($contact_medium == '') ? 'selected' : ''; ?>>Seleziona</option>
                <option value="Telefono" <?php echo ($contact_medium == 'Telefono') ? 'selected' : ''; ?>>Telefono</option>
                <option value="Email" <?php echo ($contact_medium == 'Email') ? 'selected' : ''; ?>>Email</option>
                <option value="Meeting" <?php echo ($contact_medium == 'Meeting') ? 'selected' : ''; ?>>Meeting</option>
                <option value="Altro" <?php echo ($contact_medium == 'Altro') ? 'selected' : ''; ?>>Altro</option>
            </select>

            <div class="flex items-center mb-3">
                <input type="checkbox" id="order_executed" name="order_executed" class="w-auto mr-2" <?php echo $order_executed_checked; ?>>
                <label for="order_executed" class="text-gray-700 text-sm font-bold">Ordine Eseguito</label>
            </div>
        </div>
    </div> <!-- Fine della griglia dei campi -->

    <div class="flex justify-end mt-6">
        <button type="submit" class="btn btn-primary"><?php echo $submit_button_text; ?></button>
        <a href="<?php echo $cancel_url; ?>" class="btn btn-secondary ml-2">Annulla</a>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const clientTypeSelect = document.getElementById('client_type');
        const vatNumberField = document.getElementById('vat_number_field');
        const vatNumberInput = document.getElementById('vat_number');
        const taxCodeField = document.getElementById('tax_code_field');
        const taxCodeInput = document.getElementById('tax_code');
        const taxCodeHint = document.getElementById('tax_code_hint');
        
        // Input per la gestione e il reset (non più nascosti ma i loro valori potrebbero essere resettati)
        const sdiInput = document.getElementById('sdi');
        const companyAddressInput = document.getElementById('company_address');
        const companyCityInput = document.getElementById('company_city');
        const companyZipInput = document.getElementById('company_zip');
        const companyProvinceInput = document.getElementById('company_province');
        const pecInput = document.getElementById('pec');

        // Funzione per mostrare/nascondere i campi e aggiornare i pattern
        function toggleFiscalFields() {
            const selectedType = clientTypeSelect.value;

            // Nascondi tutti i campi fiscali all'inizio per resettare lo stato visivo
            vatNumberField.classList.add('hidden');
            taxCodeField.classList.add('hidden');
            
            // Logica per determinare la visibilità dei campi e i pattern di validazione
            if (selectedType === 'Privato') {
                // Per i privati:
                // Codice Fiscale: Visibile (ma non obbligatorio per l'HTML, la validazione avviene nel controller)
                taxCodeField.classList.remove('hidden');
                taxCodeInput.setAttribute('pattern', '^[A-Z0-9]{16}$');
                taxCodeInput.setAttribute('title', 'Inserire esattamente 16 caratteri alfanumerici (alfanumerico maiuscolo).');
                taxCodeHint.textContent = 'Formato Codice Fiscale: 16 caratteri alfanumerici.';

            } else if (selectedType === 'Ditta Individuale') {
                // Per Ditta Individuale:
                // Partita IVA: Visibile
                vatNumberField.classList.remove('hidden');
                vatNumberInput.setAttribute('pattern', '\\d{11}');
                vatNumberInput.setAttribute('title', 'Inserire esattamente 11 cifre numeriche per la Partita IVA.');

                // Codice Fiscale: Visibile
                taxCodeField.classList.remove('hidden');
                taxCodeInput.setAttribute('pattern', '^[A-Z0-9]{16}$');
                taxCodeInput.setAttribute('title', 'Inserire esattamente 16 caratteri alfanumerici (alfanumerico maiuscolo) per il Codice Fiscale.');
                taxCodeHint.textContent = 'Codice Fiscale: 16 caratteri alfanumerici.';

            } else if (selectedType === 'Azienda/Società') {
                // Per Azienda/Società:
                // Partita IVA: Visibile
                vatNumberField.classList.remove('hidden');
                vatNumberInput.setAttribute('pattern', '\\d{11}');
                vatNumberInput.setAttribute('title', 'Inserire esattamente 11 cifre numeriche per la Partita IVA.');

                // Codice Fiscale: Visibile
                taxCodeField.classList.remove('hidden');
                taxCodeInput.setAttribute('pattern', '\\d{11}');
                taxCodeInput.setAttribute('title', 'Inserire esattamente 11 cifre numeriche per il Codice Fiscale.');
                taxCodeHint.textContent = 'Codice Fiscale: 11 cifre numeriche.';
            
            } else if (selectedType === 'Fornitore') { // NUOVO TIPO: Fornitore
                // Per Fornitore:
                // Partita IVA: Visibile
                vatNumberField.classList.remove('hidden');
                vatNumberInput.setAttribute('pattern', '\\d{11}');
                vatNumberInput.setAttribute('title', 'Inserire esattamente 11 cifre numeriche per la Partita IVA.');

                // Codice Fiscale: Visibile e Flessibile
                taxCodeField.classList.remove('hidden');
                taxCodeInput.setAttribute('pattern', '(^([A-Z0-9]{16})$)|(^(\\d{11})$)'); // Regex flessibile
                taxCodeInput.setAttribute('title', 'Inserire 16 caratteri alfanumerici O 11 cifre numeriche per il Codice Fiscale.');
                taxCodeHint.textContent = 'Codice Fiscale: 16 caratteri alfanumerici (se privato) O 11 cifre numeriche (se azienda).';
            }
        }

        // Event listener per il cambio del tipo di cliente
        clientTypeSelect.addEventListener('change', toggleFiscalFields);

        // Chiamata iniziale per impostare lo stato corretto all'apertura della pagina
        toggleFiscalFields();
    });
</script>
