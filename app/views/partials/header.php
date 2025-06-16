<?php
// app/views/partials/header.php
// Contiene l'inizio del documento HTML, HEAD e la barra di navigazione

// Recupera il ruolo dell'utente dalla sessione per i controlli di visibilità dei link
$current_user_role = $_SESSION['role'] ?? null;
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM EPS</title>
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome CDN per le icone -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Includi il tuo file CSS personalizzato -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-gray-100 font-sans">
    <nav class="navbar">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php?page=dashboard" class="navbar-brand">
                <img src="img/logo_epsw.png" alt="Logo EPSB" class="h-auto mr-2" 
                     onerror="this.onerror=null; this.src='https://placehold.co/140x50/DDDDDD/333333?text=Logo+Non+Trovato';"
                     style="display: block !important; opacity: 1 !important; width: 150px !important; height: 50px !important;">
                CRM eps
            </a>
            <div class="navbar-nav flex space-x-4 items-center">
                <a href="index.php?page=dashboard" class="<?php echo ($page === 'dashboard') ? 'active' : ''; ?>">Dashboard</a>
                <a href="index.php?page=contacts" class="<?php echo ($page === 'contacts') ? 'active' : ''; ?>">Contatti</a>
                <a href="index.php?page=repairs" class="<?php echo ($page === 'repairs') ? 'active' : ''; ?>">Riparazioni</a>
                
                <?php 
                // Il link "Interazioni" è visibile per Admin, Super Amministratore, Tecnico e Commerciale
                if (in_array($current_user_role, ['admin', 'superadmin', 'tecnico', 'commerciale'])): 
                ?>
                    <a href="index.php?page=interactions" class="<?php echo ($page === 'interactions') ? 'active' : ''; ?>">Interazioni</a>
                <?php endif; ?>

                <?php 
                // Il link "Catalogo Prodotti" è visibile solo per Super Amministratore
                if ($current_user_role === 'superadmin'): 
                ?>
                    <a href="index.php?page=products_catalog" class="<?php echo ($page === 'products_catalog') ? 'active' : ''; ?>">Catalogo Prodotti</a>
                <?php endif; ?>

                <?php 
                // Il link "Ordini Commerciali" è visibile per Commerciale, Admin, Super Amministratore, Tecnico
//                if (in_array($current_user_role, ['commerciale', 'admin', 'superadmin', 'tecnico'])): <br>
				if ($current_user_role === 'superadmin'):
                ?>
                    <a href="index.php?page=commercial_orders" class="<?php echo ($page === 'commercial_orders') ? 'active' : ''; ?>">Ordini Commerciali</a>
                <?php endif; ?>

                <?php 
                // Il link "Utenti" è visibile solo per Admin e Super Amministratore
                if (in_array($current_user_role, ['admin', 'superadmin'])): 
                ?>
                    <a href="index.php?page=users" class="<?php echo ($page === 'users') ? 'active' : ''; ?>">Utenti</a>
                <?php endif; ?>

                <?php 
                // Il link "Servizi Riparazione" è visibile solo per Super Amministratore
                if ($current_user_role === 'superadmin'): 
                ?>
                    <a href="index.php?page=repair_services" class="<?php echo ($page === 'repair_services') ? 'active' : ''; ?>">Servizi Riparazione</a>
                <?php endif; ?>
               
          

                <div class="relative group">
                    <a href="#" class="flex items-center text-gray-300 hover:bg-gray-700 p-2 rounded-md transition-colors duration-200">
                        <i class="fas fa-user-circle mr-2"></i> <?php echo htmlspecialchars($_SESSION['username'] ?? 'Ospite'); ?>
                    </a>
                    <div class="absolute right-0 top-full w-48 bg-gray-700 rounded-md shadow-lg py-2 z-10 opacity-0 group-hover:opacity-100 group-hover:visible transition-opacity duration-200 invisible">
                        <a href="index.php?page=my_profile&action=change_password" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-600">
                            <i class="fas fa-key mr-2"></i> Cambia Password
                        </a>

			    <div class="relative group">
    <a href="#" class="flex items-center text-gray-300 hover:bg-gray-700 p-2 rounded-md transition-colors duration-200">
        <i class="fas fa-user-circle mr-2"></i> <?php echo htmlspecialchars($_SESSION['username'] ?? 'Ospite'); ?>
    </a>
    <div class="absolute right-0 top-full w-48 bg-gray-700 rounded-md shadow-lg py-2 z-10 opacity-0 group-hover:opacity-100 group-hover:visible transition-opacity duration-200 invisible">
        <a href="index.php?page=my_profile&action=change_password" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-600">
            <i class="fas fa-key mr-2"></i> Cambia Password
        </a>
        <?php 
        // Il link "Impostazioni Aziendali" è visibile solo per Super Amministratore
        if ($current_user_role === 'superadmin'): 
        ?>
            <a href="index.php?page=company_settings" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-600">
                <i class="fas fa-cog mr-2"></i> Impostazioni Aziendali
            </a>
        <?php endif; ?>
        <a href="index.php?page=logout" class="block px-4 py-2 text-sm text-red-300 hover:bg-gray-600">
            <i class="fas fa-sign-out-alt mr-2"></i> Logout
        </a>
    </div>
</div>
			    
			    <a href="index.php?page=logout" class="block px-4 py-2 text-sm text-red-300 hover:bg-gray-600">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <main class="container">
        <!-- I messaggi flash verranno visualizzati qui da public/index.php -->
