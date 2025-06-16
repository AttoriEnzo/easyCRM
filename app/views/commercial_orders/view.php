<?php
// app/views/commercial_orders/view.php

// Le variabili $order, $order_items, $contact_interactions sono passate dal CommercialOrderController->view()

// Assicurati che $order sia definito
if (!isset($order) || $order === null) {
    echo "<p class='flash-error'>Dati ordine non disponibili.</p>";
    return;
}

// Recupera il ruolo dell'utente corrente dalla sessione per i controlli di visibilità
$current_user_role = $_SESSION['role'] ?? null;
$current_user_id = $_SESSION['user_id'] ?? null;

// Permessi per le azioni
$can_edit_order = in_array($current_user_role, ['commerciale', 'admin', 'superadmin']) &&
                  ($_SESSION['role'] !== 'commerciale' || $order['commercial_user_id'] === $current_user_id);
$can_delete_order = in_array($current_user_role, ['admin', 'superadmin']);
$can_print_commercial_doc = in_array($current_user_role, ['commerciale', 'admin', 'superadmin']) &&
                            ($_SESSION['role'] !== 'commerciale' || $order['commercial_user_id'] === $current_user_id);
$can_print_technical_doc = in_array($current_user_role, ['tecnico', 'admin', 'superadmin']);
?>

<h2 class="text-2xl font-semibold mb-4">Dettagli Ordine Commerciale #<?php echo htmlspecialchars($order['id']); ?></h2>

<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <h3 class="text-xl font-semibold mb-4 border-b pb-2">Dettagli Ordine</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
        <!-- Colonna 1: Dati principali dell'ordine -->
        <div>
            <p class="mb-2"><strong class="font-medium">ID Ordine:</strong> <?php echo htmlspecialchars($order['id']); ?></p>
            <p class="mb-2"><strong class="font-medium">Cliente:</strong> 
                <?php 
                echo '<a href="index.php?page=contacts&action=view&id=' . htmlspecialchars($order['contact_id']) . '" class="text-blue-600 hover:underline">' . htmlspecialchars($order['company'] ?: $order['contact_first_name'] . ' ' . $order['contact_last_name']) . '</a>';
                ?>
            </p>
            <p class="mb-2"><strong class="font-medium">Data Ordine:</strong> <?php echo date('d/m/Y', strtotime($order['order_date'])); ?></p>
            <p class="mb-2"><strong class="font-medium">Stato:</strong> 
                <span class="px-2 py-1 rounded-md text-sm font-semibold 
                    <?php 
                        // Classi CSS basate sullo stato per colorare le etichette
                        if ($order['status'] == 'Spedito' || $order['status'] == 'Fatturato' || $order['status'] == 'Pagato') echo 'bg-green-100 text-green-800';
                        else if ($order['status'] == 'In Preparazione' || $order['status'] == 'Pronto per Spedizione') echo 'bg-blue-100 text-blue-800';
                        else if ($order['status'] == 'In Attesa di Pagamento') echo 'bg-yellow-100 text-yellow-800';
                        else if ($order['status'] == 'Annullato') echo 'bg-red-100 text-red-800';
                        else echo 'bg-gray-100 text-gray-800';
                    ?>">
                    <?php echo htmlspecialchars($order['status']); ?>
                </span>
            </p>
            <p class="mb-2"><strong class="font-medium">Spedizione Prevista:</strong> <?php echo $order['expected_shipping_date'] ? date('d/m/Y', strtotime($order['expected_shipping_date'])) : 'N/D'; ?></p>
            <p class="mb-2"><strong class="font-medium">Vettore:</strong> <?php echo htmlspecialchars($order['carrier'] ?? 'N/D'); ?></p>
            <p class="mb-2"><strong class="font-medium">Costi Spedizione:</strong> <?php echo !empty($order['shipping_costs']) ? '&euro; ' . htmlspecialchars(number_format($order['shipping_costs'], 2, ',', '.')) : 'N/D'; ?></p>
            <p class="mb-2"><strong class="font-medium">Creato da:</strong> <?php echo htmlspecialchars($order['commercial_username'] ?? 'N/D'); ?></p>
        </div>

        <!-- Colonna 2: Indirizzo di spedizione e Note -->
        <div>
            <p class="mb-2"><strong class="font-medium">Indirizzo di Spedizione:</strong> <?php echo htmlspecialchars($order['shipping_address'] ?? 'N/D'); ?></p>
            <p class="mb-2"><strong class="font-medium">Città Spedizione:</strong> <?php echo htmlspecialchars($order['shipping_city'] ?? 'N/D'); ?></p>
            <p class="mb-2"><strong class="font-medium">CAP Spedizione:</strong> <?php echo htmlspecialchars($order['shipping_zip'] ?? 'N/D'); ?></p>
            <p class="mb-2"><strong class="font-medium">Provincia Spedizione:</strong> <?php echo htmlspecialchars($order['shipping_province'] ?? 'N/D'); ?></p>
            <p class="mb-2"><strong class="font-medium">Note Commerciali:</strong> <br><?php echo nl2br(htmlspecialchars($order['notes_commercial'] ?? 'N/D')); ?></p>
            <p class="mb-2"><strong class="font-medium">Note Tecniche:</strong> <br><?php echo nl2br(htmlspecialchars($order['notes_technical'] ?? 'N/D')); ?></p>
            <p class="mb-2"><strong class="font-medium">Totale Ordine:</strong> <span class="text-green-700 text-lg font-bold">&euro; <?php echo htmlspecialchars(number_format($order['total_amount'], 2, ',', '.')); ?></span></p>
        </div>
    </div>
    
    <div class="mt-6 flex space-x-2">
        <a href="index.php?page=commercial_orders" class="btn btn-secondary">Torna agli Ordini</a>
        <?php if ($can_edit_order): ?>
            <a href="index.php?page=commercial_orders&action=edit&id=<?php echo htmlspecialchars($order['id']); ?>" class="btn btn-primary">Modifica Ordine</a>
        <?php endif; ?>
        <?php if ($can_delete_order): ?>
            <a href="index.php?page=commercial_orders&action=delete&id=<?php echo htmlspecialchars($order['id']); ?>" class="btn btn-danger" onclick="return confirm('Sei sicuro di voler eliminare questo ordine? Questa azione è irreversibile.');">Elimina Ordine</a>
        <?php endif; ?>
        <?php if ($can_print_commercial_doc): ?>
            <a href="index.php?page=commercial_orders&action=print_commercial_doc&id=<?php echo htmlspecialchars($order['id']); ?>" target="_blank" class="btn btn-tertiary">Stampa Conferma Ordine</a>
        <?php endif; ?>
        <?php if ($can_print_technical_doc): ?>
            <a href="index.php?page=commercial_orders&action=print_technical_doc&id=<?php echo htmlspecialchars($order['id']); ?>" target="_blank" class="btn btn-tertiary ml-2">Stampa Documento Tecnico</a>
        <?php endif; ?>
    </div>
