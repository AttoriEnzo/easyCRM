<?php
// app/views/dashboard.php

// Le variabili $total_contacts, $recent_interactions e $recent_repairs sono passate dal DashboardController->index()

// Nota: header.php e footer.php sono inclusi in public/index.php
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-indigo-50 p-6 rounded-lg shadow-sm flex flex-col justify-center items-center text-center">
        <h2 class="text-xl font-semibold text-indigo-700 mb-3">Totale Contatti</h2>
        <p class="text-5xl font-bold text-indigo-900"><?php echo htmlspecialchars($total_contacts); ?></p>
        <a href="index.php?page=contacts" class="mt-4 btn btn-secondary btn-sm">Gestisci Contatti</a>
    </div>

    <div class="bg-green-50 p-6 rounded-lg shadow-sm flex flex-col">
        <h2 class="text-xl font-semibold text-green-700 mb-3">Interazioni Recenti</h2>
        <?php if (isset($recent_interactions) && count($recent_interactions) > 0): ?>
            <ul class="list-disc pl-5 text-gray-700 flex-grow">
                <?php foreach ($recent_interactions as $interaction): ?>
                    <li class="mb-1 text-sm">
                        <span class="font-medium"><?php echo htmlspecialchars($interaction['type']); ?></span> con <a href="index.php?page=contacts&action=view&id=<?php echo htmlspecialchars($interaction['contact_id']); ?>" class="text-blue-600 hover:underline"><?php echo htmlspecialchars($interaction['company'] ?: $interaction['first_name'] . ' ' . $interaction['last_name']); ?></a> il <?php echo date('d/m/Y', strtotime($interaction['interaction_date'])); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <a href="index.php?page=interactions" class="mt-4 btn btn-secondary btn-sm w-full">Vedi Tutte le Interazioni</a>
        <?php else: ?>
            <p class="text-gray-600 flex-grow">Nessuna interazione recente.</p>
        <?php endif; ?>
    </div>

    <!-- Sezione Riparazioni Recenti -->
    <div class="bg-yellow-50 p-6 rounded-lg shadow-sm flex flex-col">
        <h2 class="text-xl font-semibold text-yellow-700 mb-3">Riparazioni Recenti</h2>
        <?php if (isset($recent_repairs) && count($recent_repairs) > 0): ?>
            <ul class="list-disc pl-5 text-gray-700 flex-grow">
                <?php foreach ($recent_repairs as $repair): ?>
                    <li class="mb-1 text-sm">
                        <!-- MODIFICATO: Esteso il link per includere "Riparazione #" -->
                        <a href="index.php?page=repairs&action=view&id=<?php echo htmlspecialchars($repair['id']); ?>" class="text-blue-600 hover:underline">
                            Riparazione #<?php echo htmlspecialchars($repair['id']); ?>
                        </a>
                        per <?php echo htmlspecialchars($repair['company'] ?: $repair['first_name'] . ' ' . $repair['last_name']); ?> 
                        <!-- MODIFICATO: Rimosso "Stato:" -->
                        (<?php echo htmlspecialchars($repair['status']); ?>)
                    </li>
                <?php endforeach; ?>
            </ul>
            <a href="index.php?page=repairs" class="mt-4 btn btn-secondary btn-sm w-full">Vedi Tutte le Riparazioni</a>
        <?php else: ?>
            <p class="text-gray-600 flex-grow">Nessuna riparazione recente.</p>
        <?php endif; ?>
    </div>
</div>

<div class="text-center mt-8 space-x-4">
    <a href="index.php?page=contacts&action=add" class="btn btn-primary">Aggiungi Nuovo Contatto</a>
    <a href="index.php?page=contacts&action=export" class="btn btn-secondary">Esporta Contatti</a>
    <a href="index.php?page=contacts&action=import" class="btn btn-secondary">Importa Contatti</a>
</div>
