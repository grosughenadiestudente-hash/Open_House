<?php
require_once 'config.php';
requirePartner();

$lang = $_GET['lang'] ?? 'it';
$partner_user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT pp.*, u.nome, u.cognome, u.email
                       FROM partner_profili pp
                       JOIN utenti u ON u.id = pp.utente_id
                       WHERE pp.utente_id = ?");
$stmt->execute([$partner_user_id]);
$partner = $stmt->fetch();

$stmt = $pdo->prepare("SELECT COUNT(*) AS totale FROM prenotazioni WHERE partner_vr_id = ?");
$stmt->execute([$partner['id'] ?? 0]);
$tot_prenotazioni = $stmt->fetch()['totale'] ?? 0;
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Partner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
<div class="container py-4">
    <h2 class="mb-3"><i class="bi bi-broadcast-pin"></i> Dashboard Partner</h2>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary"><div class="card-body">
                <h5 class="card-title">Stato validazione</h5>
                <p class="mb-0"><?= htmlspecialchars($partner['stato_validazione'] ?? 'in_attesa') ?></p>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success"><div class="card-body">
                <h5 class="card-title">Prenotazioni ricevute</h5>
                <h3 class="mb-0"><?= (int)$tot_prenotazioni ?></h3>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-dark"><div class="card-body">
                <h5 class="card-title">Tipo partner</h5>
                <p class="mb-0"><?= htmlspecialchars($partner['tipo_partner'] ?? '-') ?></p>
            </div></div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">Profilo partner</div>
        <div class="card-body">
            <?php if (!$partner): ?>
                <p class="text-muted mb-0">Profilo partner non trovato. Completa la registrazione.</p>
            <?php else: ?>
                <p><strong>Ragione sociale:</strong> <?= htmlspecialchars($partner['ragione_sociale']) ?></p>
                <p><strong>Referente:</strong> <?= htmlspecialchars(trim(($partner['nome'] ?? '') . ' ' . ($partner['cognome'] ?? ''))) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($partner['email']) ?></p>
                <p><strong>Area:</strong> <?= htmlspecialchars(($partner['citta'] ?? '-') . ', ' . ($partner['regione'] ?? '-')) ?></p>
                <p class="mb-0"><strong>Descrizione:</strong> <?= htmlspecialchars($partner['descrizione'] ?? '-') ?></p>
            <?php endif; ?>
        </div>
    </div>
    <div class="mt-3">
        <a href="dashboard.php" class="btn btn-primary">Aggiorna</a>
        <a href="logout.php" class="btn btn-outline-secondary">Logout</a>
    </div>
</div>
</body>
</html>
