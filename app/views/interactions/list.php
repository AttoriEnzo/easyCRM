<?php
// app/views/interactions/list.php

// Le variabili $interactions e $search_query sono passate dal ContactController->globalIndex()

// Recupera il ruolo e l'ID dell'utente corrente dalla sessione per i controlli di visibilità
$current_user_role = $_SESSION['role'] ?? null;
$current_user_id = $_SESSION['user_id'] ?? null;
?>

<h2 class="text-2xl font-semibold mb-4">Elenco Globale Interazioni</h2>

<div class="flex flex-col md:flex-row justify-between items-center mb-4">
    <!-- Non c'è un pulsante "Aggiungi Interazione" qui, perché si aggiungono dalla scheda contatto -->
    <div class="mb-2 md:mb-0">
        <a href="index.php?page=contacts" class="btn btn-secondary">Torna ai Contatti</a>
    </div>
    <form method="GET" class="w-full md:w-auto flex items-center">
        <input type="hidden" name="page" value="interactions">
        <input type="text" name="q" placeholder="Cerca interazioni..." value="<?php echo htmlspecialchars($search_query); ?>" class="w-full md:w-64 mr-2">
        <button type="submit" class="btn btn-secondary flex items-center justify-center">
            <i class="fas fa-search mr-1"></i> Cerca
        </button>
    </form>
</div>

<?php if (isset($interactions) && count($interactions) > 0): ?>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-lg shadow-sm">
            <thead>
                <tr>
                    <th class="text-sm">ID</th>
                    <th class="text-sm">Contatto</th>
                    <th class="text-sm">Data</th>
                    <th class="text-sm">Tipo</th>
                    <th class="text-sm">Note</th>
                    <th class="text-sm">Creato da</th>
                    <th class="text-sm">Creato il</th>
                    <th class="text-sm">Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($interactions as $interaction): ?>
                    <tr>
                        <td class="text-xs"><?php echo htmlspecialchars($interaction['id']); ?></td>
                        <td class="text-xs">
                            <a href="index.php?page=contacts&action=view&id=<?php echo htmlspecialchars($interaction['contact_id']); ?>" class="text-blue-600 hover:underline">
                                <?php echo htmlspecialchars($interaction['company'] ?: $interaction['first_name'] . ' ' . $interaction['last_name']); ?>
                            </a>
                        </td>
                        <td class="text-xs"><?php echo date('d/m/Y', strtotime($interaction['interaction_date'])); ?></td>
                        <td class="text-xs"><?php echo htmlspecialchars($interaction['type']); ?></td>
                        <td class="text-xs"><?php echo htmlspecialchars(substr($interaction['notes'], 0, 100)); ?><?php echo strlen($interaction['notes']) > 100 ? '...' : ''; ?></td>
                        <td class="text-xs"><?php echo htmlspecialchars($interaction['user_username'] ?? 'N/D'); ?></td>
                        <td class="text-xs"><?php echo date('d/m/Y H:i', strtotime($interaction['created_at'])); ?></td>
                        <td class="whitespace-nowrap text-xs">
                            <a href="index.php?page=contacts&action=view&id=<?php echo htmlspecialchars($interaction['contact_id']); ?>" title="Vedi Contatto" class="text-blue-600 hover:text-blue-800 p-2 rounded-full hover:bg-blue-100 transition duration-150 ease-in-out">
                                <i class="fas fa-eye text-lg"></i>
                            </a>
                            <?php 
                            // Logica per mostrare il pulsante "Elimina"
                            // Admin e Superadmin possono eliminare qualsiasi interazione.
                            // Tecnici e Commerciali possono eliminare SOLO le proprie interazioni.
                            if ($current_user_role === 'admin' || $current_user_role === 'superadmin' || 
                                ($current_user_id !== null && $interaction['user_id'] === $current_user_id && 
                                ($current_user_role === 'tecnico' || $current_user_role === 'commerciale'))
                            ): ?>
                                <a href="index.php?page=contacts&action=delete_interaction&id=<?php echo htmlspecialchars($interaction['id']); ?>&contact_id=<?php echo htmlspecialchars($interaction['contact_id']); ?>" class="text-red-600 hover:text-red-800 p-2 rounded-full hover:bg-red-100 transition duration-150 ease-in-out ml-1" onclick="return confirm('Sei sicuro di voler eliminare questa interazione?');" title="Elimina Interazione">
                                    <i class="fas fa-trash-alt text-lg"></i>
                                </a>
                            <?php else: ?>
                                <span class="text-gray-400 opacity-50 p-2 cursor-not-allowed ml-1" title="Non hai i permessi per eliminare questa interazione">
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
    <p class="text-gray-600">Nessuna interazione trovata.</p>
<?php endif; ?>
