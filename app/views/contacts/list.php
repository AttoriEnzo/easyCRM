<?php
// app/views/contacts/list.php

// Le variabili $contacts e $search_query sono passate dal ContactController->index()

?>

<h2 class="text-2xl font-semibold mb-4">Elenco Contatti</h2>
<div class="flex flex-col md:flex-row justify-between items-center mb-4">
    <div class="flex space-x-2 mb-2 md:mb-0">
        <a href="index.php?page=contacts&action=add" class="btn btn-primary">Aggiungi Contatto</a>
        <!-- I pulsanti Esporta e Importa sono stati spostati sulla Dashboard -->
    </div>
    <form method="GET" class="w-full md:w-auto flex items-center">
        <input type="hidden" name="page" value="contacts">
        <input type="text" name="q" placeholder="Cerca contatti..." value="<?php echo htmlspecialchars($search_query); ?>" class="w-full md:w-64 mr-2">
        <button type="submit" class="btn btn-secondary flex items-center justify-center">
            <i class="fas fa-search mr-1"></i> Cerca
        </button>
    </form>
</div>

<?php if (isset($contacts) && count($contacts) > 0): ?>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-lg shadow-sm">
            <thead>
                <tr>
                    <th class="text-sm">ID</th>
                    <th class="text-sm">Nome</th>
                    <th class="text-sm">Cognome</th>
                    <th class="text-sm">Azienda</th>
                    <th class="text-sm">Telefono Fisso</th>
                    <th class="text-sm">Telefono Cellulare</th>
                    <th class="text-sm">Email</th>
                    <th class="text-sm">Data Ultimo Contatto</th> <!-- NUOVO CAMPO -->
                    <th class="text-sm">Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contacts as $contact): ?>
                    <tr>
                        <td class="text-xs"><?php echo htmlspecialchars($contact['id']); ?></td>
                        <td class="text-xs"><?php echo htmlspecialchars($contact['first_name']); ?></td>
                        <td class="text-xs"><?php echo htmlspecialchars($contact['last_name']); ?></td>
                        <td class="text-xs"><?php echo htmlspecialchars($contact['company'] ?? 'N/D'); ?></td>
                        <td class="text-xs"><?php echo htmlspecialchars($contact['phone'] ?? 'N/D'); ?></td>
                        <td class="text-xs"><?php echo htmlspecialchars($contact['mobile_phone'] ?? 'N/D'); ?></td>
                        <td class="text-xs"><?php echo htmlspecialchars($contact['email'] ?? 'N/D'); ?></td>
                        <td class="text-xs"><?php echo $contact['last_contact_date'] ? date('d/m/Y', strtotime($contact['last_contact_date'])) : 'N/D'; ?></td> <!-- NUOVO CAMPO -->
                        <td class="whitespace-nowrap text-xs">
                            <a href="index.php?page=contacts&action=view&id=<?php echo $contact['id']; ?>" title="Vedi Dettagli" class="text-blue-600 hover:text-blue-800 p-2 rounded-full hover:bg-blue-100 transition duration-150 ease-in-out">
                                <i class="fas fa-eye text-lg"></i>
                            </a>
                            <a href="index.php?page=contacts&action=edit&id=<?php echo $contact['id']; ?>" title="Modifica Contatto" class="text-yellow-600 hover:text-yellow-800 p-2 rounded-full hover:bg-yellow-100 transition duration-150 ease-in-out ml-1">
                                <i class="fas fa-edit text-lg"></i>
                            </a>
                            <a href="index.php?page=contacts&action=delete&id=<?php echo $contact['id']; ?>" title="Elimina Contatto" class="text-red-600 hover:text-red-800 p-2 rounded-full hover:bg-red-100 transition duration-150 ease-in-out ml-1" onclick="return confirm('Sei sicuro di voler eliminare questo contatto?');">
                                <i class="fas fa-trash-alt text-lg"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <p class="text-gray-600">Nessun contatto trovato. <a href="index.php?page=contacts&action=add" class="text-blue-600 hover:underline">Aggiungine uno nuovo!</a></p>
<?php endif; ?>
