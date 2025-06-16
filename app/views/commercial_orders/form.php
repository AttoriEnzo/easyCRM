<?php
// app/views/commercial_orders/form.php

// Le variabili $order (se in modalità modifica), $form_title, $submit_button_text, $action_url, $cancel_url
// e $contacts_for_dropdown, $active_products sono passate dal CommercialOrderController.

// Determina se siamo in modalità aggiunta o modifica
$is_edit_mode = isset($order['id']) && $order['id'] !== null;
$form_title = $is_edit_mode ? 'Modifica Ordine Commerciale' : 'Crea Nuovo Ordine Commerciale';
$submit_button_text = $is_edit_mode ? 'Aggiorna Ordine' : 'Salva Ordine';
$action_url = $is_edit_mode ? "index.php?page=commercial_orders&action=edit&id=" . htmlspecialchars($order['id']) : "index.php?page=commercial_orders&action=add";
$cancel_url = "index.php?page=commercial_orders";

// Pre-popola i valori del form in base alla modalità
$contact_id = htmlspecialchars($order['contact_id'] ?? '');
$order_date = htmlspecialchars($order['order_date'] ?? date('Y-m-d'));
$status = htmlspecialchars($order['status'] ?? 'Ordine Inserito');
$expected_shipping_date = htmlspecialchars($order['expected_shipping_date'] ?? '');
$shipping_address = htmlspecialchars($order['shipping_address'] ?? ''); // Questi campi saranno popolati da JS in futuro
$shipping_city = htmlspecialchars($order['shipping_city'] ?? '');
$shipping_zip = htmlspecialchars($order['shipping_zip'] ?? '');
$shipping_province = htmlspecialchars($order['shipping_province'] ?? '');
$carrier = htmlspecialchars($order['carrier'] ?? '');
$shipping_costs = htmlspecialchars($order['shipping_costs'] ?? '0.00');
$notes_commercial = htmlspecialchars($order['notes_commercial'] ?? '');
$notes_technical = htmlspecialchars($order['notes_technical'] ?? '');
$total_amount = htmlspecialchars($order['total_amount'] ?? '0.00');

// Array degli stati dell'ordine per il dropdown
$order_statuses = ['Ordine Inserito', 'In Preparazione', 'Pronto per Spedizione', 'Spedito', 'Fatturato', 'Annullato', 'In Attesa di Pagamento', 'Pagato'];

// Recupera gli articoli dell'ordine esistenti, se in modalità modifica
// Questi sono già decodificati nel controller e passati come $order['order_items_data']
$initial_order_items = $order['order_items_data'] ?? [];

// Codifica i prodotti attivi per JavaScript (per la logica di selezione articoli)
$active_products_json = json_encode($active_products);

