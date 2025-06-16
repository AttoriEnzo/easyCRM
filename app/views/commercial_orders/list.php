<?php
// app/views/commercial_orders/list.php

// Le variabili $orders, $search_query, $filter_status, $available_statuses sono passate dal CommercialOrderController->index()

// Recupera il ruolo e l'ID dell'utente corrente dalla sessione per i controlli di visibilità
$current_user_role = $_SESSION['role'] ?? null;
$current_user_id = $_SESSION['user_id'] ?? null;

// Determina i permessi per le azioni CRUD
$can_add_edit_order = in_array($current_user_role, ['commerciale', 'admin', 'superadmin']);
$can_delete_order = in_array($current_user_role, ['admin', 'superadmin']);
$can_view_all_orders = in_array($current_user_role, ['admin', 'superadmin', 'tecnico']); // Tecnico vede tutti gli ordini
$can_print_commercial_doc = in_array($current_user_role, ['commerciale', 'admin', 'superadmin']);
$can_print_technical_doc = in_array($current_user_role, ['tecnico', 'admin', 'superadmin']);
?>

<h2 class="text-2xl font-semibold mb-4">Elenco Ordini Commerciali</h2>

<div class="flex flex-col md:flex-row justify-between items-center mb-4">
    <?php if ($can_add_edit_order): ?>
        <div class="mb-2 md:mb-0">
            <a href="index.php?page=commercial_orders&action=add" class="btn btn-primary">Crea Nuovo Ordine</a>
        </div>
    <?php endif; ?>
    <form method="GET" class="w-full md:w-auto flex flex-col md:flex-row items-center space-y-2 md:space-y-0 md:space-x-2">
        <input type="hidden" name="page" value="commercial_orders">
        
        <select name="status" class="w-full md:w-auto">
            <?php foreach ($available_statuses as $status_option): ?>
                <option value="<?php echo htmlspecialchars($status_option); ?>" <?php echo ($filter_status === $status_option) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($status_option); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="text" name="q" placeholder="Cerca ordini..." value="<?php echo htmlspecialchars($search_query); ?>" class="w-full md:w-64">
        <button type="submit" class="btn btn-secondary flex items-center justify-center">
            <i class="fas fa-search mr-1"></i> Cerca
        </button>
    </form>
</div>

<?php if (isset($orders) && count($orders) > 0): ?>
    <div class="overflow-x-auto bg-white rounded-lg shadow-sm">
        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    <th class="text-sm">ID Ordine</th>
                    <th class="text-sm">Cliente</th>
                    <th class="text-sm">Data Ordine</th>
                    <th class="text-sm">Stato</th>
                    <th class="text-sm">Totale</th>
                    <th class="text-sm">Commerciale</th>
                    <th class="text-sm">Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td class="text-xs"><?php echo htmlspecialchars($order['id']); ?></td>
                        <td class="text-xs">
                            <a href="index.php?page=contacts&action=view&id=<?php echo htmlspecialchars($order['contact_id']); ?>" class="text-blue-600 hover:underline">
                                <?php echo htmlspecialchars($order['company'] ?: $order['contact_first_name'] . ' ' . $order['contact_last_name']); ?>
                            </a>
                        </td>
                        <td class="text-xs"><?php echo date('d/m/Y', strtotime($order['order_date'])); ?></td>
                        <td class="text-xs">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold 
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
                        </td>
                        <td class="text-xs">&euro; <?php echo htmlspecialchars(number_format($order['total_amount'], 2, ',', '.')); ?></td>
                        <td class="text-xs"><?php echo htmlspecialchars($order['commercial_username'] ?? 'N/D'); ?></td>
                        <td class="whitespace-nowrap text-xs">
                            <a href="index.php?page=commercial_orders&action=view&id=<?php echo $order['id']; ?>" title="Vedi Dettagli" class="text-blue-600 hover:text-blue-800 p-2 rounded-full hover:bg-blue-100 transition duration-150 ease-in-out">
                                <i class="fas fa-eye text-lg"></i>
                            </a>
                            <?php 
                            // Un commerciale può modificare solo i propri ordini
                            if ($can_add_edit_order && ($_SESSION['role'] !== 'commerciale' || $order['commercial_user_id'] === $current_user_id)): ?>
                                <a href="index.php?page=commercial_orders&action=edit&id=<?php echo $order['id']; ?>" title="Modifica Ordine" class="text-yellow-600 hover:text-yellow-800 p-2 rounded-full hover:bg-yellow-100 transition duration-150 ease-in-out ml-1">
                                    <i class="fas fa-edit text-lg"></i>
                                </a>
                            <?php else: ?>
                                <span class="text-gray-400 opacity-50 p-2 cursor-not-allowed ml-1" title="Non hai i permessi per modificare questo ordine">
                                    <i class="fas fa-edit text-lg"></i>
                                </span>
                            <?php endif; ?>

                            <?php if ($can_delete_order): ?>
                                <a href="index.php?page=commercial_orders&action=delete&id=<?php echo $order['id']; ?>" title="Elimina Ordine" class="text-red-600 hover:text-red-800 p-2 rounded-full hover:bg-red-100 transition duration-150 ease-in-out ml-1" onclick="return confirm('Sei sicuro di voler eliminare questo ordine? Questa azione è irreversibile.');">
                                    <i class="fas fa-trash-alt text-lg"></i>
                                </a>
                            <?php else: ?>
                                <span class="text-gray-400 opacity-50 p-2 cursor-not-allowed ml-1" title="Non hai i permessi per eliminare questo ordine">
                                    <i class="fas fa-trash-alt text-lg"></i>
                                </span>
                            <?php endif; ?>

                            <?php if ($can_print_commercial_doc && ($_SESSION['role'] !== 'commerciale' || $order['commercial_user_id'] === $current_user_id)): ?>
                                <a href="index.php?page=commercial_orders&action=print_commercial_doc&id=<?php echo $order['id']; ?>" target="_blank" title="Stampa Conferma Ordine" class="text-green-600 hover:text-green-800 p-2 rounded-full hover:bg-green-100 transition duration-150 ease-in-out ml-1">
                                    <i class="fas fa-file-invoice text-lg"></i>
                                </a>
                            <?php else: ?>
                                <span class="text-gray-400 opacity-50 p-2 cursor-not-allowed ml-1" title="Non hai i permessi per stampare la conferma ordine">
                                    <i class="fas fa-file-invoice text-lg"></i>
                                </span>
                            <?php endif; ?>

                            <?php if ($can_print_technical_doc): ?>
                                <a href="index.php?page=commercial_orders&action=print_technical_doc&id=<?php echo $order['id']; ?>" target="_blank" title="Stampa Documento Tecnico" class="text-purple-600 hover:text-purple-800 p-2 rounded-full hover:bg-purple-100 transition duration-150 ease-in-out ml-1">
                                    <i class="fas fa-cogs text-lg"></i>
                                </a>
                            <?php else: ?>
                                <span class="text-gray-400 opacity-50 p-2 cursor-not-allowed ml-1" title="Non hai i permessi per stampare il documento tecnico">
                                    <i class="fas fa-cogs text-lg"></i>
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <p class="text-gray-600">Nessun ordine trovato.
    <?php if ($can_add_edit_order): ?>
        <a href="index.php?page=commercial_orders&action=add" class="text-blue-600 hover:underline">Creane uno nuovo!</a>
    <?php endif; ?>
    </p>
<?php endif; ?>
