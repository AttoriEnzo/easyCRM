<?php
// app/views/commercial_orders/add_edit.php

// Le variabili $order_data (se in modalità modifica), $form_title, $submit_button_text, $action_url,
// $contacts_list, $order_items (se in modalità modifica) sono passate dal CommercialOrderController.

// Determina se siamo in modalità aggiunta o modifica
$is_edit_mode = isset($order_data['id']) && $order_data['id'] !== null;
$form_title = $is_edit_mode ? 'Modifica Ordine Commerciale #' . htmlspecialchars($order_data['id']) : 'Crea Nuovo Ordine Commerciale';
$submit_button_text = $is_edit_mode ? 'Aggiorna Ordine' : 'Salva Ordine';
$action_url = $is_edit_mode ? "index.php?page=commercial_orders&action=edit&id=" . htmlspecialchars($order_data['id']) : "index.php?page=commercial_orders&action=add";
$cancel_url = $is_edit_mode ? "index.php?page=commercial_orders&action=view&id=" . htmlspecialchars($order_data['id']) : "index.php?page=commercial_orders";

// Pre-popola i valori del form in base alla modalità o ai dati POST in caso di errore di validazione
$contact_id = htmlspecialchars($order_data['contact_id'] ?? '');
$order_date = htmlspecialchars($order_data['order_date'] ?? date('Y-m-d'));
$status = htmlspecialchars($order_data['status'] ?? 'Ordine Inserito');
$expected_shipping_date = htmlspecialchars($order_data['expected_shipping_date'] ?? '');
$shipping_address = htmlspecialchars($order_data['shipping_address'] ?? '');
$shipping_city = htmlspecialchars($order_data['shipping_city'] ?? '');
$shipping_zip = htmlspecialchars($order_data['shipping_zip'] ?? '');
$shipping_province = htmlspecialchars($order_data['shipping_province'] ?? '');
$carrier = htmlspecialchars($order_data['carrier'] ?? '');
$shipping_costs = htmlspecialchars($order_data['shipping_costs'] ?? '0.00');
$notes_commercial = htmlspecialchars($order_data['notes_commercial'] ?? '');
$notes_technical = htmlspecialchars($order_data['notes_technical'] ?? '');
$total_amount = htmlspecialchars($order_data['total_amount'] ?? '0.00');

// Stati disponibili (coerenti con il modello)
$available_statuses = [
    'Ordine Inserito', 'In Preparazione', 'Pronto per Spedizione', 'Spedito', 'Fatturato',
    'Annullato', 'In Attesa di Pagamento', 'Pagato'
];

// Recupera il ruolo dell'utente corrente per i permessi
$current_user_role = $_SESSION['role'] ?? null;
$current_user_id = $_SESSION['user_id'] ?? null;

// Logica per bloccare i campi in base al ruolo e allo stato dell'ordine
$can_edit_all_fields = in_array($current_user_role, ['admin', 'superadmin']);
$can_edit_commercial_fields = in_array($current_user_role, ['commerciale', 'admin', 'superadmin']);
$can_edit_technical_fields = in_array($current_user_role, ['tecnico', 'admin', 'superadmin']);

// Un Commerciale può modificare solo i propri ordini e fino a un certo stato
$is_commerciale_and_not_owner = ($current_user_role === 'commerciale' && $is_edit_mode && $order_data['commercial_user_id'] != $current_user_id);
$is_locked_for_commerciale = $is_edit_mode && in_array($order_data['status'], ['Pronto per Spedizione', 'Spedito', 'Fatturato', 'Annullato', 'Pagato']);

?>

<h2 class="text-2xl font-semibold mb-4"><?php echo $form_title; ?></h2>

