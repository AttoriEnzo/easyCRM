<?php
// app/views/contacts/add_edit_address.php

// Le variabili $contact, $address_data, $form_title, $submit_button_text, $action_url
// sono passate dal ContactController->addAddress() o ContactController->editAddress()

// Assicurati che $contact sia definito (dovrebbe sempre esserlo)
if (!isset($contact) || $contact === null) {
    echo "<p class='flash-error'>Dati contatto non disponibili per la gestione indirizzo.</p>";
    return;
}

// Determina se siamo in modalità aggiunta o modifica
$is_edit_mode = isset($address_data['id']) && $address_data['id'] !== null;

// Pre-popola i valori del form in base alla modalità o ai dati POST in caso di errore di validazione
$address_id = $is_edit_mode ? htmlspecialchars($address_data['id']) : '';
$address_type = htmlspecialchars($address_data['address_type'] ?? '');
$address = htmlspecialchars($address_data['address'] ?? '');
$city = htmlspecialchars($address_data['city'] ?? '');
$zip = htmlspecialchars($address_data['zip'] ?? '');
$province = htmlspecialchars($address_data['province'] ?? '');
$is_default_shipping_checked = ($address_data['is_default_shipping'] ?? 0) == 1 ? 'checked' : '';

// URL per annullare e tornare alla vista del contatto
$cancel_url = "index.php?page=contacts&action=view&id=" . htmlspecialchars($contact['id']);
?>

<h2 class="text-2xl font-semibold mb-4"><?php echo $form_title; ?></h2>

<form method="POST" action="<?php echo $action_url; ?>" class="bg-white p-6 rounded-lg shadow-md max-w-lg mx-auto">
    <input type="hidden" name="contact_id" value="<?php echo htmlspecialchars($contact['id']); ?>">
    <?php if ($is_edit_mode): ?>
        <input type="hidden" name="id" value="<?php echo $address_id; ?>">
    <?php endif; ?>

    <div class="mb-4">
        <label for="address_type" class="block text-gray-700 text-sm font-bold mb-2">Tipo Indirizzo:</label>
        <select id="address_type" name="address_type" required class="w-full">
            <option value="">Seleziona Tipo</option>
            <option value="Principale" <?php echo ($address_type == 'Principale') ? 'selected' : ''; ?>>Principale</option>
            <option value="Spedizione" <?php echo ($address_type == 'Spedizione') ? 'selected' : ''; ?>>Spedizione</option>
            <option value="Fatturazione" <?php echo ($address_type == 'Fatturazione') ? 'selected' : ''; ?>>Fatturazione</option>
            <option value="Secondaria" <?php echo ($address_type == 'Secondaria') ? 'selected' : ''; ?>>Secondaria</option>
        </select>
    </div>

    <div class="mb-4">
        <label for="address" class="block text-gray-700 text-sm font-bold mb-2">Indirizzo:</label>
        <input type="text" id="address" name="address" value="<?php echo $address; ?>" required class="w-full">
    </div>

    <div class="mb-4">
        <label for="city" class="block text-gray-700 text-sm font-bold mb-2">Città:</label>
        <input type="text" id="city" name="city" value="<?php echo $city; ?>" required class="w-full">
    </div>

    <div class="mb-4">
        <label for="zip" class="block text-gray-700 text-sm font-bold mb-2">CAP (5 cifre):</label>
        <input type="text" id="zip" name="zip" value="<?php echo $zip; ?>" pattern="^\d{5}$" title="Inserire esattamente 5 cifre numeriche." required class="w-full">
    </div>

    <div class="mb-6">
        <label for="province" class="block text-gray-700 text-sm font-bold mb-2">Provincia (2 lettere maiuscole):</label>
        <input type="text" id="province" name="province" value="<?php echo $province; ?>" pattern="^[A-Z]{2}$" title="Inserire esattamente 2 lettere maiuscole per la provincia (es. RM, MI)." required class="w-full uppercase">
    </div>

    <div class="flex items-center mb-6">
        <input type="checkbox" id="is_default_shipping" name="is_default_shipping" class="w-auto mr-2" <?php echo $is_default_shipping_checked; ?>>
        <label for="is_default_shipping" class="text-gray-700 text-sm font-bold">Imposta come indirizzo di spedizione predefinito</label>
    </div>

    <div class="flex items-center justify-between">
        <button type="submit" class="btn btn-primary">
            <?php echo $submit_button_text; ?>
        </button>
        <a href="<?php echo $cancel_url; ?>" class="btn btn-secondary">Annulla</a>
    </div>
</form>
