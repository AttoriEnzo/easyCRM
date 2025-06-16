<?php
// app/views/products/list.php

// La variabile $products è passata dal ProductsController->index()

// Recupera il ruolo dell'utente corrente dalla sessione per i controlli di visibilità
$current_user_role = $_SESSION['role'] ?? null;

// Solo il Super Amministratore può gestire i prodotti
$canManageProducts = ($current_user_role === 'superadmin');
?>

<h2 class="text-2xl font-semibold mb-4">Catalogo Prodotti</h2>

<div class="flex flex-col md:flex-row justify-between items-center mb-4">
    <?php if ($canManageProducts): ?>
        <div class="flex space-x-2 mb-2 md:mb-0">
            <a href="index.php?page=products_catalog&action=add" class="btn btn-primary">Aggiungi Nuovo Prodotto</a>
        </div>
    <?php endif; ?>
    <form method="GET" class="w-full md:w-auto flex items-center">
        <input type="hidden" name="page" value="products_catalog">
        <input type="text" name="q" placeholder="Cerca prodotti..." value="<?php echo htmlspecialchars($search_query ?? ''); ?>" class="w-full md:w-64 mr-2">
        <button type="submit" class="btn btn-secondary flex items-center justify-center">
            <i class="fas fa-search mr-1"></i> Cerca
        </button>
    </form>
</div>

<?php if (isset($products) && count($products) > 0): ?>
    <div class="overflow-x-auto bg-white rounded-lg shadow-sm">
        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    <th class="text-sm">ID</th>
                    <th class="text-sm">Codice Prodotto</th>
                    <th class="text-sm">Tipo Prodotto</th>
                    <th class="text-sm">Nome Prodotto</th>
                    <th class="text-sm">Prezzo Netto</th>
                    <th class="text-sm">Prezzo Lordo</th>
                    <th class="text-sm">Attivo</th>
                    <?php if ($canManageProducts): ?>
                        <th class="text-sm">Azioni</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td class="text-xs"><?php echo htmlspecialchars($product['id']); ?></td>
                        <td class="text-xs"><?php echo htmlspecialchars($product['product_code']); ?></td>
                        <td class="text-xs"><?php echo htmlspecialchars($product['product_type']); ?></td>
                        <td class="text-xs"><?php echo htmlspecialchars($product['product_name']); ?></td>
                        <td class="text-xs">&euro; <?php echo htmlspecialchars(number_format($product['default_price_net'], 2, ',', '.')); ?></td>
                        <td class="text-xs">&euro; <?php echo htmlspecialchars(number_format($product['default_price_gross'], 2, ',', '.')); ?></td>
                        <td class="text-xs">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold <?php echo ($product['is_active'] == 1) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo ($product['is_active'] == 1) ? 'Sì' : 'No'; ?>
                            </span>
                        </td>
                        <?php if ($canManageProducts): ?>
                            <td class="whitespace-nowrap text-xs">
                                <a href="index.php?page=products_catalog&action=edit&id=<?php echo htmlspecialchars($product['id']); ?>" title="Modifica Prodotto" class="text-yellow-600 hover:text-yellow-800 p-2 rounded-full hover:bg-yellow-100 transition duration-150 ease-in-out">
                                    <i class="fas fa-edit text-lg"></i>
                                </a>
                                <a href="index.php?page=products_catalog&action=delete&id=<?php echo htmlspecialchars($product['id']); ?>" title="Elimina Prodotto" class="text-red-600 hover:text-red-800 p-2 rounded-full hover:bg-red-100 transition duration-150 ease-in-out ml-1" onclick="return confirm('Sei sicuro di voler eliminare questo prodotto? Questa azione è irreversibile e potrebbe influire sugli ordini esistenti.');">
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
    <p class="text-gray-600">Nessun prodotto trovato.
    <?php if ($canManageProducts): ?>
        <a href="index.php?page=products_catalog&action=add" class="text-blue-600 hover:underline">Aggiungine uno nuovo!</a>
    <?php endif; ?>
    </p>
<?php endif; ?>
