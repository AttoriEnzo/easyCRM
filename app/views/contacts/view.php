<?php
// app/views/contacts/view.php

// Le variabili $contact, $interactions, e $addresses sono passate dal ContactController->view()

// Assicurati che $contact e $interactions siano definiti
if (!isset($contact) || $contact === null) {
    echo "<p class='flash-error'>Dati contatto non disponibili.</p>";
    return;
}

// Recupera il ruolo e l'ID dell'utente corrente dalla sessione per i controlli di visibilità
$current_user_role = $_SESSION['role'] ?? null;
$current_user_id = $_SESSION['user_id'] ?? null;

// Funzione helper per determinare se l'utente ha permesso di gestire indirizzi
$canManageAddresses = in_array($current_user_role, ['admin', 'superadmin']);
?>

<h2 class="text-2xl font-semibold mb-4">Dettagli Contatto: <?php echo htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']); ?></h2>

<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
        <!-- COLONNA DI SINISTRA: Informazioni di Contatto Base -->
        <div>
            <h3 class="text-xl font-semibold mb-3 text-indigo-700">Informazioni Primarie</h3>
            <p class="mb-2"><span class="font-semibold">Nome:</span> <?php echo htmlspecialchars($contact['first_name']); ?></p>
            <p class="mb-2"><span class="font-semibold">Cognome:</span> <?php echo htmlspecialchars($contact['last_name']); ?></p>
            <p class="mb-2"><span class="font-semibold">Email:</span> <?php echo htmlspecialchars($contact['email']); ?></p>
            <p class="mb-2"><span class="font-semibold">Telefono Fisso:</span> <?php echo htmlspecialchars($contact['phone'] ?? 'N/D'); ?></p>
            <p class="mb-2"><span class="font-semibold">Telefono Cellulare:</span> <?php echo htmlspecialchars($contact['mobile_phone'] ?? 'N/D'); ?></p>
            <p class="mb-2"><span class="font-semibold">PEC:</span> <?php echo htmlspecialchars($contact['pec'] ?? 'N/D'); ?></p>
            <p class="mb-2"><span class="font-semibold">Azienda:</span> <?php echo htmlspecialchars($contact['company']); ?></p>
        </div>

        <!-- COLONNA DI DESTRA: Dettagli Specifici e Amministrativi -->
        <div>
            <h3 class="text-xl font-semibold mb-3 text-indigo-700">Dettagli Aggiuntivi</h3>
            <p class="mb-2"><span class="font-semibold">Tipo Cliente:</span> <?php echo htmlspecialchars($contact['client_type'] ?? 'N/D'); ?></p>
            <p class="mb-2"><span class="font-semibold">Codice Fiscale:</span> <?php echo htmlspecialchars($contact['tax_code'] ?? 'N/D'); ?></p>
            <p class="mb-2"><span class="font-semibold">Partita IVA:</span> <?php echo htmlspecialchars($contact['vat_number'] ?? 'N/D'); ?></p>
            <p class="mb-2"><span class="font-semibold">Codice SDI:</span> <?php echo htmlspecialchars($contact['sdi'] ?? 'N/D'); ?></p>
            <p class="mb-2"><span class="font-semibold">Indirizzo Azienda:</span> <?php echo htmlspecialchars($contact['company_address'] ?? 'N/D'); ?></p>
            <p class="mb-2"><span class="font-semibold">Città Azienda:</span> <?php echo htmlspecialchars($contact['company_city'] ?? 'N/D'); ?></p>
            <p class="mb-2"><span class="font-semibold">CAP Azienda:</span> <?php echo htmlspecialchars($contact['company_zip'] ?? 'N/D'); ?></p>
            <p class="mb-2"><span class="font-semibold">Provincia Azienda:</span> <?php echo htmlspecialchars($contact['company_province'] ?? 'N/D'); ?></p>
            <p class="mb-2"><span class="font-semibold">Ultimo Contatto:</span> <?php echo $contact['last_contact_date'] ? date('d/m/Y', strtotime($contact['last_contact_date'])) : 'N/D'; ?></p>
            <p class="mb-2"><span class="font-semibold">Mezzo Contatto:</span> <?php echo htmlspecialchars($contact['contact_medium'] ?? 'N/D'); ?></p>
            <p class="mb-2"><span class="font-semibold">Ordine Eseguito:</span> <?php echo ($contact['order_executed'] == 1) ? 'Sì' : 'No'; ?></p>
            <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Creato il:</span> <?php echo date('d/m/Y H:i', strtotime($contact['created_at'])); ?></p>
        </div>
    </div>
    <div class="mt-6 flex space-x-2">
        <a href="index.php?page=contacts&action=edit&id=<?php echo htmlspecialchars($contact['id']); ?>" class="btn btn-secondary">Modifica Contatto</a>
        <a href="index.php?page=contacts" class="btn btn-tertiary">Torna ai Contatti</a>
    </div>
</div>

<h3 class="text-xl font-semibold mt-6 mb-4">Indirizzi Associati</h3>

