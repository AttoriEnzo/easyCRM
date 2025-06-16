<?php
// app/views/repairs/list.php

// Le variabili $repairs e $search_query sono passate dal RepairController->index()

// Array degli stati della riparazione per il dropdown (replica da repair_form.php per coerenza)
$repair_statuses = ['In Attesa', 'In Lavorazione', 'Ricambi Ordinati', 'In Test', 'Completata', 'Annullata', 'Ritirata'];

// Recupera il ruolo dell'utente corrente dalla sessione per i controlli di visibilità
$current_user_role = $_SESSION['role'] ?? null;

// Nota: header.php e footer.php sono inclusi in public/index.php
?>

<h2 class="text-2xl font-semibold mb-4">Elenco Riparazioni</h2>

<div class="flex flex-col md:flex-row justify-between items-center mb-4">
    <?php 
    // Il pulsante "Aggiungi Riparazione" è visibile per Superadmin, Admin e Tecnici
    if (in_array($current_user_role, ['admin', 'superadmin', 'tecnico'])): 
    ?>
        <a href="index.php?page=repairs&action=add" class="btn btn-primary mb-2 md:mb-0">Aggiungi Riparazione</a>
    <?php else: ?>
        <div class="mb-2 md:mb-0"></div> <!-- Mantiene lo spazio per l'allineamento -->
    <?php endif; ?>
    <form method="GET" class="w-full md:w-auto flex items-center">
        <input type="hidden" name="page" value="repairs">
        <input type="text" name="q" placeholder="Cerca riparazioni..." value="<?php echo htmlspecialchars($search_query); ?>" class="w-full md:w-64 mr-2">
        <button type="submit" class="btn btn-secondary flex items-center justify-center">
            <i class="fas fa-search mr-1"></i> Cerca
        </button>
    </form>
</div>

