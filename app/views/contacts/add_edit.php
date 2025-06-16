<?php
// app/views/contacts/form.php

// Le variabili $contact (se in modalità modifica), $form_title, $submit_button_text, $action_url, $cancel_url
// e $client_types, $users_for_assignment sono passate dal ContactController.

$is_edit_mode = isset($contact['id']) && $contact['id'] !== null;
$form_title = $form_title ?? ($is_edit_mode ? 'Modifica Contatto' : 'Crea Nuovo Contatto');
$submit_button_text = $submit_button_text ?? ($is_edit_mode ? 'Aggiorna Contatto' : 'Salva Contatto');

// *************** INIZIO CORREZIONE LINEA 11 (e 12) ***************
// Garantisce che $contact['id'] sia sempre una stringa valida per htmlspecialchars
$contact_id_for_url = htmlspecialchars($contact['id'] ?? '');

$action_url = $action_url ?? ($is_edit_mode ? "index.php?route=contacts/edit&id=" . $contact_id_for_url : "index.php?route=contacts/add");
$cancel_url = $cancel_url ?? ($is_edit_mode ? "index.php?route=contacts/view&id=" . $contact_id_for_url : "index.php?route=contacts/list");
// *************** FINE CORREZIONE ***************

// Pre-popola i valori del form
$first_name = htmlspecialchars($contact['first_name'] ?? '');
$last_name = htmlspecialchars($contact['last_name'] ?? '');
$email = htmlspecialchars($contact['email'] ?? '');
$phone = htmlspecialchars($contact['phone'] ?? '');
$company = htmlspecialchars($contact['company'] ?? '');
$last_contact_date = htmlspecialchars($contact['last_contact_date'] ?? '');
$contact_medium = htmlspecialchars($contact['contact_medium'] ?? '');
$order_executed = isset($contact['order_executed']) && $contact['order_executed'] == 1 ? 'checked' : '';
$client_type_id = htmlspecialchars($contact['client_type_id'] ?? ''); // Ora usa client_type_id
$tax_code = htmlspecialchars($contact['tax_code'] ?? '');
$vat_number = htmlspecialchars($contact['vat_number'] ?? '');
$sdi = htmlspecialchars($contact['sdi'] ?? '');
$company_address = htmlspecialchars($contact['company_address'] ?? '');
$company_city = htmlspecialchars($contact['company_city'] ?? '');
$company_zip = htmlspecialchars($contact['company_zip'] ?? '');
$company_province = htmlspecialchars($contact['company_province'] ?? '');
$pec = htmlspecialchars($contact['pec'] ?? '');
$mobile_phone = htmlspecialchars($contact['mobile_phone'] ?? '');
$assigned_to_user_id = htmlspecialchars($contact['assigned_to_user_id'] ?? '');
$contact_status = htmlspecialchars($contact['status'] ?? 'New');

$contact_statuses = ['New', 'Contacted', 'Qualified', 'Lost', 'Won', 'Active Client']; // Stati contatto

?>

<h2 class="text-2xl font-semibold mb-4"><?php echo $form_title; ?></h2>

<!-- Messaggio flash per errori di validazione del form o successo -->
<?php if (!empty($_SESSION['message'])): ?>
    <div class="flash-message flash-<?php echo htmlspecialchars($_SESSION['message_type']); ?> mb-4">
        <?php echo htmlspecialchars($_SESSION['message']); ?>
    </div>
    <?php 
    // Pulisci il messaggio flash dopo averlo visualizzato nel form
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
    ?>
<?php endif; ?>

