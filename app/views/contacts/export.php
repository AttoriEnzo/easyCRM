<?php
// app/views/contacts/export.php

// Questo file verrà caricato dal ContactController->export()
// Non riceve variabili dirette in fase di caricamento GET,
// ma elenca i campi disponibili per l'esportazione.

// Definisci qui tutti i campi disponibili nella tabella 'contacts'
// L'ordine qui influenzerà l'ordine delle colonne nel CSV esportato
$available_fields = [
    'id' => 'ID Contatto',
    'first_name' => 'Nome',
    'last_name' => 'Cognome',
    'email' => 'Email',
    'phone' => 'Telefono Fisso',
    'mobile_phone' => 'Telefono Cellulare',
    'company' => 'Azienda',
    'client_type' => 'Tipo Cliente',
    'tax_code' => 'Codice Fiscale',
    'vat_number' => 'Partita IVA',
    'sdi' => 'Codice SDI',
    'company_address' => 'Indirizzo Azienda',
    'company_city' => 'Città Azienda',
    'company_zip' => 'CAP Azienda',
    'company_province' => 'Provincia Azienda',
    'pec' => 'PEC',
    'last_contact_date' => 'Data Ultimo Contatto',
    'contact_medium' => 'Mezzo Contatto',
    'order_executed' => 'Ordine Eseguito',
    'created_at' => 'Data Creazione'
];

// Per il pre-selezione, potremmo voler selezionare tutto di default
// o basarci su una selezione precedente, ma per ora le lasciamo tutte non selezionate.
// In caso di POST fallito, potresti ripopolare $_POST['fields'] per mantenere le selezioni utente.
$selected_fields = $_POST['fields'] ?? array_keys($available_fields); // Seleziona tutto di default per POST o prima visita
?>

<h2 class="text-2xl font-semibold mb-4">Esporta Contatti in CSV</h2>

<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <p class="mb-4 text-gray-700">Seleziona i campi che desideri includere nel file CSV. Solo i contatti esistenti nel database verranno esportati.</p>

    <form method="POST" action="index.php?page=contacts&action=export" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <?php foreach ($available_fields as $field_name => $field_label): ?>
            <div class="flex items-center">
                <input type="checkbox" id="field_<?php echo htmlspecialchars($field_name); ?>"
                       name="fields[]" value="<?php echo htmlspecialchars($field_name); ?>"
                       class="w-auto h-auto text-indigo-600 rounded border-gray-300 focus:ring-indigo-500"
                       <?php echo in_array($field_name, $selected_fields) ? 'checked' : ''; ?>>
                <label for="field_<?php echo htmlspecialchars($field_name); ?>" class="ml-2 text-gray-700 text-sm font-bold cursor-pointer">
                    <?php echo htmlspecialchars($field_label); ?>
                </label>
            </div>
        <?php endforeach; ?>

        <div class="md:col-span-3 lg:col-span-4 flex justify-between items-center mt-6">
            <div>
                <input type="checkbox" id="select_all_fields" class="w-auto h-auto text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                <label for="select_all_fields" class="ml-2 text-gray-700 text-sm font-bold cursor-pointer">Seleziona Tutto</label>
            </div>
            <button type="submit" class="btn btn-primary ml-auto">Scarica CSV</button>
        </div>
    </form>
</div>

<div class="mt-6">
    <a href="index.php?page=contacts" class="btn btn-tertiary">Torna ai Contatti</a>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('select_all_fields');
        const fieldCheckboxes = document.querySelectorAll('input[name="fields[]"]');

        selectAllCheckbox.addEventListener('change', function() {
            fieldCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });

        // Aggiorna lo stato di "Seleziona Tutto" quando i singoli campi cambiano
        fieldCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (!this.checked) {
                    selectAllCheckbox.checked = false;
                } else {
                    const allChecked = Array.from(fieldCheckboxes).every(cb => cb.checked);
                    selectAllCheckbox.checked = allChecked;
                }
            });
        });

        // Inizializza lo stato del "Seleziona Tutto" al caricamento
        const allCheckedOnLoad = Array.from(fieldCheckboxes).every(cb => cb.checked);
        selectAllCheckbox.checked = allCheckedOnLoad;
    });
</script>