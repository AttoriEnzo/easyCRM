<?php
// app/views/repair_service_items/form.php

// Le variabili $item (se in modalità modifica), $form_title, $submit_button_text, $action_url, $cancel_url
// sono passate dal RepairServiceItemController.

// Determina se siamo in modalità aggiunta o modifica
$is_edit_mode = isset($item['id']) && $item['id'] !== null;
$form_title = $is_edit_mode ? 'Modifica Intervento di Servizio' : 'Aggiungi Nuovo Intervento di Servizio';
$submit_button_text = $is_edit_mode ? 'Aggiorna Intervento' : 'Crea Intervento';
$action_url = $is_edit_mode ? "index.php?page=repair_services&action=edit&id=" . htmlspecialchars($item['id']) : "index.php?page=repair_services&action=add";
$cancel_url = "index.php?page=repair_services";

// Pre-popola i valori del form in base alla modalità
$name = htmlspecialchars($item['name'] ?? '');
$description = htmlspecialchars($item['description'] ?? '');
$default_cost = htmlspecialchars($item['default_cost'] ?? '0.00');
$is_active_checked = ($item['is_active'] ?? 1) == 1 ? 'checked' : ''; // Default attivo
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

<form method="POST" action="<?php echo $action_url; ?>" class="bg-white p-6 rounded-lg shadow-md max-w-lg mx-auto">
    <div class="mb-4">
        <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Nome Intervento: <span class="text-red-500">*</span></label>
        <input type="text" id="name" name="name" value="<?php echo $name; ?>" required
               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
    </div>

    <div class="mb-4">
        <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Descrizione:</label>
        <textarea id="description" name="description" rows="4"
                  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"><?php echo $description; ?></textarea>
    </div>

    <div class="mb-4">
        <label for="default_cost" class="block text-gray-700 text-sm font-bold mb-2">Costo Predefinito (€): <span class="text-red-500">*</span></label>
        <input type="number" step="0.01" id="default_cost" name="default_cost" value="<?php echo $default_cost; ?>" required
               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
    </div>

    <div class="flex items-center mb-6">
        <input type="checkbox" id="is_active" name="is_active" class="w-auto mr-2" <?php echo $is_active_checked; ?>>
        <label for="is_active" class="text-gray-700 text-sm font-bold">Intervento Attivo</label>
        <p class="text-xs text-gray-600 ml-2">(Se disattivato, non sarà selezionabile per nuove riparazioni.)</p>
    </div>

    <div class="flex items-center justify-between mt-6">
        <button type="submit" class="btn btn-primary">
            <?php echo $submit_button_text; ?>
        </button>
        <a href="<?php echo $cancel_url; ?>" class="btn btn-secondary">Annulla</a>
    </div>
</form>