<form method="POST" action="<?php echo $action_url; ?>" class="bg-white p-6 rounded-lg shadow-md max-w-4xl mx-auto">
    <?php if ($is_edit_mode): ?>
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($order_data['id']); ?>">
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 mb-6">
        <!-- Dettagli Ordine Commerciale -->
        <div>
            <h3 class="text-xl font-semibold mb-3 text-indigo-700">Dettagli Ordine</h3>
            <label for="contact_id" class="block text-gray-700 text-sm font-bold mb-2">Cliente: <span class="text-red-500">*</span></label>
            <select id="contact_id" name="contact_id" required class="w-full mb-3" <?php echo ($is_edit_mode && $is_locked_for_commerciale && !$can_edit_all_fields) ? 'disabled' : ''; ?>>
                <option value="">Seleziona Cliente</option>
                <?php foreach ($contacts_list as $contact): ?>
                    <option value="<?php echo htmlspecialchars($contact['id']); ?>"
                            <?php echo ($contact['id'] == $contact_id) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($contact['company'] ?: $contact['first_name'] . ' ' . $contact['last_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="order_date" class="block text-gray-700 text-sm font-bold mb-2">Data Ordine: <span class="text-red-500">*</span></label>
            <input type="date" id="order_date" name="order_date" value="<?php echo $order_date; ?>" required class="w-full mb-3" <?php echo ($is_edit_mode && $is_locked_for_commerciale && !$can_edit_all_fields) ? 'disabled' : ''; ?>>

            <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Stato Ordine: <span class="text-red-500">*</span></label>
            <select id="status" name="status" required class="w-full mb-3" <?php echo (!$can_edit_all_fields && !$can_edit_technical_fields) ? 'disabled' : ''; ?>>
                <?php foreach ($available_statuses as $s): ?>
                    <option value="<?php echo htmlspecialchars($s); ?>" <?php echo ($status == $s) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($s); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <label for="expected_shipping_date" class="block text-gray-700 text-sm font-bold mb-2">Data Spedizione Prevista:</label>
            <input type="date" id="expected_shipping_date" name="expected_shipping_date" value="<?php echo $expected_shipping_date; ?>" class="w-full mb-3" <?php echo ($is_edit_mode && $is_locked_for_commerciale && !$can_edit_all_fields) ? 'disabled' : ''; ?>>

            <label for="carrier" class="block text-gray-700 text-sm font-bold mb-2">Vettore:</label>
            <input type="text" id="carrier" name="carrier" value="<?php echo $carrier; ?>" class="w-full mb-3" <?php echo ($is_edit_mode && $is_locked_for_commerciale && !$can_edit_all_fields) ? 'disabled' : ''; ?>>

            <label for="shipping_costs" class="block text-gray-700 text-sm font-bold mb-2">Costi di Spedizione:</label>
            <input type="number" step="0.01" id="shipping_costs" name="shipping_costs" value="<?php echo $shipping_costs; ?>" class="w-full mb-3" <?php echo ($is_edit_mode && $is_locked_for_commerciale && !$can_edit_all_fields) ? 'disabled' : ''; ?>>
        </div>

        <!-- Dettagli Spedizione e Note -->
        <div>
            <h3 class="text-xl font-semibold mb-3 text-indigo-700">Dettagli Spedizione</h3>
            <label for="shipping_address" class="block text-gray-700 text-sm font-bold mb-2">Indirizzo di Spedizione:</label>
            <input type="text" id="shipping_address" name="shipping_address" value="<?php echo $shipping_address; ?>" class="w-full mb-3" <?php echo ($is_edit_mode && $is_locked_for_commerciale && !$can_edit_all_fields) ? 'disabled' : ''; ?>>

            <label for="shipping_city" class="block text-gray-700 text-sm font-bold mb-2">Città di Spedizione:</label>
            <input type="text" id="shipping_city" name="shipping_city" value="<?php echo $shipping_city; ?>" class="w-full mb-3" <?php echo ($is_edit_mode && $is_locked_for_commerciale && !$can_edit_all_fields) ? 'disabled' : ''; ?>>

            <label for="shipping_zip" class="block text-gray-700 text-sm font-bold mb-2">CAP di Spedizione (5 cifre):</label>
            <input type="text" id="shipping_zip" name="shipping_zip" value="<?php echo $shipping_zip; ?>" pattern="^\d{5}$" title="Inserire esattamente 5 cifre numeriche." class="w-full mb-3" <?php echo ($is_edit_mode && $is_locked_for_commerciale && !$can_edit_all_fields) ? 'disabled' : ''; ?>>

            <label for="shipping_province" class="block text-gray-700 text-sm font-bold mb-2">Provincia di Spedizione (2 lettere):</label>
            <input type="text" id="shipping_province" name="shipping_province" value="<?php echo $shipping_province; ?>" pattern="^[A-Z]{2}$" title="Inserire esattamente 2 lettere maiuscole per la provincia (es. RM, MI)." class="w-full mb-3 uppercase" <?php echo ($is_edit_mode && $is_locked_for_commerciale && !$can_edit_all_fields) ? 'disabled' : ''; ?>>

            <label for="notes_commercial" class="block text-gray-700 text-sm font-bold mb-2">Note Commerciali:</label>
            <textarea id="notes_commercial" name="notes_commercial" rows="3" class="w-full mb-3" <?php echo ($can_edit_commercial_fields && !$is_commerciale_and_not_owner) ? '' : 'disabled'; ?>><?php echo $notes_commercial; ?></textarea>

            <label for="notes_technical" class="block text-gray-700 text-sm font-bold mb-2">Note Tecniche:</label>
            <textarea id="notes_technical" name="notes_technical" rows="3" class="w-full mb-3" <?php echo $can_edit_technical_fields ? '' : 'disabled'; ?>><?php echo $notes_technical; ?></textarea>
        </div>
    </div>

    <div class="flex items-center justify-between mt-6">
        <button type="submit" class="btn btn-primary" <?php echo ($is_commerciale_and_not_owner || ($is_edit_mode && $is_locked_for_commerciale && !$can_edit_all_fields && !$can_edit_technical_fields)) ? 'disabled' : ''; ?>>
            <?php echo $submit_button_text; ?>
        </button>
        <a href="<?php echo $cancel_url; ?>" class="btn btn-secondary">Annulla</a>
    </div>
</form>

<?php if ($is_edit_mode): // Se siamo in modalità modifica, mostra la sezione degli articoli e il totale ?>
    <div class="bg-white p-6 rounded-lg shadow-md mt-6">
        <h3 class="text-xl font-semibold mb-4 border-b pb-2">Articoli dell'Ordine</h3>
        <p class="text-lg font-bold text-gray-800 mb-4">Totale Ordine: &euro; <?php echo htmlspecialchars(number_format($total_amount, 2, ',', '.')); ?></p>

        <?php 
        // Permessi per aggiungere/modificare/eliminare voci d'ordine
        $can_manage_order_items = in_array($current_user_role, ['commerciale', 'admin', 'superadmin', 'tecnico']);
        // Blocca la modifica/eliminazione per i commerciali se l'ordine è in stato avanzato
        $commerciale_item_lock = ($current_user_role === 'commerciale' && in_array($order_data['status'], ['Pronto per Spedizione', 'Spedito', 'Fatturato', 'Annullato', 'Pagato']));
        ?>

        <?php if ($can_manage_order_items && !$commerciale_item_lock): ?>
            <a href="index.php?page=commercial_orders&action=add_item&order_id=<?php echo htmlspecialchars($order_data['id']); ?>" class="btn btn-primary mb-4">
                Aggiungi Articolo
            </a>
        <?php else: ?>
            <span class="btn btn-primary opacity-50 cursor-not-allowed mb-4" title="Non puoi aggiungere articoli a questo ordine in base al suo stato o ai tuoi permessi.">
                Aggiungi Articolo
            </span>
        <?php endif; ?>

        <?php if (isset($order_items) && count($order_items) > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th>Descrizione</th>
                            <th>Quantità Ordinata</th>
                            <th>Prezzo Unitario</th>
                            <th>Totale Riga</th>
                            <th>Quantità Spedita</th>
                            <th>Numero Seriale</th>
                            <th>Note Articolo</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['description']); ?></td>
                                <td><?php echo htmlspecialchars($item['ordered_quantity']); ?></td>
                                <td>&euro; <?php echo htmlspecialchars(number_format($item['ordered_unit_price'], 2, ',', '.')); ?></td>
                                <td>&euro; <?php echo htmlspecialchars(number_format($item['item_total'], 2, ',', '.')); ?></td>
                                <td><?php echo htmlspecialchars($item['actual_shipped_quantity']); ?></td>
                                <td><?php echo htmlspecialchars($item['actual_shipped_serial_number'] ?? 'N/D'); ?></td>
                                <td><?php echo htmlspecialchars(substr($item['notes_item'] ?? 'N/D', 0, 50)); ?><?php echo strlen($item['notes_item'] ?? '') > 50 ? '...' : ''; ?></td>
                                <td class="whitespace-nowrap">
                                    <?php if ($can_manage_order_items && !$commerciale_item_lock): ?>
                                        <a href="index.php?page=commercial_orders&action=edit_item&id=<?php echo htmlspecialchars($item['id']); ?>&order_id=<?php echo htmlspecialchars($order_data['id']); ?>" title="Modifica Articolo" class="text-yellow-600 hover:text-yellow-800 p-2 rounded-full hover:bg-yellow-100 transition duration-150 ease-in-out">
                                            <i class="fas fa-edit text-lg"></i>
                                        </a>
                                        <a href="index.php?page=commercial_orders&action=delete_item&id=<?php echo htmlspecialchars($item['id']); ?>&order_id=<?php echo htmlspecialchars($order_data['id']); ?>" title="Elimina Articolo" class="text-red-600 hover:text-red-800 p-2 rounded-full hover:bg-red-100 transition duration-150 ease-in-out ml-1" onclick="return confirm('Sei sicuro di voler eliminare questa voce d\'ordine?');">
                                            <i class="fas fa-trash-alt text-lg"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-400 opacity-50 p-2 cursor-not-allowed" title="Non hai i permessi per modificare o eliminare questo articolo.">
                                            <i class="fas fa-edit text-lg"></i>
                                        </span>
                                        <span class="text-gray-400 opacity-50 p-2 cursor-not-allowed ml-1" title="Non hai i permessi per modificare o eliminare questo articolo.">
                                            <i class="fas fa-trash-alt text-lg"></i>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-600">Nessun articolo associato a questo ordine.</p>
        <?php endif; ?>
    </div>
<?php endif; ?>
