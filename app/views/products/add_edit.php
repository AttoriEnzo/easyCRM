<?php
// app/views/products/add_edit.php

// Le variabili $product_data (se in modalità modifica), $form_title, $submit_button_text, $action_url
// sono passate dal ProductsController->add() o ProductsController->edit()

// In modalità modifica, $supplier_info_list sarà presente
$supplier_info_list = $supplier_info_list ?? [];

// Determina se siamo in modalità aggiunta o modifica
$is_edit_mode = isset($product_data['id']) && $product_data['id'] !== null;
$form_title = $is_edit_mode ? 'Modifica Prodotto' : 'Aggiungi Nuovo Prodotto';
$submit_button_text = $is_edit_mode ? 'Aggiorna Prodotto' : 'Salva Prodotto';
$action_url = $is_edit_mode ? "index.php?page=products_catalog&action=edit&id=" . htmlspecialchars($product_data['id']) : "index.php?page=products_catalog&action=add";
$cancel_url = "index.php?page=products_catalog";

// Pre-popola i valori del form in base alla modalità
$product_id = $is_edit_mode ? htmlspecialchars($product_data['id']) : '';
$product_code = htmlspecialchars($product_data['product_code'] ?? '');
$product_type = htmlspecialchars($product_data['product_type'] ?? '');
$product_name = htmlspecialchars($product_data['product_name'] ?? '');
$description = htmlspecialchars($product_data['description'] ?? '');
$default_price_net = htmlspecialchars($product_data['default_price_net'] ?? '0.00');
$default_price_gross = htmlspecialchars($product_data['default_price_gross'] ?? '0.00');
$amperes = htmlspecialchars($product_data['amperes'] ?? '');
$volts = htmlspecialchars($product_data['volts'] ?? '');
$other_specs = htmlspecialchars($product_data['other_specs'] ?? '');
$is_active_checked = ($product_data['is_active'] ?? 1) == 1 ? 'checked' : ''; // Default attivo
?>

<h2 class="text-2xl font-semibold mb-4"><?php echo $form_title; ?></h2>

<form method="POST" action="<?php echo $action_url; ?>" class="bg-white p-6 rounded-lg shadow-md max-w-2xl mx-auto">
    <?php if ($is_edit_mode): ?>
        <input type="hidden" name="id" value="<?php echo $product_id; ?>">
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
        <!-- Colonna 1: Dati Base Prodotto -->
        <div>
            <label for="product_code" class="block text-gray-700 text-sm font-bold mb-2">Codice Prodotto: <span class="text-red-500">*</span></label>
            <input type="text" id="product_code" name="product_code" value="<?php echo $product_code; ?>" required class="w-full mb-3">

            <label for="product_type" class="block text-gray-700 text-sm font-bold mb-2">Tipo Prodotto: <span class="text-red-500">*</span></label>
            <input type="text" id="product_type" name="product_type" value="<?php echo $product_type; ?>" required class="w-full mb-3" placeholder="Es. MMA, TIG, MIG, PLASMA">

            <label for="product_name" class="block text-gray-700 text-sm font-bold mb-2">Nome Prodotto: <span class="text-red-500">*</span></label>
            <input type="text" id="product_name" name="product_name" value="<?php echo $product_name; ?>" required class="w-full mb-3" placeholder="Es. Genera 170, Patrol 180">

            <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Descrizione:</label>
            <textarea id="description" name="description" rows="4" class="w-full mb-3"><?php echo $description; ?></textarea>
        </div>

        <!-- Colonna 2: Dati Prezzi e Specifiche -->
        <div>
            <label for="default_price_net" class="block text-gray-700 text-sm font-bold mb-2">Prezzo di Listino Netto: <span class="text-red-500">*</span></label>
            <input type="number" step="0.01" id="default_price_net" name="default_price_net" value="<?php echo $default_price_net; ?>" required class="w-full mb-3">

            <label for="default_price_gross" class="block text-gray-700 text-sm font-bold mb-2">Prezzo di Listino Lordo: <span class="text-red-500">*</span></label>
            <input type="number" step="0.01" id="default_price_gross" name="default_price_gross" value="<?php echo $default_price_gross; ?>" required class="w-full mb-3">

            <label for="amperes" class="block text-gray-700 text-sm font-bold mb-2">Ampere (corrente massima):</label>
            <input type="number" step="0.01" id="amperes" name="amperes" value="<?php echo $amperes; ?>" class="w-full mb-3">

            <label for="volts" class="block text-gray-700 text-sm font-bold mb-2">Volt (tensione di alimentazione):</label>
            <input type="text" id="volts" name="volts" value="<?php echo $volts; ?>" class="w-full mb-3" placeholder="Es. 230 V, 400 V">

            <label for="other_specs" class="block text-gray-700 text-sm font-bold mb-2">Altre Specifiche:</label>
            <textarea id="other_specs" name="other_specs" rows="4" class="w-full mb-3"><?php echo $other_specs; ?></textarea>

            <div class="flex items-center mb-3">
                <input type="checkbox" id="is_active" name="is_active" class="w-auto mr-2" <?php echo $is_active_checked; ?>>
                <label for="is_active" class="text-gray-700 text-sm font-bold">Prodotto Attivo nel Catalogo</label>
            </div>
        </div>
    </div> <!-- Fine della griglia dei campi -->

    <div class="flex items-center justify-between mt-6">
        <button type="submit" class="btn btn-primary">
            <?php echo $submit_button_text; ?>
        </button>
        <a href="<?php echo $cancel_url; ?>" class="btn btn-secondary">Annulla</a>
    </div>
