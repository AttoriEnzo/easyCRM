<?php
// app/views/users/list.php

// Le variabili $users e $search_query sono passate dal UserController->index()

?>

<h2 class="text-2xl font-semibold mb-4">Gestione Utenti</h2>
<div class="flex flex-col md:flex-row justify-between items-center mb-4">
    <div class="flex space-x-2 mb-2 md:mb-0">
        <a href="index.php?page=users&action=add" class="btn btn-primary">Aggiungi Utente</a>
    </div>
    <form method="GET" class="w-full md:w-auto flex items-center">
        <input type="hidden" name="page" value="users">
        <input type="text" name="q" placeholder="Cerca utenti..." value="<?php echo htmlspecialchars($search_query); ?>" class="w-full md:w-64 mr-2">
        <button type="submit" class="btn btn-secondary flex items-center justify-center">
            <i class="fas fa-search mr-1"></i> Cerca
        </button>
    </form>
</div>

<?php if (isset($users) && count($users) > 0): ?>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-lg shadow-sm">
            <thead>
                <tr>
                    <th class="text-sm">ID</th>
                    <th class="text-sm">Username</th>
                    <th class="text-sm">Ruolo</th>
                    <th class="text-sm">Creato Il</th>
                    <th class="text-sm">Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="text-xs"><?php echo htmlspecialchars($user['id']); ?></td>
                        <td class="text-xs"><?php echo htmlspecialchars($user['username']); ?></td>
                        <td class="text-xs"><?php echo htmlspecialchars($user['role']); ?></td>
                        <td class="text-xs"><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($user['created_at']))); ?></td>
                        <td class="whitespace-nowrap text-xs">
                            <a href="index.php?page=users&action=edit&id=<?php echo $user['id']; ?>" title="Modifica Utente" class="text-yellow-600 hover:text-yellow-800 p-2 rounded-full hover:bg-yellow-100 transition duration-150 ease-in-out ml-1">
                                <i class="fas fa-edit text-lg"></i>
                            </a>
                            <a href="index.php?page=users&action=delete&id=<?php echo $user['id']; ?>" title="Elimina Utente" class="text-red-600 hover:text-red-800 p-2 rounded-full hover:bg-red-100 transition duration-150 ease-in-out ml-1" onclick="return confirm('Sei sicuro di voler eliminare questo utente?');">
                                <i class="fas fa-trash-alt text-lg"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <p class="text-gray-600">Nessun utente trovato. <a href="index.php?page=users&action=add" class="text-blue-600 hover:underline">Aggiungine uno nuovo!</a></p>
<?php endif; ?>
