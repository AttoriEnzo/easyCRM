<?php
// app/views/login.php
// Questa pagina ora è autonoma e non include header/footer esterni

// La funzione display_flash_message() dovrà essere definita localmente o inclusa,
// ma per semplicità, la rendiamo autonoma per il login.
// Assicurati che session_start() sia già stato chiamato in public/index.php.

// Recupera e pulisce il messaggio flash
$message = $_SESSION['message'] ?? '';
$message_type = $_SESSION['message_type'] ?? 'info';
unset($_SESSION['message']);
unset($_SESSION['message_type']);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login CRM Minimalista</title>
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome CDN per le icone -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Includi il tuo file CSS personalizzato (per stili globali e login) -->
    <link rel="stylesheet" href="css/style.css">

    <style>
        /* Stili specifici per il body della pagina di login per centrare verticalmente */
        html, body {
            height: 100%; /* Assicura che html e body occupino l'intera altezza */
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6; /* gray-100 */
        }
        body {
            display: flex; /* Usa flexbox per il body */
            align-items: center; /* Centra verticalmente il contenuto */
            justify-content: center; /* Centra orizzontalmente il contenuto */
        }
        /* Override di eventuali margini superiori del div con il form */
        .login-container {
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <div class="login-container max-w-md w-full bg-white p-8 rounded-lg shadow-xl space-y-6">
        <div class="text-center">
            <!-- Logo EPSB -->
            <img class="mx-auto" src="img/logo_epsb.png" alt="Logo EPSB" width="140" height="50" onerror="this.onerror=null; this.src='https://placehold.co/140x50/DDDDDD/333333?text=Logo+Non+Trovato';">
            <p class="mt-4 text-center text-sm text-gray-600">
                Inserisci le tue credenziali per continuare
            </p>
        </div>
        
        <?php if (!empty($message)): // Mostra il messaggio flash ?>
            <div class='flash-message flash-<?php echo htmlspecialchars($message_type); ?>'>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" action="index.php?page=login" method="POST">
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="username" class="sr-only">Username</label>
                    <input id="username" name="username" type="text" autocomplete="username" required
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                           placeholder="Username">
                </div>
                <div>
                    <label for="password" class="sr-only">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                           placeholder="Password">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="text-sm">
                    <!-- Eventuali link per "Password dimenticata?" qui, se implementati in futuro -->
                </div>
            </div>

            <div>
                <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-sign-in-alt h-5 w-5 text-indigo-500 group-hover:text-indigo-400"></i>
                    </span>
                    Accedi
                </button>
            </div>
        </form>
    </div>
</body>
</html>