</form>

<?php if ($is_edit_mode): ?>
    <h3 class="text-xl font-semibold mt-8 mb-4">Informazioni Fornitore</h3>
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <a href="index.php?page=products_catalog&action=add_supplier_info&product_id=<?php echo htmlspecialchars($product_id); ?>" class="btn btn-primary mb-4">
            Aggiungi Info Fornitore
        </a>

        <?php if (isset($supplier_info_list) && count($supplier_info_list) > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th>Fornitore</th>
                            <th>Codice Fornitore</th>
                            <th>Prezzo Acquisto</th>
                            <th>Data Acquisto</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($supplier_info_list as $info): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($info['supplier_name']); ?></td>
                                <td><?php echo htmlspecialchars($info['supplier_product_code'] ?? 'N/D'); ?></td>
                                <td>&euro; <?php echo htmlspecialchars(number_format($info['purchase_price'], 2, ',', '.')); ?></td>
                                <td><?php echo $info['purchase_date'] ? date('d/m/Y', strtotime($info['purchase_date'])) : 'N/D'; ?></td>
                                <td class="whitespace-nowrap">
                                    <a href="index.php?page=products_catalog&action=edit_supplier_info&id=<?php echo htmlspecialchars($info['id']); ?>&product_id=<?php echo htmlspecialchars($product_id); ?>" title="Modifica Info Fornitore" class="text-yellow-600 hover:text-yellow-800 p-2 rounded-full hover:bg-yellow-100 transition duration-150 ease-in-out">
                                        <i class="fas fa-edit text-lg"></i>
                                    </a>
                                    <a href="index.php?page=products_catalog&action=delete_supplier_info&id=<?php echo htmlspecialchars($info['id']); ?>&product_id=<?php echo htmlspecialchars($product_id); ?>" title="Elimina Info Fornitore" class="text-red-600 hover:text-red-800 p-2 rounded-full hover:bg-red-100 transition duration-150 ease-in-out ml-1" onclick="return confirm('Sei sicuro di voler eliminare questa informazione fornitore?');">
                                        <i class="fas fa-trash-alt text-lg"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-600">Nessuna informazione fornitore associata a questo prodotto.</p>
        <?php endif; ?>
    </div>
<?php endif; ?>
