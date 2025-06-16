<?php
// app/views/repairs/repair_form.php

// Le variabili $repair (se in modalità modifica), $form_title, $submit_button_text, $action_url
// e $contacts_for_dropdown sono passate dal RepairController.

// Determina se siamo in modalità aggiunta o modifica
$is_edit_mode = isset($repair['id']) && $repair['id'] !== null;
$form_title = $is_edit_mode ? 'Modifica Riparazione' : 'Aggiungi Nuova Riparazione';
$submit_button_text = $is_edit_mode ? 'Aggiorna Riparazione' : 'Crea Riparazione';
$action_url = $is_edit_mode ? "index.php?page=repairs&action=edit&id=" . htmlspecialchars($repair['id']) : "index.php?page=repairs&action=add";
$cancel_url = "index.php?page=repairs"; // Torna alla lista riparazioni

// Pre-popola i valori del form in base alla modalità
$contact_id = htmlspecialchars($repair['contact_id'] ?? '');
$device_type = htmlspecialchars($repair['device_type'] ?? '');
$brand = htmlspecialchars($repair['brand'] ?? '');
$model = htmlspecialchars($repair['model'] ?? '');
$serial_number = htmlspecialchars($repair['serial_number'] ?? '');
$problem_description = htmlspecialchars($repair['problem_description'] ?? '');
$accessories = htmlspecialchars($repair['accessories'] ?? '');
$reception_date = htmlspecialchars($repair['reception_date'] ?? date('Y-m-d')); // Default alla data odierna per l'aggiunta
$ddt_number = htmlspecialchars($repair['ddt_number'] ?? '');
$ddt_date = htmlspecialchars($repair['ddt_date'] ?? '');
$status = htmlspecialchars($repair['status'] ?? 'In Attesa'); // Default 'In Attesa'
$technician_notes = htmlspecialchars($repair['technician_notes'] ?? '');
// estimated_cost NON viene più preso direttamente dal DB, ma sarà calcolato dal JS
$estimated_cost = htmlspecialchars($repair['estimated_cost'] ?? '0.00'); // Mantiene il vecchio valore come default iniziale
$completion_date = htmlspecialchars($repair['completion_date'] ?? '');
$shipping_date = htmlspecialchars($repair['shipping_date'] ?? '');
$tracking_code = htmlspecialchars($repair['tracking_code'] ?? '');

// Variabile per passare gli interventi già selezionati alla pagina di selezione (se in modifica)
// Questa verrà riempita dal controller quando modifichiamo una riparazione esistente
$initial_repair_items_json = htmlspecialchars($repair['repair_items_json'] ?? '[]');

// Array degli stati della riparazione per il dropdown
$repair_statuses = ['In Attesa', 'In Lavorazione', 'Ricambi Ordinati', 'In Test', 'Completata', 'Annullata', 'Ritirata'];
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