<form method="POST" action="<?php echo $action_url; ?>" class="bg-white p-6 rounded-lg shadow-md max-w-full mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Colonna 1: Dati Anagrafici Base -->
        <div>
            <h3 class="text-xl font-semibold mb-3 text-indigo-700">Dettagli Base</h3>

            <label for="first_name" class="block text-gray-700 text-sm font-bold mb-2">Nome: <span class="text-red-500">*</span></label>
            <input type="text" id="first_name" name="first_name" value="<?php echo $first_name; ?>" required
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">

            <label for="last_name" class="block text-gray-700 text-sm font-bold mb-2">Cognome: <span class="text-red-500">*</span></label>
            <input type="text" id="last_name" name="last_name" value="<?php echo $last_name; ?>" required
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">

            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo $email; ?>"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">

            <label for="phone" class="block text-gray-700 text-sm font-bold mb-2">Telefono Fisso:</label>
            <input type="text" id="phone" name="phone" value="<?php echo $phone; ?>"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">

            <label for="mobile_phone" class="block text-gray-700 text-sm font-bold mb-2">Cellulare:</label>
            <input type="text" id="mobile_phone" name="mobile_phone" value="<?php echo $mobile_phone; ?>"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">

            <label for="company" class="block text-gray-700 text-sm font-bold mb-2">Azienda:</label>
            <input type="text" id="company" name="company" value="<?php echo $company; ?>"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">

            <label for="client_type_id" class="block text-gray-700 text-sm font-bold mb-2">Tipo Cliente:</label>
            <select id="client_type_id" name="client_type_id"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">
                <option value="">Seleziona Tipo</option>
                <?php foreach ($client_types as $type): ?>
                    <option value="<?php echo htmlspecialchars($type['id']); ?>"
                            <?php echo ($client_type_id == $type['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($type['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <label for="assigned_to_user_id" class="block text-gray-700 text-sm font-bold mb-2">Assegnato a:</label>
            <select id="assigned_to_user_id" name="assigned_to_user_id"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">
                <option value="">Nessuno</option>
                <?php foreach ($users_for_assignment as $user): ?>
                    <option value="<?php echo htmlspecialchars($user['id']); ?>"
                            <?php echo ($assigned_to_user_id == $user['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($user['username']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Stato Contatto:</label>
            <select id="status" name="status" required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">
                <?php foreach ($contact_statuses as $s): ?>
                    <option value="<?php echo htmlspecialchars($s); ?>"
                            <?php echo ($contact_status == $s) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($s); ?>
                    </option>
                <?php endforeach; ?>
            </select>

        </div>

        <!-- Colonna 2: Dati Commerciali/Fiscali e Note -->
        <div>
            <h3 class="text-xl font-semibold mb-3 text-indigo-700">Dati Commerciali/Fiscali</h3>

            <label for="tax_code" class="block text-gray-700 text-sm font-bold mb-2">Codice Fiscale:</label>
            <input type="text" id="tax_code" name="tax_code" value="<?php echo $tax_code; ?>"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">

            <label for="vat_number" class="block text-gray-700 text-sm font-bold mb-2">Partita IVA:</label>
            <input type="text" id="vat_number" name="vat_number" value="<?php echo $vat_number; ?>"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">

            <label for="sdi" class="block text-gray-700 text-sm font-bold mb-2">Codice SDI:</label>
            <input type="text" id="sdi" name="sdi" value="<?php echo $sdi; ?>"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">

            <label for="pec" class="block text-gray-700 text-sm font-bold mb-2">PEC:</label>
            <input type="email" id="pec" name="pec" value="<?php echo $pec; ?>"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">

            <label for="company_address" class="block text-gray-700 text-sm font-bold mb-2">Indirizzo Azienda (Sede Legale):</label>
            <input type="text" id="company_address" name="company_address" value="<?php echo $company_address; ?>"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">
            
            <label for="company_city" class="block text-gray-700 text-sm font-bold mb-2">Città Azienda:</label>
            <input type="text" id="company_city" name="company_city" value="<?php echo $company_city; ?>"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">

            <label for="company_zip" class="block text-gray-700 text-sm font-bold mb-2">CAP Azienda:</label>
            <input type="text" id="company_zip" name="company_zip" value="<?php echo $company_zip; ?>"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">

            <label for="company_province" class="block text-gray-700 text-sm font-bold mb-2">Provincia Azienda:</label>
            <input type="text" id="company_province" name="company_province" value="<?php echo $company_province; ?>"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">

            <h3 class="text-xl font-semibold mt-6 mb-3 text-indigo-700">Attività e Note</h3>

            <label for="last_contact_date" class="block text-gray-700 text-sm font-bold mb-2">Ultima Data Contatto:</label>
            <input type="date" id="last_contact_date" name="last_contact_date" value="<?php echo $last_contact_date; ?>"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">

            <label for="contact_medium" class="block text-gray-700 text-sm font-bold mb-2">Mezzo Contatto:</label>
            <input type="text" id="contact_medium" name="contact_medium" value="<?php echo $contact_medium; ?>" placeholder="Es. Telefono, Email, Meeting"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">

            <div class="flex items-center mb-4">
                <input type="checkbox" id="order_executed" name="order_executed" value="1" <?php echo $order_executed; ?>
                       class="form-checkbox h-5 w-5 text-indigo-600 rounded">
                <label for="order_executed" class="ml-2 text-gray-700 text-sm font-bold">Ordine già eseguito?</label>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between mt-6">
        <button type="submit" class="btn btn-primary">
            <?php echo $submit_button_text; ?>
        </button>
        <a href="<?php echo $cancel_url; ?>" class="btn btn-secondary">Annulla</a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const clientTypeSelect = document.getElementById('client_type_id');
    const taxCodeInput = document.getElementById('tax_code');
    const vatNumberInput = document.getElementById('vat_number');
    const sdiInput = document.getElementById('sdi');
    const companyInput = document.getElementById('company');
    const companyAddressInput = document.getElementById('company_address');
    const companyCityInput = document.getElementById('company_city');
    const companyZipInput = document.getElementById('company_zip');
    const companyProvinceInput = document.getElementById('company_province');
    const pecInput = document.getElementById('pec');

    function toggleCompanyFields() {
        const selectedType = clientTypeSelect.value;
        // Inizializza clientTypesData se non è già definito
        const clientTypesData = <?php echo json_encode($client_types ?? []); ?>;
        const selectedClientType = clientTypesData.find(type => type.id == selectedType);
        const typeName = selectedClientType ? selectedClientType.name : '';

        // Resetta tutti i campi per mostrare/nascondere in base al tipo
        taxCodeInput.closest('div').style.display = 'block'; // Di default visibile
        vatNumberInput.closest('div').style.display = 'block'; // Di default visibile
        sdiInput.closest('div').style.display = 'block';
        companyInput.closest('div').style.display = 'block';
        companyAddressInput.closest('div').style.display = 'block';
        companyCityInput.closest('div').style.display = 'block';
        companyZipInput.closest('div').style.display = 'block';
        companyProvinceInput.closest('div').style.display = 'block';
        pecInput.closest('div').style.display = 'block';
        
        // Per i privati, nascondi campi aziendali
        if (typeName === 'Privato') {
            vatNumberInput.value = '';
            vatNumberInput.closest('div').style.display = 'none';
            sdiInput.value = '';
            sdiInput.closest('div').style.display = 'none';
            companyInput.value = '';
            companyInput.closest('div').style.display = 'none';
            companyAddressInput.value = '';
            companyAddressInput.closest('div').style.display = 'none';
            companyCityInput.value = '';
            companyCityInput.closest('div').style.display = 'none';
            companyZipInput.value = '';
            companyZipInput.closest('div').style.display = 'none';
            companyProvinceInput.value = '';
            companyProvinceInput.closest('div').style.display = 'none';
            pecInput.value = '';
            pecInput.closest('div').style.display = 'none';
        } else if (typeName === 'Ditta Individuale' || typeName === 'Azienda/Società') {
            // Per aziende, il codice fiscale potrebbe essere facoltativo se la P.IVA è presente
            // Oltre al VAT, PEC e SDI sono generalmente obbligatori per la fatturazione elettronica
            // Non nascondiamo nulla qui, ma potremmo aggiungere validazioni JavaScript più avanti
        }
    }

    // Esegui la funzione al caricamento della pagina e al cambio del tipo cliente
    toggleCompanyFields();
    clientTypeSelect.addEventListener('change', toggleCompanyFields);
});
</script>
