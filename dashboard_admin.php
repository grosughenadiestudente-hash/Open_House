<?php
require_once 'config.php';
requireAdmin();

$stmt = $pdo->query("SELECT COUNT(*) AS totale FROM istituti_e_partner WHERE Stato_Validazione = 0");
$istituti_in_attesa = $stmt->fetch()['totale'] ?? 0;

$stmt = $pdo->query("SELECT COUNT(*) AS totale FROM partner_profili WHERE stato_validazione = 'in_attesa'");
$partner_in_attesa = $stmt->fetch()['totale'] ?? 0;

$stmt = $pdo->query("SELECT COUNT(*) AS totale FROM prenotazioni");
$tot_prenotazioni = $stmt->fetch()['totale'] ?? 0;
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
<div class="container py-4">
    <h2 class="mb-3"><i class="bi bi-shield-check"></i> Dashboard Amministratore</h2>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5>Istituti da validare</h5>
                    <h2 class="mb-0"><?= (int)$istituti_in_attesa ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5>Partner da validare</h5>
                    <h2 class="mb-0"><?= (int)$partner_in_attesa ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5>Prenotazioni totali</h5>
                    <h2 class="mb-0"><?= (int)$tot_prenotazioni ?></h2>
                </div>
            </div>
        </div>
    </div>
    <div class="alert alert-secondary mt-4 mb-0">
        Prossimo step: workflow completo di approvazione enti, moderazione contenuti e audit log.
    </div>
    <div class="mt-3">
        <a href="admin_validazione_enti.php" class="btn btn-primary">Valida istituti e partner</a>
        <a href="logout.php" class="btn btn-outline-secondary">Logout</a>
    </div>
</div>
</body>
</html>
