<?php
// app/views/repairs/view.php

// Le variabili $repair e $interactions sono passate dal RepairController->view()

// Recupera il ruolo dell'utente corrente dalla sessione per i controlli di visibilità
$current_user_role = $_SESSION['role'] ?? null;
?>

<h2 class="text-2xl font-semibold mb-4">Dettagli Riparazione #<?php echo htmlspecialchars($repair['id']); ?></h2>

<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <h3 class="text-xl font-semibold mb-4 border-b pb-2">Dati Riparazione</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <p class="mb-2"><strong class="font-medium">Cliente:</strong> 
                <a href="index.php?page=contacts&action=view&id=<?php echo htmlspecialchars($repair['contact_id']); ?>" class="text-blue-600 hover:underline">
                    <?php echo htmlspecialchars($repair['company'] ?: $repair['first_name'] . ' ' . $repair['last_name']); ?>
                </a>
            </p>
            <p class="mb-2"><strong class="font-medium">Tipo Dispositivo:</strong> <?php echo htmlspecialchars($repair['device_type'] ?? 'N/D'); ?></p>
            <p class="mb-2"><strong class="font-medium">Marca:</strong> <?php echo htmlspecialchars($repair['brand'] ?? 'N/D'); ?></p>
            <p class="mb-2"><strong class="font-medium">Modello:</strong> <?php echo htmlspecialchars($repair['model'] ?? 'N/D'); ?></p>
            <p class="mb-2"><strong class="font-medium">Matricola:</strong> <?php echo htmlspecialchars($repair['serial_number'] ?? 'N/D'); ?></p>
            <p class="mb-2"><strong class="font-medium">Data Arrivo:</strong> <?php echo $repair['reception_date'] ? date('d/m/Y', strtotime($repair['reception_date'])) : 'N/D'; ?></p>
            <p class="mb-2"><strong class="font-medium">Creato da:</strong> <?php echo htmlspecialchars($repair['user_username'] ?? 'N/D'); ?></p>
        </div>
        <div>
            <p class="mb-2"><strong class="font-medium">Descrizione Problema:</strong> <?php echo nl2br(htmlspecialchars($repair['problem_description'] ?? 'N/D')); ?></p>
            <p class="mb-2"><strong class="font-medium">Accessori:</strong> <?php echo nl2br(htmlspecialchars($repair['accessories'] ?? 'N/D')); ?></p>
            <p class="mb-2"><strong class="font-medium">Numero DDT:</strong> <?php echo htmlspecialchars($repair['ddt_number'] ?? 'N/D'); ?></p>
            <p class="mb-2"><strong class="font-medium">Data DDT:</strong> <?php echo $repair['ddt_date'] ? date('d/m/Y', strtotime($repair['ddt_date'])) : 'N/D'; ?></p>
            <p class="mb-2"><strong class="font-medium">Stato:</strong> 
                <span class="px-2 py-1 rounded-md text-sm font-semibold 
                    <?php 
                        // Classi CSS per il colore dello stato
                        if ($repair['status'] == 'Completata' || $repair['status'] == 'Ritirata') echo 'bg-green-100 text-green-800';
                        else if ($repair['status'] == 'In Lavorazione' || $repair['status'] == 'In Test') echo 'bg-blue-100 text-blue-800';
                        else if ($repair['status'] == 'Ricambi Ordinati') echo 'bg-yellow-100 text-yellow-800';
                        else if ($repair['status'] == 'Annullata') echo 'bg-red-100 text-red-800';
                        else echo 'bg-gray-100 text-gray-800';
                    ?>
                ">
                    <?php echo htmlspecialchars($repair['status'] ?? 'N/D'); ?>
                </span>
            </p>
            <p class="mb-2"><strong class="font-medium">Note Tecnico:</strong> <?php echo nl2br(htmlspecialchars($repair['technician_notes'] ?? 'N/D')); ?></p>
            <p class="mb-2"><strong class="font-medium">Costo Stimato:</strong> <?php echo $repair['estimated_cost'] !== null ? '&euro; ' . number_format($repair['estimated_cost'], 2, ',', '.') : 'N/D'; ?></p>
            <p class="mb-2"><strong class="font-medium">Data Termine Lavori:</strong> <?php echo $repair['completion_date'] ? date('d/m/Y', strtotime($repair['completion_date'])) : 'N/D'; ?></p>
            <p class="mb-2"><strong class="font-medium">Data Spedizione:</strong> <?php echo $repair['shipping_date'] ? date('d/m/Y', strtotime($repair['shipping_date'])) : 'N/D'; ?></p>
            <p class="mb-2"><strong class="font-medium">Codice Tracking:</strong> <?php echo htmlspecialchars($repair['tracking_code'] ?? 'N/D'); ?></p>
        </div>
    </div>
    
    <div class="mt-6 flex space-x-2">
        <a href="index.php?page=repairs" class="btn btn-secondary">Torna all'Elenco</a>
        <?php 
        // I pulsanti Modifica sono visibili solo per Superadmin, Admin e Tecnici
        if (in_array($current_user_role, ['admin', 'superadmin', 'tecnico'])): 
        ?>
            <a href="index.php?page=repairs&action=edit&id=<?php echo htmlspecialchars($repair['id']); ?>" class="btn btn-primary">Modifica</a>
        <?php endif; ?>
    </div>
</div>

---

<?php if (!empty($interactions) || in_array($current_user_role, ['admin', 'superadmin', 'tecnico', 'commerciale'])): ?>
<div class="bg-white p-6 rounded-lg shadow-md mt-6">
    <h3 class="text-xl font-semibold mb-4 border-b pb-2">Interazioni del Contatto (<?php echo htmlspecialchars($repair['company'] ?: $repair['first_name'] . ' ' . $repair['last_name']); ?>)</h3>
    <?php if (!empty($interactions)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg shadow-sm">
                <thead>
                    <tr>
                        <th class="text-sm">Data</th>
                        <th class="text-sm">Tipo</th>
                        <th class="text-sm">Note</th>
                        <th class="text-sm">Creato da</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($interactions as $interaction): ?>
                        <tr>
                            <td class="text-xs"><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($interaction['interaction_date']))); ?></td>
                            <td class="text-xs"><?php echo htmlspecialchars($interaction['type']); ?></td>
                            <td class="text-xs max-w-xs truncate" title="<?php echo htmlspecialchars($interaction['notes']); ?>">
                                <?php echo htmlspecialchars($interaction['notes']); ?>
                            </td>
                            <td class="text-xs"><?php echo htmlspecialchars($interaction['user_username'] ?? 'N/D'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-gray-600">Nessuna interazione trovata per questo contatto.</p>
    <?php endif; ?>
    <div class="mt-4">
        <?php if (in_array($current_user_role, ['admin', 'superadmin', 'tecnico', 'commerciale'])): ?>
            <a href="index.php?page=contacts&action=view&id=<?php echo htmlspecialchars($repair['contact_id']); ?>#add-interaction-section" class="btn btn-primary">Aggiungi Nuova Interazione</a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
