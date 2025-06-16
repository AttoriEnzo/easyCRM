<?php
// app/views/repairs/select_repair_items.php
// Questa vista è una finestra stand-alone per la selezione degli interventi di riparazione.

// Le variabili $service_items (catalogo interventi preimpostati) e $initial_items_json
// (interventi già selezionati, se in modifica) vengono passate dal RepairController.

// Decodifica gli interventi iniziali già selezionati (se presenti)
$initial_items_data = json_decode($initial_items_json, true);
if (!is_array($initial_items_data)) {
    $initial_items_data = [];
}
// Codifica gli interventi di servizio disponibili per JavaScript
$available_service_items_json = json_encode($service_items);

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleziona Interventi Riparazione</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome CDN per le icone -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; margin: 0; padding: 1.5rem; display: flex; flex-direction: column; min-height: 100vh; }
        .container-modal { max-width: 900px; margin: 0 auto; padding: 1.5rem; flex-grow: 1; background-color: white; border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
        input[type="text"], input[type="number"], select {
            width: 100%; padding: 0.75rem; margin-bottom: 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); transition: all 0.2s ease-in-out;
        }
        input[type="text"]:focus, input[type="number"]:focus, select:focus {
            outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.5);
        }
        .btn { padding: 0.75rem 1.5rem; border-radius: 0.5rem; font-weight: 600; transition: all 0.2s ease-in-out; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; }
        .btn-primary { background-color: #4f46e5; color: #ffffff; }
        .btn-primary:hover { background-color: #4338ca; }
        .btn-secondary { background-color: #6b7280; color: #ffffff; }
        .btn-secondary:hover { background-color: #4b5563; }
        .btn-red { background-color: #ef4444; color: white; }
        .btn-red:hover { background-color: #dc2626; }
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 0.75rem; border: 1px solid #e5e7eb; text-align: left; font-size: 0.875rem; }
        th { background-color: #f9fafb; font-weight: 600; color: #374151; text-transform: uppercase; }
        tbody tr:nth-child(even) { background-color: #f3f4f6; }
        tbody tr:hover { background-color: #e5e7eb; }
    </style>
</head>
<body>
    <div class="container-modal">
        <h2 class="text-2xl font-semibold mb-6 text-center">Seleziona Interventi per Riparazione</h2>

        <!-- Sezione Aggiungi Intervento Preimpostato -->
        <div class="bg-gray-50 p-4 rounded-lg shadow-sm mb-6 border border-gray-200">
            <h3 class="text-xl font-semibold mb-4 text-indigo-700">Aggiungi Intervento Preimpostato</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="predefined_item_select" class="block text-gray-700 text-sm font-bold mb-2">Seleziona Intervento:</label>
                    <select id="predefined_item_select" class="block w-full">
                        <option value="">Cerca o Seleziona un intervento</option>
                        <?php foreach ($service_items as $item): ?>
                            <option 
                                value="<?php echo htmlspecialchars($item['id']); ?>" 
                                data-cost="<?php echo htmlspecialchars($item['default_cost']); ?>"
                                data-description="<?php echo htmlspecialchars($item['name']); ?>">
                                <?php echo htmlspecialchars($item['name'] . ' (€ ' . number_format($item['default_cost'], 2, ',', '.') . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="predefined_item_description_override" class="block text-gray-700 text-sm font-bold mb-2">Descrizione Specifica (opzionale):</label>
                    <input type="text" id="predefined_item_description_override" placeholder="Aggiungi dettagli extra se necessario">
                </div>
            </div>
            <button type="button" id="add_predefined_item_btn" class="btn btn-primary mt-4 flex items-center justify-center">
                <i class="fas fa-plus-circle mr-2"></i>Aggiungi Intervento Preimpostato
            </button>
        </div>

        <!-- Sezione Aggiungi Intervento Personalizzato -->
        <div class="bg-gray-50 p-4 rounded-lg shadow-sm mb-6 border border-gray-200">
            <h3 class="text-xl font-semibold mb-4 text-indigo-700">Aggiungi Intervento Personalizzato</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="custom_item_description" class="block text-gray-700 text-sm font-bold mb-2">Descrizione Personalizzata: <span class="text-red-500">*</span></label>
                    <input type="text" id="custom_item_description" placeholder="Descrizione del lavoro o del ricambio">
                </div>
                <div>
                    <label for="custom_item_cost" class="block text-gray-700 text-sm font-bold mb-2">Costo (€): <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" id="custom_item_cost" placeholder="0.00">
                </div>
            </div>
            <button type="button" id="add_custom_item_btn" class="btn btn-primary mt-4 flex items-center justify-center">
                <i class="fas fa-plus-circle mr-2"></i>Aggiungi Intervento Personalizzato
            </button>
        </div>

        <!-- Lista degli Interventi Selezionati -->
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
            <h3 class="text-xl font-semibold mb-4 text-indigo-700">Riepilogo Interventi Selezionati</h3>
            <div class="table-responsive">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th>Descrizione</th>
                            <th>Costo Unitario</th>
                            <th>Totale</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody id="selected_items_table_body">
                        <!-- Le righe verranno aggiunte qui tramite JavaScript -->
                    </tbody>
                </table>
            </div>
            <div class="text-right text-lg font-bold mt-4">
                Costo Totale Stimato: <span id="total_estimated_cost_display">€ 0,00</span>
            </div>
        </div>

        <!-- Pulsanti di Azione -->
        <div class="flex justify-end space-x-4 mt-6">
            <button type="button" id="confirm_selection_btn" class="btn btn-primary flex items-center justify-center">
                <i class="fas fa-check-circle mr-2"></i>Conferma Selezione
            </button>
            <button type="button" id="cancel_selection_btn" class="btn btn-secondary flex items-center justify-center">
                <i class="fas fa-times-circle mr-2"></i>Annulla
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elementi del DOM
            const predefinedItemSelect = document.getElementById('predefined_item_select');
            const predefinedItemDescriptionOverride = document.getElementById('predefined_item_description_override');
            const addPredefinedItemBtn = document.getElementById('add_predefined_item_btn');
            const customItemDescriptionInput = document.getElementById('custom_item_description');
            const customItemCostInput = document.getElementById('custom_item_cost');
            const addCustomItemBtn = document.getElementById('add_custom_item_btn');
            const selectedItemsTableBody = document.getElementById('selected_items_table_body');
            const totalEstimatedCostDisplay = document.getElementById('total_estimated_cost_display');
            const confirmSelectionBtn = document.getElementById('confirm_selection_btn');
            const cancelSelectionBtn = document.getElementById('cancel_selection_btn');

            let selectedRepairItems = []; // Array che conterrà gli oggetti degli interventi selezionati

            // Carica gli interventi iniziali se presenti (in modalità modifica)
            const initialItemsJson = `<?php echo $initial_items_json; ?>`;
            try {
                const parsedInitialItems = JSON.parse(initialItemsJson);
                if (Array.isArray(parsedInitialItems)) {
                    selectedRepairItems = parsedInitialItems;
                }
            } catch (e) {
                console.error("Errore nel parsing degli interventi iniziali:", e);
                selectedRepairItems = [];
            }

            // Mappa degli interventi di servizio disponibili (per lookup rapido)
            const availableServiceItems = JSON.parse(`<?php echo $available_service_items_json; ?>`);
            const serviceItemsMap = availableServiceItems.reduce((map, item) => {
                map[item.id] = item;
                return map;
            }, {});


            // Funzione per aggiornare la tabella e il totale
            function updateSelectedItemsTableAndTotal() {
                selectedItemsTableBody.innerHTML = ''; // Pulisci la tabella
                let totalCost = 0;

                selectedRepairItems.forEach((item, index) => {
                    const row = selectedItemsTableBody.insertRow();
                    row.dataset.index = index; // Aggiungi un data attribute per l'indice
                    
                    const descriptionCell = row.insertCell();
                    descriptionCell.textContent = item.custom_description;

                    const unitCostCell = row.insertCell();
                    unitCostCell.textContent = `€ ${parseFloat(item.unit_cost).toFixed(2).replace('.', ',')}`;
                    
                    const totalCell = row.insertCell();
                    totalCell.textContent = `€ ${parseFloat(item.item_total).toFixed(2).replace('.', ',')}`;

                    const actionsCell = row.insertCell();
                    const removeBtn = document.createElement('button');
                    removeBtn.classList.add('btn', 'btn-red', 'btn-sm');
                    removeBtn.innerHTML = '<i class="fas fa-trash-alt"></i> Rimuovi';
                    removeBtn.addEventListener('click', function() {
                        // Rimuovi l'elemento dall'array selectedRepairItems usando il suo indice originale
                        selectedRepairItems.splice(index, 1);
                        updateSelectedItemsTableAndTotal(); // Aggiorna la tabella e il totale
                    });
                    actionsCell.appendChild(removeBtn);

                    totalCost += parseFloat(item.item_total);
                });

                totalEstimatedCostDisplay.textContent = `€ ${totalCost.toFixed(2).replace('.', ',')}`;
            }

            // Funzione per l'aggiunta di un intervento preimpostato
            addPredefinedItemBtn.addEventListener('click', function() {
                const selectedOption = predefinedItemSelect.options[predefinedItemSelect.selectedIndex];
                const serviceItemId = selectedOption.value;
                
                if (!serviceItemId) {
                    alert('Seleziona un intervento preimpostato.');
                    return;
                }

                const item = serviceItemsMap[serviceItemId]; // Recupera l'oggetto completo
                if (!item) {
                     alert('Intervento preimpostato non trovato.');
                     return;
                }

                const descriptionOverride = predefinedItemDescriptionOverride.value.trim();
                
                const newRepairItem = {
                    service_item_id: item.id,
                    custom_description: descriptionOverride || item.name, // Usa override o nome di default
                    unit_cost: parseFloat(item.default_cost),
                    quantity: 1, // Quantità sempre 1
                    item_total: parseFloat(item.default_cost) * 1 // Total = cost * quantity
                };

                selectedRepairItems.push(newRepairItem);
                updateSelectedItemsTableAndTotal();
                predefinedItemSelect.value = ''; // Resetta la selezione
                predefinedItemDescriptionOverride.value = ''; // Pulisci l'override
            });

            // Funzione per l'aggiunta di un intervento personalizzato
            addCustomItemBtn.addEventListener('click', function() {
                const description = customItemDescriptionInput.value.trim();
                const cost = parseFloat(customItemCostInput.value);

                if (!description) {
                    alert('La descrizione personalizzata è obbligatoria.');
                    return;
                }
                if (isNaN(cost) || cost < 0) {
                    alert('Inserisci un costo valido e positivo per l\'intervento personalizzato.');
                    return;
                }

                const newRepairItem = {
                    service_item_id: null, // È un intervento personalizzato
                    custom_description: description,
                    unit_cost: cost,
                    quantity: 1, // Quantità sempre 1
                    item_total: cost * 1
                };

                selectedRepairItems.push(newRepairItem);
                updateSelectedItemsTableAndTotal();
                customItemDescriptionInput.value = ''; // Pulisci i campi
                customItemCostInput.value = '';
            });

            // Pulsante "Conferma Selezione" - Invia i dati alla finestra principale
            confirmSelectionBtn.addEventListener('click', function() {
                const totalCost = selectedRepairItems.reduce((sum, item) => sum + parseFloat(item.item_total), 0);
                // Invia i dati alla funzione di callback della finestra padre
                if (window.opener && window.opener.receiveRepairItems) {
                    window.opener.receiveRepairItems(totalCost, selectedRepairItems);
                }
                window.close(); // Chiudi la finestra
            });

            // Pulsante "Annulla" - Chiude la finestra senza salvare
            cancelSelectionBtn.addEventListener('click', function() {
                window.close();
            });

            // Inizializza la tabella e il totale al caricamento della pagina
            updateSelectedItemsTableAndTotal();
        });
    </script>
</body>
</html>
