<?php
// app/views/commercial_orders/add_edit_item.php

// Le variabili $order, $item_data (se in modalità modifica), $form_title, $submit_button_text, $action_url, $products_list
// sono passate dal CommercialOrderController.

// Assicurati che $order sia definito
if (!isset($order) || $order === null) {
    echo "<p class='flash-error'>Dati ordine non disponibili per la gestione delle voci.</p>";
    return;
}

// Determina se siamo in modalità aggiunta o modifica
$is_edit_mode = isset($item_data['id']) && $item_data['id'] !== null;
$form_title = $is_edit_mode ? 'Modifica Voce Ordine #' . htmlspecialchars($order['id']) : 'Aggiungi Voce all\'Ordine #' . htmlspecialchars($order['id']);
$submit_button_text = $is_edit_mode ? 'Aggiorna Voce' : 'Aggiungi Voce';
$action_url = $is_edit_mode ? "index.php?page=commercial_orders&action=edit_item&id=" . htmlspecialchars($item_data['id']) . "&order_id=" . htmlspecialchars($order['id']) : "index.php?page=commercial_orders&action=add_item&order_id=" . htmlspecialchars($order['id']);
$cancel_url = "index.php?page=commercial_orders&action=edit&id=" . htmlspecialchars($order['id']);

// Pre-popola i valori del form in base alla modalità o ai dati POST in caso di errore di validazione
$product_id = htmlspecialchars($item_data['product_id'] ?? '');
$description = htmlspecialchars($item_data['description'] ?? '');
$ordered_quantity = htmlspecialchars($item_data['ordered_quantity'] ?? '1');
$ordered_unit_price = htmlspecialchars($item_data['ordered_unit_price'] ?? '0.00');
$actual_shipped_quantity = htmlspecialchars($item_data['actual_shipped_quantity'] ?? ($item_data['ordered_quantity'] ?? '1')); // Default a ordered_quantity
$actual_shipped_serial_number = htmlspecialchars($item_data['actual_shipped_serial_number'] ?? '');
$notes_item = htmlspecialchars($item_data['notes_item'] ?? '');

// Recupera il ruolo dell'utente corrente per i permessi
$current_user_role = $_SESSION['role'] ?? null;
$current_user_id = $_SESSION['user_id'] ?? null;

// Un Commerciale non può modificare quantità/prezzo se l'ordine è in stato avanzato
$is_commerciale = ($current_user_role === 'commerciale');
$is_locked_for_commerciale = in_array($order['status'], ['Pronto per Spedizione', 'Spedito', 'Fatturato', 'Annullato', 'Pagato']);
$is_owner_commerciale = ($is_commerciale && $order['commercial_user_id'] == $current_user_id);
$is_tecnico_or_admin = in_array($current_user_role, ['tecnico', 'admin', 'superadmin']);

// Campi che possono essere modificati dal tecnico (anche se l'ordine è in stato avanzato)
$can_edit_shipped_fields = $is_tecnico_or_admin;
$can_edit_notes_item = $is_tecnico_or_admin || ($is_commerciale && !$is_locked_for_commerciale);

?>

<h2 class="text-2xl font-semibold mb-4"><?php echo $form_title; ?></h2>