<form method="POST" action="<?php echo $action_url; ?>" class="bg-white p-6 rounded-lg shadow-md max-w-4xl mx-auto">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Colonna 1: Dati Prodotto e Problema -->
        <div>
            <h3 class="text-xl font-semibold mb-3 text-indigo-700">Dettagli Prodotto e Problema</h3>

            <div class="flex items-center justify-between mb-2">
                <label for="contact_id" class="block text-gray-700 text-sm font-bold">Cliente Associato: <span class="text-red-500">*</span></label>
                <a href="index.php?page=contacts&action=add" target="_blank" class="text-blue-600 hover:underline text-sm flex items-center">
                    <i class="fas fa-plus-circle mr-1"></i> Aggiungi Nuovo Cliente
                </a>
            </div>
            <select id="contact_id" name="contact_id" required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">
                <option value="">Seleziona un cliente</option>
                <?php foreach ($contacts_for_dropdown as $contact): ?>
                    <option value="<?php echo htmlspecialchars($contact['id']); ?>"
                            <?php echo ($contact_id == $contact['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($contact['company'] ?: $contact['first_name'] . ' ' . $contact['last_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="device_type" class="block text-gray-700 text-sm font-bold mb-2">Tipo Dispositivo:</label>
            <input type="text" id="device_type" name="device_type" value="<?php echo $device_type; ?>"
                   placeholder="Es: Saldatrice Inverter"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">

            <label for="brand" class="block text-gray-700 text-sm font-bold mb-2">Marca:</label>
            <input type="text" id="brand" name="brand" value="<?php echo $brand; ?>"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">

            <label for="model" class="block text-gray-700 text-sm font-bold mb-2">Modello:</label>
            <input type="text" id="model" name="model" value="<?php echo $model; ?>"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">

            <label for="serial_number" class="block text-gray-700 text-sm font-bold mb-2">Matricola:</label>
            <input type="text" id="serial_number" name="serial_number" value="<?php echo $serial_number; ?>"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">
            <?php if ($is_edit_mode && !empty($serial_number)): ?>
                <p class="text-sm text-gray-600 mb-4">La matricola deve essere unica per ogni riparazione.</p>
            <?php endif; ?>

            <label for="problem_description" class="block text-gray-700 text-sm font-bold mb-2">Descrizione Problema:</label>
            <textarea id="problem_description" name="problem_description" rows="4"
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4"><?php echo $problem_description; ?></textarea>

            <label for="accessories" class="block text-gray-700 text-sm font-bold mb-2">Accessori Consegnati:</label>
            <input type="text" id="accessories" name="accessories" value="<?php echo $accessories; ?>"
                   placeholder="Es: Cavi, Torcia, Valigetta"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">
        </div>

        <!-- Colonna 2: Dati Amministrativi e Stato Riparazione -->
        <div>
            <h3 class="text-xl font-semibold mb-3 text-indigo-700">Dati Amministrativi e Stato</h3>

            <label for="reception_date" class="block text-gray-700 text-sm font-bold mb-2">Data di Arrivo:</label>
            <input type="date" id="reception_date" name="reception_date" value="<?php echo $reception_date; ?>"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">

            <label for="ddt_number" class="block text-gray-700 text-sm font-bold mb-2">Numero DDT:</label>
            <input type="text" id="ddt_number" name="ddt_number" value="<?php echo $ddt_number; ?>"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">

            <label for="ddt_date" class="block text-gray-700 text-sm font-bold mb-2">Data DDT:</label>
            <input type="date" id="ddt_date" name="ddt_date" value="<?php echo $ddt_date; ?>"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">

            <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Stato Riparazione: <span class="text-red-500">*</span></label>
            <select id="status" name="status" required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">
                <?php foreach ($repair_statuses as $s): ?>
                    <option value="<?php echo htmlspecialchars($s); ?>"
                            <?php echo ($status == $s) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($s); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="technician_notes" class="block text-gray-700 text-sm font-bold mb-2">Note Tecniche:</label>
            <textarea id="technician_notes" name="technician_notes" rows="4"
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4"><?php echo $technician_notes; ?></textarea>
            
            <!-- CAMPO COSTO STIMATO - ORA DI SOLA LETTURA E CON PULSANTE PER DETTAGLIO -->
            <label for="estimated_cost_display" class="block text-gray-700 text-sm font-bold mb-2">Costo Stimato (€):</label>
            <div class="flex items-center mb-4">
                <input type="text" id="estimated_cost_display" value="<?php echo number_format((float)$estimated_cost, 2, ',', '.'); ?>"
                       class="shadow appearance-none border rounded-l w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-100 cursor-pointer"
                       readonly>
                <button type="button" id="open_estimate_modal" class="btn btn-secondary rounded-r-md px-4 py-2 flex items-center justify-center h-full">
                    <i class="fas fa-calculator mr-2"></i>Dettaglio
                </button>
            </div>
            <!-- Campo nascosto che conterrà il JSON degli interventi -->
            <input type="hidden" id="estimated_cost" name="estimated_cost" value="<?php echo htmlspecialchars($estimated_cost); ?>">
            <input type="hidden" id="repair_items_json" name="repair_items_json" value='<?php echo $initial_repair_items_json; ?>'>


            <label for="completion_date" class="block text-gray-700 text-sm font-bold mb-2">Data Termine Lavori:</label>
            <input type="date" id="completion_date" name="completion_date" value="<?php echo $completion_date; ?>"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">

            <label for="shipping_date" class="block text-gray-700 text-sm font-bold mb-2">Data di Spedizione:</label>
            <input type="date" id="shipping_date" name="shipping_date" value="<?php echo $shipping_date; ?>"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">

            <label for="tracking_code" class="block text-gray-700 text-sm font-bold mb-2">Codice di Tracciatura:</label>
            <input type="text" id="tracking_code" name="tracking_code" value="<?php echo $tracking_code; ?>"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">
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
    const estimatedCostDisplay = document.getElementById('estimated_cost_display');
    const estimatedCostHidden = document.getElementById('estimated_cost');
    const repairItemsJsonHidden = document.getElementById('repair_items_json');
    const openEstimateModalBtn = document.getElementById('open_estimate_modal');

    // Funzione per aggiornare il costo stimato visualizzato e il campo nascosto
    function updateEstimatedCost(totalCost, repairItems) {
        estimatedCostDisplay.value = totalCost.toFixed(2).replace('.', ','); // Formatta per visualizzazione italiana
        estimatedCostHidden.value = totalCost.toFixed(2); // Formatto per salvataggio nel DB (punto decimale)
        repairItemsJsonHidden.value = JSON.stringify(repairItems);
    }

    // Inizializza il costo e gli items all'apertura del form (se in modifica)
    let initialRepairItems = JSON.parse(repairItemsJsonHidden.value || '[]');
    let initialTotalCost = initialRepairItems.reduce((sum, item) => sum + parseFloat(item.item_total), 0);
    updateEstimatedCost(initialTotalCost, initialRepairItems);


    openEstimateModalBtn.addEventListener('click', function() {
        const currentRepairItems = repairItemsJsonHidden.value; // Passa gli items attuali alla nuova finestra
        const modalUrl = `index.php?page=repairs&action=select_items&initial_items=${encodeURIComponent(currentRepairItems)}`;
        
        // Apri una nuova finestra. Usiamo un nome per la finestra per gestirla più facilmente.
        const newWindow = window.open(modalUrl, 'SelectRepairItems', 'width=900,height=700,scrollbars=yes,resizable=yes');

        // Imposta una funzione di callback sulla finestra principale
        window.receiveRepairItems = function(totalCost, repairItems) {
            updateEstimatedCost(totalCost, repairItems);
            newWindow.close(); // Chiudi la finestra di selezione dopo aver ricevuto i dati
        };
    });

    // Validazione data di spedizione non può essere prima della data di completamento
    const completionDateInput = document.getElementById('completion_date');
    const shippingDateInput = document.getElementById('shipping_date');

    function validateShippingDate() {
        if (completionDateInput.value && shippingDateInput.value) {
            const completionDate = new Date(completionDateInput.value);
            const shippingDate = new Date(shippingDateInput.value);
            if (shippingDate < completionDate) {
                shippingDateInput.setCustomValidity('La data di spedizione non può essere precedente alla data termine lavori.');
            } else {
                shippingDateInput.setCustomValidity('');
            }
        } else {
            shippingDateInput.setCustomValidity('');
        }
    }

    completionDateInput.addEventListener('change', validateShippingDate);
    shippingDateInput.addEventListener('change', validateShippingDate);
    
});
</script>
