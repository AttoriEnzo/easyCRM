<?php
// app/views/commercial_orders/print_commercial_doc.php

// Le variabili $order, $order_items, $company_settings sono passate dal CommercialOrderController->printCommercialDoc()

// Assicurati che le variabili essenziali siano definite
if (!isset($order) || $order === null || !isset($order_items) || !isset($company_settings) || $company_settings === null) {
    die("Dati insufficienti per generare la Conferma d'Ordine.");
}

// Dati aziendali per i documenti
$company_name = htmlspecialchars($company_settings['company_name'] ?? 'Nome Azienda Non Configurato');
$company_address = htmlspecialchars($company_settings['address'] ?? 'Indirizzo Non Configurato');
$company_city = htmlspecialchars($company_settings['city'] ?? '');
$company_zip = htmlspecialchars($company_settings['zip'] ?? '');
$company_province = htmlspecialchars($company_settings['province'] ?? '');
$company_full_address = $company_address . ', ' . $company_zip . ' ' . $company_city . ' (' . $company_province . ')';
$company_phone = htmlspecialchars($company_settings['phone'] ?? 'N/D');
$company_email = htmlspecialchars($company_settings['email'] ?? 'N/D');
$company_vat = htmlspecialchars($company_settings['vat_number'] ?? 'N/D');
$company_tax_code = htmlspecialchars($company_settings['tax_code'] ?? 'N/D');
$company_pec = htmlspecialchars($company_settings['pec'] ?? 'N/D');
$company_sdi = htmlspecialchars($company_settings['sdi'] ?? 'N/D');
$company_logo_url = htmlspecialchars($company_settings['logo_url'] ?? ''); // URL del logo

// Dati cliente
$client_company = htmlspecialchars($order['company'] ?: $order['contact_first_name'] . ' ' . $order['contact_last_name']);
$client_full_address = htmlspecialchars($order['company_address'] ?? 'N/D') . ', ' . htmlspecialchars($order['company_zip'] ?? '') . ' ' . htmlspecialchars($order['company_city'] ?? '') . ' (' . htmlspecialchars($order['company_province'] ?? '') . ')';
$client_vat_tax_code = !empty($order['vat_number']) ? 'P.IVA: ' . htmlspecialchars($order['vat_number']) : 'C.F.: ' . htmlspecialchars($order['tax_code']);

// Dati spedizione
$shipping_address = htmlspecialchars($order['shipping_address'] ?? 'N/D');
$shipping_city = htmlspecialchars($order['shipping_city'] ?? '');
$shipping_zip = htmlspecialchars($order['shipping_zip'] ?? '');
$shipping_province = htmlspecialchars($order['shipping_province'] ?? '');
$shipping_full_address = $shipping_address . ', ' . $shipping_zip . ' ' . $shipping_city . ' (' . $shipping_province . ')';