<form method="POST" action="<?php echo $action_url; ?>" class="bg-white p-6 rounded-lg shadow-md max-w-xl mx-auto">
    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['id']); ?>">
    <?php if ($is_edit_mode): ?>
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($item_data['id']); ?>">
    <?php endif; ?>

    <div class="mb-4">
        <label for="product_id" class="block text-gray-700 text-sm font-bold mb-2">Prodotto dal Catalogo:</label>
        <select id="product_id" name="product_id" class="w-full mb-3" <?php echo ($is_commerciale && $is_locked_for_commerciale) ? 'disabled' : ''; ?>>
            <option value="">Seleziona un Prodotto (o lascia vuoto per descrizione personalizzata)</option>
            <?php foreach ($products_list as $product): ?>
                <option value="<?php echo htmlspecialchars($product['id']); ?>"
                        data-description="<?php echo htmlspecialchars($product['description'] ?? ''); ?>"
                        data-price="<?php echo htmlspecialchars($product['default_price_net'] ?? '0.00'); ?>"
                        <?php echo ($product['id'] == $product_id) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($product['product_code'] . ' - ' . $product['product_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="text-xs text-gray-500">Seleziona un prodotto dal catalogo per pre-compilare la descrizione e il prezzo.</p>
    </div>

    <div class="mb-4">
        <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Descrizione Articolo: <span class="text-red-500">*</span></label>
        <input type="text" id="description" name="description" value="<?php echo $description; ?>" required class="w-full mb-3" <?php echo ($is_commerciale && $is_locked_for_commerciale) ? 'disabled' : ''; ?>>
    </div>

    <div class="mb-4">
        <label for="ordered_quantity" class="block text-gray-700 text-sm font-bold mb-2">Quantità Ordinata: <span class="text-red-500">*</span></label>
        <input type="number" step="1" min="1" id="ordered_quantity" name="ordered_quantity" value="<?php echo $ordered_quantity; ?>" required class="w-full mb-3" <?php echo ($is_commerciale && $is_locked_for_commerciale) ? 'disabled' : ''; ?>>
    </div>

    <div class="mb-4">
        <label for="ordered_unit_price" class="block text-gray-700 text-sm font-bold mb-2">Prezzo Unitario (€): <span class="text-red-500">*</span></label>
        <input type="number" step="0.01" id="ordered_unit_price" name="ordered_unit_price" value="<?php echo $ordered_unit_price; ?>" required class="w-full mb-3" <?php echo ($is_commerciale && $is_locked_for_commerciale) ? 'disabled' : ''; ?>>
    </div>

    <div class="mb-4">
        <label for="actual_shipped_quantity" class="block text-gray-700 text-sm font-bold mb-2">Quantità Effettivamente Spedita:</label>
        <input type="number" step="1" min="0" id="actual_shipped_quantity" name="actual_shipped_quantity" value="<?php echo $actual_shipped_quantity; ?>" class="w-full mb-3" <?php echo $can_edit_shipped_fields ? '' : 'disabled'; ?>>
    </div>

    <div class="mb-4">
        <label for="actual_shipped_serial_number" class="block text-gray-700 text-sm font-bold mb-2">Numero Seriale Spedito:</label>
        <input type="text" id="actual_shipped_serial_number" name="actual_shipped_serial_number" value="<?php echo $actual_shipped_serial_number; ?>" class="w-full mb-3" <?php echo $can_edit_shipped_fields ? '' : 'disabled'; ?>>
        <p class="text-xs text-gray-500">Inserire i numeri di serie separati da virgole se ci sono più unità (es. SN123, SN124, SN125).</p>
    </div>

    <div class="mb-6">
        <label for="notes_item" class="block text-gray-700 text-sm font-bold mb-2">Note Articolo:</label>
        <textarea id="notes_item" name="notes_item" rows="3" class="w-full mb-3" <?php echo $can_edit_notes_item ? '' : 'disabled'; ?>><?php echo $notes_item; ?></textarea>
    </div>

    <div class="flex items-center justify-between">
        <button type="submit" class="btn btn-primary" <?php echo ($is_commerciale && $is_locked_for_commerciale && !$is_tecnico_or_admin) ? 'disabled' : ''; ?>>
            <?php echo $submit_button_text; ?>
        </button>
        <a href="<?php echo $cancel_url; ?>" class="btn btn-secondary">Annulla</a>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const productIdSelect = document.getElementById('product_id');
        const descriptionInput = document.getElementById('description');
        const orderedUnitPriceInput = document.getElementById('ordered_unit_price');

        // Funzione per aggiornare descrizione e prezzo in base alla selezione del prodotto
        function updateItemDetails() {
            const selectedOption = productIdSelect.options[productIdSelect.selectedIndex];
            if (selectedOption && selectedOption.value) {
                // Se il campo descrizione non è disabilitato, lo aggiorniamo
                if (!descriptionInput.disabled) {
                    descriptionInput.value = selectedOption.dataset.description;
                }
                // Se il campo prezzo non è disabilitato, lo aggiorniamo
                if (!orderedUnitPriceInput.disabled) {
                    orderedUnitPriceInput.value = selectedOption.dataset.price;
                }
            }
            // Se si seleziona l'opzione vuota, resetta i campi se non sono disabilitati
            else if (!selectedOption.value && !descriptionInput.disabled && !orderedUnitPriceInput.disabled) {
                descriptionInput.value = '';
                orderedUnitPriceInput.value = '0.00';
            }
        }

        // Aggiungi l'event listener per il cambio del select
        productIdSelect.addEventListener('change', updateItemDetails);

        // Chiamata iniziale per pre-popolare i campi se la pagina è in modalità modifica
        // e un prodotto è già selezionato.
        if (productIdSelect.value) {
            // Solo se non è in modalità di blocco per il commerciale e non è un tecnico/admin che non ha il campo disabilitato
            <?php if (!($is_commerciale && $is_locked_for_commerciale) || $is_tecnico_or_admin): ?>
                updateItemDetails(); 
            <?php endif; ?>
        }
    });
</script>
