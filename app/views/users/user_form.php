<?php
// app/views/users/user_form.php

// Le variabili $user (se in modalità modifica), $form_title, $submit_button_text, $action_url
// sono passate dal UserController->add() o UserController->edit()

// Determina se siamo in modalità aggiunta o modifica
$is_edit_mode = isset($user['id']) && $user['id'] !== null;
$form_title = $is_edit_mode ? 'Modifica Utente' : 'Aggiungi Nuovo Utente';
$submit_button_text = $is_edit_mode ? 'Aggiorna Utente' : 'Crea Utente';
$action_url = $is_edit_mode ? "index.php?page=users&action=edit&id=" . htmlspecialchars($user['id']) : "index.php?page=users&action=add";
$cancel_url = "index.php?page=users"; // Torna alla lista utenti

// Pre-popola i valori del form in base alla modalità
$username = $is_edit_mode ? htmlspecialchars($user['username']) : '';
$role = $is_edit_mode ? htmlspecialchars($user['role']) : 'user'; // Default 'user' per nuovi utenti
?>

<h2 class="text-2xl font-semibold mb-4"><?php echo $form_title; ?></h2>

<form method="POST" action="<?php echo $action_url; ?>" class="bg-white p-6 rounded-lg shadow-md max-w-lg mx-auto">
    <div class="mb-4">
        <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username:</label>
        <input type="text" id="username" name="username" value="<?php echo $username; ?>" required
               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
    </div>

    <div class="mb-4">
        <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password: <?php echo $is_edit_mode ? '(lascia vuoto per non modificare)' : ''; ?></label>
        <input type="password" id="password" name="password" <?php echo $is_edit_mode ? '' : 'required'; ?>
               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline">
        <?php if ($is_edit_mode): ?>
            <p class="text-sm text-gray-600">Lascia il campo password vuoto se non desideri modificarla.</p>
        <?php endif; ?>
    </div>

    <div class="mb-6">
        <label for="role" class="block text-gray-700 text-sm font-bold mb-2">Ruolo:</label>
        <select id="role" name="role" required
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <option value="user" <?php echo ($role == 'user') ? 'selected' : ''; ?>>Utente Standard</option>
            <option value="admin" <?php echo ($role == 'admin') ? 'selected' : ''; ?>>Amministratore</option>
            <option value="superadmin" <?php echo ($role == 'superadmin') ? 'selected' : ''; ?>>Super Amministratore</option>
            <option value="tecnico" <?php echo ($role == 'tecnico') ? 'selected' : ''; ?>>Tecnico</option>
            <option value="commerciale" <?php echo ($role == 'commerciale') ? 'selected' : ''; ?>>Commerciale</option> <!-- NUOVO RUOLO -->
        </select>
    </div>

    <div class="flex items-center justify-between">
        <button type="submit" class="btn btn-primary">
            <?php echo $submit_button_text; ?>
        </button>
        <a href="<?php echo $cancel_url; ?>" class="btn btn-secondary">Annulla</a>
    </div>
</form>
