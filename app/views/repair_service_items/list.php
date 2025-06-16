<?php
// app/views/repair_service_items/list.php

// La variabile $service_items e $search_query sono passate dal RepairServiceItemController->index()
?>

<h2 class="text-2xl font-semibold mb-4">Gestione Interventi di Servizio Preimpostati</h2>

<div class="flex flex-col md:flex-row justify-between items-center mb-4">
    <div class="flex space-x-2 mb-2 md:mb-0">
        <a href="index.php?page=repair_services&action=add" class="btn btn-primary">Aggiungi Nuovo Intervento</a>
    </div>
    <form method="GET" class="w-full md:w-auto flex items-center">
        <input type="hidden" name="page" value="repair_services">
        <input type="text" name="q" placeholder="Cerca interventi..." value="<?php echo htmlspecialchars($search_query); ?>" class="w-full md:w-64 mr-2">
        <button type="submit" class="btn btn-secondary flex items-center justify-center">
            <i class="fas fa-search mr-1"></i> Cerca
        </button>
    </form>
</div>

<?php if (isset($service_items) && count($service_items) > 0): ?>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-lg shadow-sm">
            <thead>
                <tr>
                    <th class="text-sm">ID</th>
                    <th class="text-sm">Nome</th>
                    <th class="text-sm">Costo Predefinito</th>
                    <th class="text-sm">Descrizione</th>
                    <th class="text-sm">Attivo</th>
                    <th class="text-sm">Creato Il</th>
                    <th class="text-sm">Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($service_items as $item): ?>
                    <tr>
                        <td class="text-xs"><?php echo htmlspecialchars($item['id']); ?></td>
                        <td class="text-xs"><?php echo htmlspecialchars($item['name']); ?></td>
                        <td class="text-xs"><?php echo '&euro; ' . number_format(htmlspecialchars($item['default_cost']), 2, ',', '.'); ?></td>
                        <td class="text-xs max-w-xs truncate" title="<?php echo htmlspecialchars($item['description']); ?>">
                            <?php echo htmlspecialchars($item['description'] ?? 'N/D'); ?>
                        </td>
                        <td class="text-xs text-center">
                            <?php if ($item['is_active']): ?>
                                <i class="fas fa-check-circle text-green-500 text-lg" title="Attivo"></i>
                            <?php else: ?>
                                <i class="fas fa-times-circle text-red-500 text-lg" title="Inattivo"></i>
                            <?php endif; ?>
                        </td>
                        <td class="text-xs"><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($item['created_at']))); ?></td>
                        <td class="whitespace-nowrap text-xs">
                            <a href="index.php?page=repair_services&action=edit&id=<?php echo $item['id']; ?>" title="Modifica Intervento" class="text-yellow-600 hover:text-yellow-800 p-2 rounded-full hover:bg-yellow-100 transition duration-150 ease-in-out ml-1">
                                <i class="fas fa-edit text-lg"></i>
                            </a>
                            <a href="index.php?page=repair_services&action=delete&id=<?php echo $item['id']; ?>" title="Elimina Intervento" class="text-red-600 hover:text-red-800 p-2 rounded-full hover:bg-red-100 transition duration-150 ease-in-out ml-1" onclick="return confirm('Sei sicuro di voler eliminare questo intervento?');">
                                <i class="fas fa-trash-alt text-lg"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <p class="text-gray-600">Nessun intervento di servizio trovato. <a href="index.php?page=repair_services&action=add" class="text-blue-600 hover:underline">Aggiungine uno nuovo!</a></p>
<?php endif; ?>