</div>

<div class="bg-white p-6 rounded-lg shadow-md mt-6">
    <h3 class="text-xl font-semibold mb-4 border-b pb-2">Articoli dell'Ordine</h3>
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
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['description']); ?></td>
                            <td><?php echo htmlspecialchars($item['ordered_quantity']); ?></td>
                            <td>&euro; <?php echo htmlspecialchars(number_format($item['ordered_unit_price'], 2, ',', '.')); ?></td>
                            <td>&euro; <?php
    $qty = isset($item['quantity']) ? (float)$item['quantity'] : 0;
    $price = isset($item['unit_price']) ? (float)$item['unit_price'] : 0;
    $item_total = $qty * $price;
    echo htmlspecialchars(number_format($item_total, 2, ',', '.'));
?></td>
                            <td><?php echo htmlspecialchars($item['actual_shipped_quantity'] ?? 0); ?></td>
                            <td><?php echo htmlspecialchars($item['actual_shipped_serial_number'] ?? 'N/D'); ?></td>
                            <td class="max-w-xs truncate" title="<?php echo htmlspecialchars($item['notes_item'] ?? ''); ?>">
                                <?php echo htmlspecialchars($item['notes_item'] ?? 'N/D'); ?>
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

<?php if (!empty($contact_interactions) || in_array($current_user_role, ['admin', 'superadmin', 'tecnico', 'commerciale'])): ?>
<div class="bg-white p-6 rounded-lg shadow-md mt-6">
    <h3 class="text-xl font-semibold mb-4 border-b pb-2">Interazioni del Cliente (<?php echo htmlspecialchars($order['company'] ?: $order['contact_first_name'] . ' ' . $order['contact_last_name']); ?>)</h3>
    <?php if (!empty($contact_interactions)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr>
                        <th class="text-sm">Data</th>
                        <th class="text-sm">Tipo</th>
                        <th class="text-sm">Note</th>
                        <th class="text-sm">Creato da</th>
                        <th class="text-sm">Creato il</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contact_interactions as $interaction): ?>
                        <tr>
                            <td class="text-xs"><?php echo htmlspecialchars(date('d/m/Y', strtotime($interaction['interaction_date']))); ?></td>
                            <td class="text-xs"><?php echo htmlspecialchars($interaction['type']); ?></td>
                            <td class="text-xs max-w-xs truncate" title="<?php echo htmlspecialchars($interaction['notes']); ?>">
                                <?php echo htmlspecialchars($interaction['notes']); ?>
                            </td>
                            <td class="text-xs"><?php echo htmlspecialchars($interaction['user_username'] ?? 'N/D'); ?></td>
                            <td class="text-xs"><?php echo date('d/m/Y H:i', strtotime($interaction['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-gray-600">Nessuna interazione trovata per questo cliente.</p>
    <?php endif; ?>
    <div class="mt-4">
        <?php if (in_array($current_user_role, ['admin', 'superadmin', 'tecnico', 'commerciale'])): ?>
            <a href="index.php?page=contacts&action=view&id=<?php echo htmlspecialchars($order['contact_id']); ?>#add-interaction-section" class="btn btn-primary">Aggiungi Nuova Interazione</a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