// Recupera il ruolo dell'utente corrente dalla sessione per i controlli di visibilità/modificabilità
$current_user_role = $_SESSION['role'] ?? null;
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
        <!-- Colonna 1: Dati Principali Ordine -->
        <div>
            <h3 class="text-xl font-semibold mb-3 text-indigo-700">Dettagli Ordine</h3>

            <label for="contact_id" class="block text-gray-700 text-sm font-bold mb-2">Cliente Associato: <span class="text-red-500">*</span></label>
            <select id="contact_id" name="contact_id" required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4"
                    <?php echo ($is_edit_mode && ($current_user_role === 'tecnico' || $current_user_role === 'commerciale')) ? 'disabled' : ''; ?>>
                <option value="">Seleziona un cliente</option>
                <?php foreach ($contacts_for_dropdown as $contact): ?>
                    <option value="<?php echo htmlspecialchars($contact['id']); ?>"
                            <?php echo ($contact_id == $contact['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($contact['company'] ?: $contact['first_name'] . ' ' . $contact['last_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($is_edit_mode && ($current_user_role === 'tecnico' || $current_user_role === 'commerciale')): ?>
                <input type="hidden" name="contact_id" value="<?php echo $contact_id; ?>">
                <p class="text-sm text-gray-600 -mt-3 mb-4">Il cliente non può essere modificato dopo la creazione.</p>
            <?php endif; ?>

            <label for="order_date" class="block text-gray-700 text-sm font-bold mb-2">Data Ordine: <span class="text-red-500">*</span></label>
            <input type="date" id="order_date" name="order_date" value="<?php echo $order_date; ?>" required
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4"
                   <?php echo ($current_user_role === 'tecnico') ? 'disabled' : ''; ?>>
            <?php if ($current_user_role === 'tecnico'): ?>
                <input type="hidden" name="order_date" value="<?php echo $order_date; ?>">
            <?php endif; ?>

            <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Stato Ordine: <span class="text-red-500">*</span></label>
            <select id="status" name="status" required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">
                <?php foreach ($order_statuses as $s): ?>
                    <option value="<?php echo htmlspecialchars($s); ?>"
                            <?php echo ($status == $s) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($s); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="expected_shipping_date" class="block text-gray-700 text-sm font-bold mb-2">Data Spedizione Prevista:</label>
            <input type="date" id="expected_shipping_date" name="expected_shipping_date" value="<?php echo $expected_shipping_date; ?>"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">
            
            <!-- Campi indirizzo di spedizione - DA IMPLEMENTARE CON INDIRIZZI MULTIPLI CLIENTE -->
            <h4 class="text-lg font-semibold mb-2 text-gray-700">Indirizzo di Spedizione <span class="text-sm font-normal text-gray-500">(Attualmente input manuale, in futuro da selezione indirizzi contatto)</span></h4>
            <label for="shipping_address" class="block text-gray-700 text-sm font-bold mb-2">Indirizzo:</label>
            <input type="text" id="shipping_address" name="shipping_address" value="<?php echo $shipping_address; ?>"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">

            <label for="shipping_city" class="block text-gray-700 text-sm font-bold mb-2">Città:</label>
            <input type="text" id="shipping_city" name="shipping_city" value="<?php echo $shipping_city; ?>"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">

            <label for="shipping_zip" class="block text-gray-700 text-sm font-bold mb-2">CAP:</label>
            <input type="text" id="shipping_zip" name="shipping_zip" value="<?php echo $shipping_zip; ?>"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">

            <label for="shipping_province" class="block text-gray-700 text-sm font-bold mb-2">Provincia:</label>
            <input type="text" id="shipping_province" name="shipping_province" value="<?php echo $shipping_province; ?>"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">

            <label for="carrier" class="block text-gray-700 text-sm font-bold mb-2">Vettore:</label>
            <input type="text" id="carrier" name="carrier" value="<?php echo $carrier; ?>"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">

            <label for="shipping_costs" class="block text-gray-700 text-sm font-bold mb-2">Costi Spedizione (€):</label>
            <input type="number" step="0.01" id="shipping_costs" name="shipping_costs" value="<?php echo $shipping_costs; ?>"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4"
                   <?php echo ($current_user_role === 'tecnico') ? 'disabled' : ''; ?>>
            <?php if ($current_user_role === 'tecnico'): ?>
                <input type="hidden" name="shipping_costs" value="<?php echo $shipping_costs; ?>">
            <?php endif; ?>

        </div>

        <!-- Colonna 2: Note e Riepilogo Costi (e in futuro item dell'ordine) -->
        <div>
            <h3 class="text-xl font-semibold mb-3 text-indigo-700">Note</h3>

            <label for="notes_commercial" class="block text-gray-700 text-sm font-bold mb-2">Note Commerciali:</label>
            <textarea id="notes_commercial" name="notes_commercial" rows="4"
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4"
                      <?php echo ($current_user_role === 'tecnico') ? 'disabled' : ''; ?>><?php echo $notes_commercial; ?></textarea>
            <?php if ($current_user_role === 'tecnico'): ?>
                <input type="hidden" name="notes_commercial" value="<?php echo $notes_commercial; ?>">
            <?php endif; ?>

            <label for="notes_technical" class="block text-gray-700 text-sm font-bold mb-2">Note Tecniche:</label>
            <textarea id="notes_technical" name="notes_technical" rows="4"
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4"
                      <?php echo ($current_user_role === 'commerciale') ? 'disabled' : ''; ?>><?php echo $notes_technical; ?></textarea>
            <?php if ($current_user_role === 'commerciale'): ?>
                <input type="hidden" name="notes_technical" value="<?php echo $notes_technical; ?>">
            <?php endif; ?>
        </div>
    </div>

    <!-- Sezione Dettaglio Articoli Ordine -->
    <h3 class="text-xl font-semibold mt-6 mb-4 border-b pb-2 text-indigo-700">Articoli dell'Ordine</h3>
    <div class="bg-gray-50 p-4 rounded-lg shadow-inner mb-4">
        <button type="button" id="add_item_btn" class="btn btn-primary flex items-center justify-center">
            <i class="fas fa-plus-circle mr-2"></i>Aggiungi Articolo
        </button>
        <p class="text-sm text-gray-600 mt-2">
            Aggiungi prodotti dal catalogo o inserisci descrizioni personalizzate.
            <?php if ($current_user_role === 'tecnico'): ?>
            <br><span class="text-red-600">Attenzione: come tecnico, non puoi aggiungere o rimuovere articoli, né modificare prezzi o quantità ordinate. Puoi solo modificare le quantità spedite, i numeri seriali e le note per articolo.</span>
            <?php endif; ?>
        </p>
    </div>

    <div class="overflow-x-auto bg-white p-4 rounded-lg shadow-sm">
        <table class="min-w-full">
            <thead>
                <tr>
                    <th class="text-sm w-1/4">Prodotto/Descrizione</th>
                    <th class="text-sm w-1/12">Q. Ord.</th>
                    <th class="text-sm w-1/12">Prezzo Unit. (€)</th>
                    <th class="text-sm w-1/12">Totale Art. (€)</th>
                    <th class="text-sm w-1/12">Q. Spedita</th>
                    <th class="text-sm w-1/4">Matricole Spedite</th>
                    <th class="text-sm w-1/6">Note Articolo</th>
                    <th class="text-sm w-1/12">Azioni</th>
                </tr>
            </thead>
            <tbody id="order_items_container">
                <!-- Le righe degli articoli verranno aggiunte qui tramite JavaScript -->
            </tbody>
        </table>
    </div>

    <div class="text-right text-2xl font-bold mt-6 text-green-700">
        Totale Ordine: <span id="total_amount_display">€ <?php echo number_format((float)$total_amount, 2, ',', '.'); ?></span>
    </div>

    <!-- Campi nascosti per inviare i dati processati dal JS -->
    <input type="hidden" id="order_items_json" name="order_items_json">
    <input type="hidden" id="total_amount_hidden" name="total_amount" value="<?php echo htmlspecialchars($total_amount); ?>">

    <div class="flex items-center justify-between mt-6">
        <button type="submit" class="btn btn-primary">
            <?php echo $submit_button_text; ?>
        </button>
        <a href="<?php echo $cancel_url; ?>" class="btn btn-secondary">Annulla</a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const orderItemsContainer = document.getElementById('order_items_container');
    const addOrderItemsBtn = document.getElementById('add_item_btn');
    const totalAmountDisplay = document.getElementById('total_amount_display');
    const totalAmountHidden = document.getElementById('total_amount_hidden');
    const orderItemsJsonHidden = document.getElementById('order_items_json');

    // Dati dei prodotti attivi disponibili
    const activeProducts = <?php echo $active_products_json; ?>;
    const productsMap = activeProducts.reduce((map, product) => {
        map[product.id] = product;
        return map;
    }, {});

    // Dati iniziali degli articoli dell'ordine (per la modalità modifica)
    let orderItems = <?php echo json_encode($initial_order_items); ?>;

    // Ruolo utente corrente dal PHP
    const currentUserRole = "<?php echo $current_user_role; ?>";
    const isTechnician = (currentUserRole === 'tecnico');
    const isCommerciale = (currentUserRole === 'commerciale');
    const isAdminOrSuperAdmin = (currentUserRole === 'admin' || currentUserRole === 'superadmin');

    // Funzione per calcolare e aggiornare il totale dell'ordine
    function updateOrderTotal() {
        let currentTotal = 0;
        orderItems.forEach(item => {
            currentTotal += parseFloat(item.ordered_item_total || 0);
        });
        totalAmountDisplay.textContent = `€ ${currentTotal.toFixed(2).replace('.', ',')}`;
        totalAmountHidden.value = currentTotal.toFixed(2);
        orderItemsJsonHidden.value = JSON.stringify(orderItems); // Aggiorna il campo nascosto JSON
    }

    // Funzione per creare una nuova riga articolo
    function createOrderItemRow(itemData = {}) {
        const index = orderItems.length; // Usa la lunghezza attuale come indice
        // Genera un ID temporaneo per gli elementi della riga per collegare eventi
        const rowId = `item-row-${Date.now()}-${Math.floor(Math.random() * 1000)}`;

        const newRow = orderItemsContainer.insertRow();
        newRow.id = rowId; // Assegna l'ID alla riga

        newRow.innerHTML = `
            <td>
                <select id="product_select_${rowId}" class="w-full text-xs product-select" ${isTechnician ? 'disabled' : ''}>
                    <option value="">Personalizzato</option>
                    <?php foreach ($active_products as $product): ?>
                        <option value="<?php echo htmlspecialchars($product['id']); ?>"
                                data-price-net="<?php echo htmlspecialchars($product['default_price_net']); ?>"
                                data-price-gross="<?php echo htmlspecialchars($product['default_price_gross']); ?>"
                                <?php echo (isset($product['product_type']) ? 'data-product-type="' . htmlspecialchars($product['product_type']) . '"' : ''); ?>
                                <?php echo (isset($product['product_name']) ? 'data-product-name="' . htmlspecialchars($product['product_name']) . '"' : ''); ?>>
                            <?php echo htmlspecialchars($product['product_name'] . ' (' . $product['product_type'] . ') - € ' . number_format($product['default_price_net'], 2, ',', '.')); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="text" id="description_${rowId}" placeholder="Descrizione Articolo" class="w-full text-xs mt-1" value="${itemData.description || ''}" ${isTechnician ? 'disabled' : ''}>
            </td>
            <td><input type="number" step="1" min="1" id="ordered_quantity_${rowId}" class="w-full text-xs numeric-input" value="${itemData.ordered_quantity || 1}" ${isTechnician ? 'disabled' : ''}></td>
            <td><input type="number" step="0.01" min="0" id="ordered_unit_price_${rowId}" class="w-full text-xs numeric-input" value="${(itemData.ordered_unit_price || 0).toFixed(2)}" ${isTechnician ? 'disabled' : ''}></td>
            <td><input type="text" id="item_total_${rowId}" class="w-full text-xs bg-gray-100" value="${(itemData.ordered_item_total || 0).toFixed(2)}" readonly></td>
            <td><input type="number" step="1" min="0" id="actual_shipped_quantity_${rowId}" class="w-full text-xs numeric-input" value="${itemData.actual_shipped_quantity || 0}" ${isCommerciale ? 'disabled' : ''}></td>
            <td><textarea id="actual_shipped_serial_number_${rowId}" rows="2" placeholder="Matricole (una per riga o separate da virgola)" class="w-full text-xs" ${isCommerciale ? 'disabled' : ''}>${itemData.actual_shipped_serial_number || ''}</textarea></td>
            <td><textarea id="notes_item_${rowId}" rows="2" placeholder="Note per articolo" class="w-full text-xs" ${isCommerciale ? 'disabled' : ''}>${itemData.notes_item || ''}</textarea></td>
            <td>
                <button type="button" class="btn btn-red btn-sm remove-item-btn" ${isTechnician || isCommerciale ? 'disabled' : ''}>
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        `;

        // Collega i gestori di eventi
        attachItemEventListeners(newRow, index, itemData.id); // Passa l'ID dell'item se esiste
        
        // Se c'è un product_id nell'itemData, selezionalo nel dropdown
        if (itemData.product_id) {
            const productSelect = newRow.querySelector(`#product_select_${rowId}`);
            productSelect.value = itemData.product_id;
        }
    }

    // Funzione per collegare gli event listener a una riga specifica
    function attachItemEventListeners(row, index, itemId = null) {
        const productSelect = row.querySelector('.product-select');
        const descriptionInput = row.querySelector(`#description_${row.id}`);
        const orderedQuantityInput = row.querySelector(`#ordered_quantity_${row.id}`);
        const orderedUnitPriceInput = row.querySelector(`#ordered_unit_price_${row.id}`);
        const itemTotalInput = row.querySelector(`#item_total_${row.id}`);
        const actualShippedQuantityInput = row.querySelector(`#actual_shipped_quantity_${row.id}`);
        const actualShippedSerialNumberInput = row.querySelector(`#actual_shipped_serial_number_${row.id}`);
        const notesItemInput = row.querySelector(`#notes_item_${row.id}`);
        const removeBtn = row.querySelector('.remove-item-btn');

        // Aggiorna l'oggetto nell'array `orderItems` quando i campi cambiano
        function updateItemObject() {
            // Assicurati che l'indice esista o sia corretto
            if (!orderItems[index]) {
                orderItems[index] = {}; // Inizializza l'oggetto se non esiste (dovrebbe esistere se la riga è stata creata da createOrderItemRow)
            }
            orderItems[index].id = itemId; // Mantiene l'ID del database se in modifica
            orderItems[index].product_id = productSelect.value ? parseInt(productSelect.value) : null;
            orderItems[index].description = descriptionInput.value;
            orderItems[index].ordered_quantity = parseInt(orderedQuantityInput.value || 0);
            orderItems[index].ordered_unit_price = parseFloat(orderedUnitPriceInput.value || 0);
            
            // Calcola il totale dell'articolo
            const itemTotal = orderItems[index].ordered_quantity * orderItems[index].ordered_unit_price;
            orderItems[index].ordered_item_total = parseFloat(itemTotal.toFixed(2));
            itemTotalInput.value = orderItems[index].ordered_item_total.toFixed(2); // Aggiorna il campo totale visibile

            orderItems[index].actual_shipped_quantity = parseInt(actualShippedQuantityInput.value || 0);
            orderItems[index].actual_shipped_serial_number = actualShippedSerialNumberInput.value;
            orderItems[index].notes_item = notesItemInput.value;

            updateOrderTotal(); // Ricalcola il totale generale
        }

        // Event listener per il cambio prodotto (pre-fill fields)
        productSelect.addEventListener('change', function() {
            const selectedProductId = this.value;
            if (selectedProductId) {
                const product = productsMap[selectedProductId];
                if (product) {
                    descriptionInput.value = product.product_name + ' (' + product.product_type + ')';
                    // Qui potresti aggiungere la logica per default_price_net/gross basata sul tipo cliente
                    // Per ora, useremo il prezzo netto come default.
                    orderedUnitPriceInput.value = parseFloat(product.default_price_net).toFixed(2);
                }
            } else {
                descriptionInput.value = ''; // Pulisce se si seleziona "Personalizzato"
                orderedUnitPriceInput.value = '0.00';
            }
            updateItemObject();
        });

        // Event listeners per l'input e il cambio dei campi quantità e prezzo unitario
        orderedQuantityInput.addEventListener('input', updateItemObject);
        orderedUnitPriceInput.addEventListener('input', updateItemObject);
        descriptionInput.addEventListener('input', updateItemObject);
        actualShippedQuantityInput.addEventListener('input', updateItemObject);
        actualShippedSerialNumberInput.addEventListener('input', updateItemObject);
        notesItemInput.addEventListener('input', updateItemObject);

        // Event listener per il pulsante di rimozione
        removeBtn.addEventListener('click', function() {
            // Rimuovi l'elemento dall'array `orderItems` usando il suo indice
            orderItems.splice(index, 1);
            newRow.remove(); // Rimuovi la riga dal DOM
            
            // Re-indicizza gli elementi rimanenti e ri-attacca i listener (necessario dopo splice)
            orderItemsContainer.querySelectorAll('tr').forEach((row, newIdx) => {
                const oldRowId = row.id;
                row.id = `item-row-${Date.now()}-${newIdx}`; // Nuovi ID per evitare conflitti se si aggiungono altri elementi
                // Rimuovi vecchi listener e attacca nuovi
                const clone = row.cloneNode(true);
                row.parentNode.replaceChild(clone, row);
                attachItemEventListeners(clone, newIdx, orderItems[newIdx] ? orderItems[newIdx].id : null);
            });

            updateOrderTotal(); // Ricalcola il totale generale
        });

        // Inizializza l'itemObject con i dati passati
        updateItemObject();
    }

    // Aggiungi event listener al pulsante "Aggiungi Articolo"
    addOrderItemsBtn.addEventListener('click', function() {
        // Impedisci l'aggiunta di articoli se il ruolo è tecnico o commerciale
        if (isTechnician || isCommerciale) {
            alert("Non hai i permessi per aggiungere nuovi articoli all'ordine.");
            return;
        }
        createOrderItemRow({}); // Crea una riga vuota per un nuovo articolo
    });

    // Inizializzazione: Aggiungi le righe degli articoli esistenti (per la modalità modifica)
    // Creiamo una copia per non modificare l'array originale durante l'iterazione
    [...orderItems].forEach((item, index) => {
        createOrderItemRow(item);
    });

    // Aggiorna il totale iniziale all'apertura della pagina
    updateOrderTotal();

    // Gestione disabilitazione pulsante Aggiungi per Tecnici/Commerciali
    if (isTechnician || isCommerciale) {
        addOrderItemsBtn.disabled = true;
    }
});
</script>