<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <?php if ($canManageAddresses): // Mostra il pulsante solo se l'utente ha i permessi ?>
        <a href="index.php?page=contacts&action=add_address&contact_id=<?php echo htmlspecialchars($contact['id']); ?>" class="btn btn-primary mb-4">
            Aggiungi Nuovo Indirizzo
        </a>
    <?php endif; ?>

    <?php if (isset($addresses) && count($addresses) > 0): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Indirizzo</th>
                        <th>Città</th>
                        <th>CAP</th>
                        <th>Provincia</th>
                        <th>Predefinito Spedizione</th>
                        <?php if ($canManageAddresses): ?>
                            <th>Azioni</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($addresses as $address): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($address['address_type']); ?></td>
                            <td><?php echo htmlspecialchars($address['address']); ?></td>
                            <td><?php echo htmlspecialchars($address['city']); ?></td>
                            <td><?php echo htmlspecialchars($address['zip']); ?></td>
                            <td><?php echo htmlspecialchars($address['province']); ?></td>
                            <td><?php echo ($address['is_default_shipping'] == 1) ? 'Sì' : 'No'; ?></td>
                            <?php if ($canManageAddresses): ?>
                                <td class="whitespace-nowrap">
                                    <a href="index.php?page=contacts&action=edit_address&id=<?php echo htmlspecialchars($address['id']); ?>&contact_id=<?php echo htmlspecialchars($contact['id']); ?>" title="Modifica Indirizzo" class="text-yellow-600 hover:text-yellow-800 p-2 rounded-full hover:bg-yellow-100 transition duration-150 ease-in-out">
                                        <i class="fas fa-edit text-lg"></i>
                                    </a>
                                    <a href="index.php?page=contacts&action=delete_address&id=<?php echo htmlspecialchars($address['id']); ?>&contact_id=<?php echo htmlspecialchars($contact['id']); ?>" title="Elimina Indirizzo" class="text-red-600 hover:text-red-800 p-2 rounded-full hover:bg-red-100 transition duration-150 ease-in-out ml-1" onclick="return confirm('Sei sicuro di voler eliminare questo indirizzo?');">
                                        <i class="fas fa-trash-alt text-lg"></i>
                                    </a>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-gray-600">Nessun indirizzo associato a questo contatto.</p>
    <?php endif; ?>
</div>

<h3 class="text-xl font-semibold mt-6 mb-4">Interazioni</h3>

<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <h4 class="text-lg font-medium mb-3">Aggiungi Nuova Interazione</h4>
    <form method="POST" action="index.php?page=contacts&action=view&id=<?php echo htmlspecialchars($contact['id']); ?>">
        <input type="hidden" name="add_interaction" value="1">
        <label for="interaction_date" class="block text-gray-700 text-sm font-bold mb-2">Data Interazione:</label>
        <input type="date" id="interaction_date" name="interaction_date" value="<?php echo date('Y-m-d'); ?>" required>

        <label for="type" class="block text-gray-700 text-sm font-bold mb-2">Tipo:</label>
        <select id="type" name="type" required>
            <option value="">Seleziona Tipo</option>
            <option value="Chiamata">Chiamata</option>
            <option value="Email">Email</option>
            <option value="Meeting">Meeting</option>
            <option value="Messaggio">Messaggio</option>
            <option value="Altro">Altro</option>
        </select>

        <label for="notes" class="block text-gray-700 text-sm font-bold mb-2">Note:</label>
        <textarea id="notes" name="notes" rows="4"></textarea>

        <button type="submit" class="btn btn-primary mt-4">Aggiungi Interazione</button>
    </form>
</div>

<?php if (isset($interactions) && count($interactions) > 0): ?>
    <div class="overflow-x-auto bg-white p-6 rounded-lg shadow-md">
        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Data</th>
                    <th>Tipo</th>
                    <th>Note</th>
                    <th>Creato da</th> <!-- NUOVA COLONNA: Chi ha creato l'interazione -->
                    <th>Creato il</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($interactions as $interaction): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($interaction['id']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($interaction['interaction_date'])); ?></td>
                        <td><?php echo htmlspecialchars($interaction['type']); ?></td>
                        <td><?php echo htmlspecialchars(substr($interaction['notes'], 0, 100)); ?><?php echo strlen($interaction['notes']) > 100 ? '...' : ''; ?></td>
                        <td><?php echo htmlspecialchars($interaction['user_username'] ?? 'N/D'); ?></td> <!-- Visualizza lo username -->
                        <td><?php echo date('d/m/Y H:i', strtotime($interaction['created_at'])); ?></td>
                        <td>
                            <?php 
                            // Logica per mostrare il pulsante "Elimina"
                            // Admin e Superadmin possono eliminare qualsiasi interazione.
                            // Tecnici e Commerciali possono eliminare SOLO le proprie interazioni.
                            if ($current_user_role === 'admin' || $current_user_role === 'superadmin' || 
                                ($current_user_id !== null && $interaction['user_id'] === $current_user_id && 
                                ($current_user_role === 'tecnico' || $current_user_role === 'commerciale'))
                            ): ?>
                                <a href="index.php?page=contacts&action=delete_interaction&id=<?php echo htmlspecialchars($interaction['id']); ?>&contact_id=<?php echo htmlspecialchars($contact['id']); ?>" class="text-red-600 hover:text-red-800 p-1 rounded-full hover:bg-red-100 transition duration-150 ease-in-out" onclick="return confirm('Sei sicuro di voler eliminare questa interazione?');" title="Elimina Interazione">
                                    <i class="fa-solid fa-trash-can fa-lg"></i>
                                </a>
                            <?php else: ?>
                                <span class="text-gray-400 opacity-50 p-1 cursor-not-allowed" title="Non hai i permessi per eliminare questa interazione">
                                    <i class="fa-solid fa-trash-can fa-lg"></i>
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <p class="text-gray-600">Nessuna interazione registrata per questo contatto.</p>
<?php endif; ?>

<div class="mt-8">
    <a href="index.php?page=contacts" class="btn btn-secondary">Torna ai Contatti</a>
</div>