<?php if (isset($repairs) && count($repairs) > 0): ?>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-lg shadow-sm">
            <thead>
                <tr>
                    <th class="text-sm">ID</th>
                    <th class="text-sm">Cliente</th>
                    <th class="text-sm">Dispositivo</th>
                    <th class="text-sm">Matricola</th>
                    <th class="text-sm">Stato</th> <!-- Questa colonna diventerà un dropdown -->
                    <th class="text-sm">Data Arrivo</th>
                    <th class="text-sm">Creato da</th> <!-- NUOVA COLONNA -->
                    <th class="text-sm">Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($repairs as $repair): ?>
                    <tr id="repair-row-<?php echo htmlspecialchars($repair['id']); ?>">
                        <td class="text-xs"><?php echo htmlspecialchars($repair['id']); ?></td>
                        <td class="text-xs">
                            <?php 
                            echo htmlspecialchars($repair['company'] ?: $repair['first_name'] . ' ' . $repair['last_name']); 
                            ?>
                        </td>
                        <td class="text-xs"><?php echo htmlspecialchars($repair['brand'] . ' ' . $repair['model'] . ' (' . $repair['device_type'] . ')'); ?></td>
                        <td class="text-xs"><?php echo htmlspecialchars($repair['serial_number'] ?? 'N/D'); ?></td>
                        <td class="text-xs relative">
                            <select 
                                class="repair-status-select px-2 py-1 rounded-md text-xs font-semibold w-full 
                                <?php 
                                    // Classi CSS per il colore del dropdown
                                    if ($repair['status'] == 'Completata' || $repair['status'] == 'Ritirata') echo 'bg-green-100 text-green-800';
                                    else if ($repair['status'] == 'In Lavorazione' || $repair['status'] == 'In Test') echo 'bg-blue-100 text-blue-800';
                                    else if ($repair['status'] == 'Ricambi Ordinati') echo 'bg-yellow-100 text-yellow-800';
                                    else if ($repair['status'] == 'Annullata') echo 'bg-red-100 text-red-800';
                                    else echo 'bg-gray-100', 'text-gray-800';
                                ?>"
                                data-repair-id="<?php echo htmlspecialchars($repair['id']); ?>"
                                <?php 
                                // Disabilita il dropdown per i ruoli 'commerciale' e 'user'
                                if ($current_user_role === 'commerciale' || $current_user_role === 'user') {
                                    echo 'disabled';
                                }
                                ?>
                            >
                                <?php foreach ($repair_statuses as $s): ?>
                                    <option value="<?php echo htmlspecialchars($s); ?>"
                                            <?php echo ($repair['status'] == $s) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($s); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="status-spinner absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 hidden">
                                <i class="fas fa-spinner fa-spin text-indigo-500"></i>
                            </span>
                        </td>
                        <td class="text-xs"><?php echo $repair['reception_date'] ? date('d/m/Y', strtotime($repair['reception_date'])) : 'N/D'; ?></td>
                        <td class="text-xs"><?php echo htmlspecialchars($repair['user_username'] ?? 'N/D'); ?></td> <!-- VISUALIZZA CHI HA CREATO LA RIPARAZIONE -->
                        <td class="whitespace-nowrap text-xs">
                            <a href="index.php?page=repairs&action=view&id=<?php echo $repair['id']; ?>" title="Vedi Dettagli" class="text-blue-600 hover:text-blue-800 p-2 rounded-full hover:bg-blue-100 transition duration-150 ease-in-out">
                                <i class="fas fa-eye text-lg"></i>
                            </a>
                            <?php 
                            // I pulsanti Modifica e Elimina sono visibili solo per Superadmin, Admin e Tecnici
                            if (in_array($current_user_role, ['admin', 'superadmin', 'tecnico'])): 
                            ?>
                                <a href="index.php?page=repairs&action=edit&id=<?php echo $repair['id']; ?>" title="Modifica Riparazione" class="text-yellow-600 hover:text-yellow-800 p-2 rounded-full hover:bg-yellow-100 transition duration-150 ease-in-out ml-1">
                                    <i class="fas fa-edit text-lg"></i>
                                </a>
                                <a href="index.php?page=repairs&action=delete&id=<?php echo $repair['id']; ?>" title="Elimina Riparazione" class="text-red-600 hover:text-red-800 p-2 rounded-full hover:bg-red-100 transition duration-150 ease-in-out ml-1" onclick="return confirm('Sei sicuro di voler eliminare questa riparazione?');">
                                    <i class="fas fa-trash-alt text-lg"></i>
                                </a>
                            <?php else: ?>
                                <!-- Icone disabilitate per utenti senza permesso di modifica/eliminazione -->
                                <span class="text-gray-400 opacity-50 p-2 cursor-not-allowed ml-1" title="Non hai i permessi per modificare questa riparazione">
                                    <i class="fas fa-edit text-lg"></i>
                                </span>
                                <span class="text-gray-400 opacity-50 p-2 cursor-not-allowed ml-1" title="Non hai i permessi per eliminare questa riparazione">
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
    <p class="text-gray-600">Nessuna riparazione trovata. 
    <?php if (in_array($current_user_role, ['admin', 'superadmin', 'tecnico'])): ?>
        <a href="index.php?page=repairs&action=add" class="text-blue-600 hover:underline">Aggiungine una nuova!</a>
    <?php endif; ?>
    </p>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelects = document.querySelectorAll('.repair-status-select');

    // Funzione per mostrare un messaggio flash personalizzato
    function showFlashMessage(message, type = 'info') {
        let currentFlashMessage = document.querySelector('.flash-message');
        if (currentFlashMessage) {
            currentFlashMessage.remove(); // Rimuovi il messaggio esistente
        }

        const newFlashMessage = document.createElement('div');
        newFlashMessage.classList.add('flash-message', `flash-${type}`, 'mb-4');
        newFlashMessage.textContent = message;
        
        // Inserisci il nuovo messaggio subito dopo l'h2
        const h2 = document.querySelector('h2.text-2xl');
        if (h2) {
            h2.after(newFlashMessage);
        } else {
            // Fallback se h2 non trovato
            document.querySelector('main.container').prepend(newFlashMessage);
        }

        // Rimuovi il messaggio dopo un certo tempo
        setTimeout(() => {
            newFlashMessage.remove();
        }, 5000); // 5 secondi
    }

    statusSelects.forEach(select => {
        // Aggiungi event listener solo se il select NON è disabilitato
        if (!select.disabled) {
            select.addEventListener('change', function() {
                const repairId = this.dataset.repairId;
                const newStatus = this.value;
                const spinner = this.nextElementSibling; // Lo spinner è il fratello successivo del select

                // Mostra lo spinner e disabilita il select
                spinner.classList.remove('hidden');
                this.disabled = true;

                // Rimuovi qualsiasi messaggio flash precedente specifico per questa azione
                let currentFlashMessage = document.querySelector('.flash-message');
                if (currentFlashMessage) {
                    currentFlashMessage.remove();
                }

                fetch('index.php?page=repairs&action=update_status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${encodeURIComponent(repairId)}&status=${encodeURIComponent(newStatus)}`
                })
                .then(response => {
                    // Controllo esplicito della risposta HTTP
                    if (!response.ok) {
                        // Se la risposta HTTP non è OK, cerchiamo di leggere il messaggio di errore dal JSON
                        // o forniamo un messaggio generico.
                        return response.json().then(errorData => {
                            throw new Error(errorData.message || 'Errore HTTP: ' + response.status);
                        }).catch(() => {
                            // Se non riusciamo a leggere il JSON, diamo un errore HTTP generico
                            throw new new Error('Errore HTTP non atteso: ' + response.status); // Correzione qui
                        });
                    }
                    // Se la risposta HTTP è OK, procedi con la lettura del JSON
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showFlashMessage(data.message, 'success');
                        // Aggiorna le classi CSS del dropdown per il colore in base al nuovo stato
                        this.className = ''; // Resetta tutte le classi
                        this.classList.add('repair-status-select', 'px-2', 'py-1', 'rounded-md', 'text-xs', 'font-semibold', 'w-full');
                        if (newStatus == 'Completata' || newStatus == 'Ritirata') this.classList.add('bg-green-100', 'text-green-800');
                        else if (newStatus == 'In Lavorazione' || newStatus == 'In Test') this.classList.add('bg-blue-100', 'text-blue-800');
                        else if (newStatus == 'Ricambi Ordinati') this.classList.add('bg-yellow-100', 'text-yellow-800');
                        else if (newStatus == 'Annullata') this.classList.add('bg-red-100', 'text-red-800');
                        else this.classList.add('bg-gray-100', 'text-gray-800');

                    } else {
                        // Questo gestisce gli errori logici inviati dal server (es. permessi negati, validazione)
                        showFlashMessage(data.message, 'error');
                        // Se l'aggiornamento fallisce, ripristina lo stato originale del select
                        const originalStatus = this.dataset.originalStatus; 
                        const originalOption = Array.from(this.options).find(option => option.value === originalStatus);
                        if (originalOption) {
                            originalOption.selected = true;
                        }
                    }
                })
                .catch(error => {
                    console.error('Errore durante la richiesta fetch:', error);
                    // Questo blocco cattura errori di rete, errori di parsing JSON, o errori lanciati dal blocco .then(response => ...)
                    showFlashMessage(error.message || 'Si è verificato un errore di comunicazione con il server.', 'error');
                })
                .finally(() => {
                    // Nascondi lo spinner e riabilita il select, indipendentemente dal successo o fallimento
                    spinner.classList.add('hidden');
                    this.disabled = false;
                });
            });

            // Salva lo stato originale del select al caricamento della pagina
            // Questo è utile per ripristinare il valore in caso di errore AJAX
            select.dataset.originalStatus = select.value;
        }
    });
});
</script>
