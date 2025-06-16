<?php
// app/views/products/add_edit_supplier_info.php

// Le variabili $product, $supplier_info_data (se in modalità modifica),
// $form_title, $submit_button_text, $action_url
// sono passate dal ProductsController->addSupplierInfo() o ProductsController->editSupplierInfo()

// Assicurati che $product sia definito (dovrebbe sempre esserlo)
if (!isset($product) || $product === null) {
    echo "<p class='flash-error'>Dati prodotto non disponibili per la gestione delle informazioni fornitore.</p>";
    return;
}

// Determina se siamo in modalità aggiunta o modifica
$is_edit_mode = isset($supplier_info_data['id']) && $supplier_info_data['id'] !== null;
$form_title = $is_edit_mode ? 'Modifica Info Fornitore per ' . htmlspecialchars($product['product_name'] . ' (' . $product['product_code'] . ')') : 'Aggiungi Info Fornitore per ' . htmlspecialchars($product['product_name'] . ' (' . $product['product_code'] . ')');
$submit_button_text = $is_edit_mode ? 'Aggiorna Informazione' : 'Salva Informazione';
$action_url = $is_edit_mode ? "index.php?page=products_catalog&action=edit_supplier_info&id=" . htmlspecialchars($supplier_info_data['id']) . "&product_id=" . htmlspecialchars($product['id']) : "index.php?page=products_catalog&action=add_supplier_info&product_id=" . htmlspecialchars($product['id']);
$cancel_url = "index.php?page=products_catalog&action=edit&id=" . htmlspecialchars($product['id']);

// Pre-popola i valori del form in base alla modalità
$supplier_info_id = $is_edit_mode ? htmlspecialchars($supplier_info_data['id']) : '';
$supplier_name = htmlspecialchars($supplier_info_data['supplier_name'] ?? '');
$supplier_product_code = htmlspecialchars($supplier_info_data['supplier_product_code'] ?? '');
$purchase_price = htmlspecialchars($supplier_info_data['purchase_price'] ?? '0.00');
$purchase_date = htmlspecialchars($supplier_info_data['purchase_date'] ?? date('Y-m-d')); // Default alla data odierna
?>

<h2 class="text-2xl font-semibold mb-4"><?php echo $form_title; ?></h2>

<form method="POST" action="<?php echo $action_url; ?>" class="bg-white p-6 rounded-lg shadow-md max-w-lg mx-auto">
    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
    <?php if ($is_edit_mode): ?>
        <input type="hidden" name="id" value="<?php echo $supplier_info_id; ?>">
    <?php endif; ?>

    <div class="mb-4">
        <label for="supplier_name" class="block text-gray-700 text-sm font-bold mb-2">Nome Fornitore: <span class="text-red-500">*</span></label>
        <input type="text" id="supplier_name" name="supplier_name" value="<?php echo $supplier_name; ?>" required class="w-full mb-3">
    </div>

    <div class="mb-4">
        <label for="supplier_product_code" class="block text-gray-700 text-sm font-bold mb-2">Codice Prodotto Fornitore:</label>
        <input type="text" id="supplier_product_code" name="supplier_product_code" value="<?php echo $supplier_product_code; ?>" class="w-full mb-3">
    </div>

    <div class="mb-4">
        <label for="purchase_price" class="block text-gray-700 text-sm font-bold mb-2">Prezzo di Acquisto: <span class="text-red-500">*</span></label>
        <input type="number" step="0.01" id="purchase_price" name="purchase_price" value="<?php echo $purchase_price; ?>" required class="w-full mb-3">
    </div>

    <div class="mb-6">
        <label for="purchase_date" class="block text-gray-700 text-sm font-bold mb-2">Data di Acquisto:</label>
        <input type="date" id="purchase_date" name="purchase_date" value="<?php echo $purchase_date; ?>" class="w-full mb-3">
    </div>

    <div class="flex items-center justify-between">
        <button type="submit" class="btn btn-primary">
            <?php echo $submit_button_text; ?>
        </button>
        <a href="<?php echo $cancel_url; ?>" class="btn btn-secondary">Annulla</a>
    </div>
</form>