// Note
$notes_commercial = nl2br(htmlspecialchars($order['notes_commercial'] ?? ''));
$notes_technical = nl2br(htmlspecialchars($order['notes_technical'] ?? '')); // Generalmente non sul commerciale

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conferma d'Ordine #<?php echo htmlspecialchars($order['id']); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 20mm; /* Aggiungi un margine di stampa */
            background-color: #fff;
            color: #333;
            font-size: 10pt;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            border: 1px solid #ddd; /* Cornice per il documento */
            padding: 20mm;
            box-sizing: border-box;
        }
        header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        header .logo {
            flex: 1;
            text-align: left;
        }
        header .logo img {
            max-width: 150px;
            height: auto;
            display: block; /* Per rimuovere spazio sotto l'immagine */
        }
        header .company-info {
            flex: 2;
            text-align: right;
            font-size: 9pt;
        }
        h1 {
            text-align: center;
            font-size: 18pt;
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 25px;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        .info-block p {
            margin: 0;
            padding: 2px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 9pt;
        }
        table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .total-row td {
            font-weight: bold;
            background-color: #f2f2f2;
        }
        .total-amount {
            text-align: right;
            font-size: 14pt;
            font-weight: bold;
            margin-top: 20px;
        }
        .notes {
            margin-top: 30px;
            font-size: 9pt;
        }
        .footer {
            text-align: center;
            margin-top: 50px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 8pt;
            color: #777;
        }
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .container {
                border: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">
                <?php if (!empty($company_logo_url)): ?>
                    <img src="<?php echo $company_logo_url; ?>" alt="Logo Azienda" onerror="this.onerror=null; this.src='https://placehold.co/150x50/DDDDDD/333333?text=Logo';">
                <?php else: ?>
                    <!-- Fallback se non c'è URL del logo o se l'immagine non si carica -->
                    <img src="img/logo_epsb.png" alt="Logo EPSB Default" onerror="this.onerror=null; this.src='https://placehold.co/150x50/DDDDDD/333333?text=Logo';">
                <?php endif; ?>
            </div>
            <div class="company-info">
                <strong><?php echo $company_name; ?></strong><br>
                <?php echo $company_full_address; ?><br>
                Tel: <?php echo $company_phone; ?> | Email: <?php echo $company_email; ?><br>
                P.IVA: <?php echo $company_vat; ?> | C.F.: <?php echo $company_tax_code; ?><br>
                PEC: <?php echo $company_pec; ?> | SDI: <?php echo $company_sdi; ?>
            </div>
        </header>

        <h1>Conferma d'Ordine #<?php echo htmlspecialchars($order['id']); ?></h1>

        <div class="info-grid">
            <div class="info-block">
                <div class="section-title">Destinatario (Cliente)</div>
                <p><strong>Ragione Sociale:</strong> <?php echo $client_company; ?></p>
                <p><strong>Indirizzo:</strong> <?php echo $client_full_address; ?></p>
                <p><?php echo $client_vat_tax_code; ?></p>
                <p><strong>Commerciale:</strong> <?php echo htmlspecialchars($order['commercial_username'] ?? 'N/D'); ?></p>
            </div>
            <div class="info-block">
                <div class="section-title">Dettagli Ordine</div>
                <p><strong>Data Ordine:</strong> <?php echo date('d/m/Y', strtotime($order['order_date'])); ?></p>
                <p><strong>Stato Ordine:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
                <p><strong>Spedizione Prevista:</strong> <?php echo $order['expected_shipping_date'] ? date('d/m/Y', strtotime($order['expected_shipping_date'])) : 'N/D'; ?></p>
                <p><strong>Vettore:</strong> <?php echo htmlspecialchars($order['carrier'] ?? 'N/D'); ?></p>
                <p><strong>Costi Spedizione:</strong> <?php echo !empty($order['shipping_costs']) ? '&euro; ' . htmlspecialchars(number_format($order['shipping_costs'], 2, ',', '.')) : 'N/D'; ?></p>
            </div>
        </div>
        
        <div class="info-block">
            <div class="section-title">Indirizzo di Spedizione</div>
            <p><?php echo $shipping_full_address; ?></p>
        </div>

        <div class="section-title">Articoli Ordinati</div>
        <table>
            <thead>
                <tr>
                    <th>Descrizione</th>
                    <th style="width: 80px;">Quantità</th>
                    <th style="width: 100px;">Prezzo Unit.</th>
                    <th style="width: 100px;">Totale Riga</th>
                    <th style="width: 120px;">Matricola Spedita</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $subtotal = 0;
                foreach ($order_items as $item): 
                    $item_total = $item['ordered_quantity'] * $item['ordered_unit_price'];
                    $subtotal += $item_total;
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['description']); ?></td>
                        <td style="text-align: center;"><?php echo htmlspecialchars($item['ordered_quantity']); ?></td>
                        <td style="text-align: right;"><?php echo htmlspecialchars(number_format($item['ordered_unit_price'], 2, ',', '.')); ?> &euro;</td>
                        <td style="text-align: right;"><?php echo htmlspecialchars(number_format($item_total, 2, ',', '.')); ?> &euro;</td>
                        <td><?php echo htmlspecialchars($item['actual_shipped_serial_number'] ?? 'N/D'); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="3" style="text-align: right;">Subtotale:</td>
                    <td style="text-align: right;"><?php echo htmlspecialchars(number_format($subtotal, 2, ',', '.')); ?> &euro;</td>
                    <td></td>
                </tr>
                 <tr class="total-row">
                    <td colspan="3" style="text-align: right;">Costi Spedizione:</td>
                    <td style="text-align: right;"><?php echo htmlspecialchars(number_format($order['shipping_costs'], 2, ',', '.')); ?> &euro;</td>
                    <td></td>
                </tr>
                <tr class="total-row">
                    <td colspan="3" style="text-align: right;">TOTALE ORDINE:</td>
                    <td style="text-align: right; font-size: 11pt;"><?php echo htmlspecialchars(number_format($order['total_amount'], 2, ',', '.')); ?> &euro;</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <div class="notes">
            <p><strong>Note Commerciali:</strong></p>
            <p><?php echo !empty($notes_commercial) ? $notes_commercial : 'Nessuna nota commerciale.'; ?></p>
        </div>

        <footer class="footer">
            Conferma d'Ordine generata il <?php echo date('d/m/Y H:i'); ?> | CRM Minimalista
        </footer>
    </div>
</body>
</html>
