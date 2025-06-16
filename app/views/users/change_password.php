<?php
// app/views/users/change_password.php

// Questo file è la vista per la modifica della password dell'utente loggato.
// Le variabili $user (se recuperate), $_SESSION['message'], $_SESSION['message_type']
// sono passate dal AuthController->changePassword()
?>

<h2 class="text-2xl font-semibold mb-4">Modifica la Tua Password</h2>

<!-- Messaggio flash per errori di validazione del form o successo -->
<?php if (!empty($_SESSION['message'])): ?>
    <div class="flash-message flash-<?php echo htmlspecialchars($_SESSION['message_type']); ?> mb-4">
        <?php echo htmlspecialchars($_SESSION['message']); ?>
    </div>
    <?php 
    // Pulisci il messaggio flash dopo averlo visualizzato nel form
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
    ?>
<?php endif; ?>

<form method="POST" action="index.php?page=my_profile&action=change_password" class="bg-white p-6 rounded-lg shadow-md max-w-lg mx-auto">
    <div class="mb-4">
        <label for="current_password" class="block text-gray-700 text-sm font-bold mb-2">Password Attuale:</label>
        <input type="password" id="current_password" name="current_password" required
               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
    </div>

    <div class="mb-4">
        <label for="new_password" class="block text-gray-700 text-sm font-bold mb-2">Nuova Password:</label>
        <input type="password" id="new_password" name="new_password" required
               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        <p class="text-xs text-gray-500 mt-1">Minimo 6 caratteri.</p>
    </div>

    <div class="mb-6">
        <label for="confirm_new_password" class="block text-gray-700 text-sm font-bold mb-2">Conferma Nuova Password:</label>
        <input type="password" id="confirm_new_password" name="confirm_new_password" required
               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
    </div>

    <div class="flex items-center justify-between">
        <button type="submit" class="btn btn-primary">
            Aggiorna Password
        </button>
        <a href="index.php?page=dashboard" class="btn btn-secondary">Annulla</a>
    </div>
</form>
