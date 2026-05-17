<?php
require_once 'config.php';
requireAdmin();

$flash = '';
$flashType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enteId = (int)($_POST['ente_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($enteId > 0 && in_array($action, ['approva', 'blocca'], true)) {
        $newStatus = $action === 'approva' ? 1 : 2;
        $stmt = $pdo->prepare('UPDATE istituti_e_partner SET Stato_Validazione = ? WHERE ID_Ente = ?');
        $stmt->execute([$newStatus, $enteId]);

        $flash = $action === 'approva'
            ? 'Ente approvato con successo.'
            : 'Ente bloccato con successo.';
    } else {
        $flash = 'Operazione non valida.';
        $flashType = 'danger';
    }
}

$stmt = $pdo->query("SELECT ID_Ente, Ragione_Sociale, Tipologia, Cod_Mecc, Cod_REA, Comune, Provincia, Regione, Email, Telefono, Stato_Validazione
                     FROM istituti_e_partner
                     WHERE Stato_Validazione IN (0, 2)
                     ORDER BY Stato_Validazione ASC, Ragione_Sociale ASC");
$enti = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validazione Enti - Open House</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
<div class="container py-4">
    <h2 class="mb-3"><i class="bi bi-check2-square"></i> Validazione Enti</h2>
    <div class="mb-3">
        <a href="dashboard_admin.php" class="btn btn-outline-secondary btn-sm">Torna alla dashboard</a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="alert alert-<?= htmlspecialchars($flashType) ?>"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header">Istituti e partner in attesa/bloccati</div>
        <div class="card-body p-0">
            <?php if (empty($enti)): ?>
                <p class="p-3 mb-0 text-muted">Nessun ente da validare.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Tipologia</th>
                                <th>Cod_Mecc</th>
                                <th>Cod_REA</th>
                                <th>Email</th>
                                <th>Telefono</th>
                                <th>Stato</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($enti as $ente): ?>
                                <tr>
                                    <td><?= htmlspecialchars($ente['Ragione_Sociale']) ?></td>
                                    <td><?= htmlspecialchars($ente['Tipologia'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($ente['Cod_Mecc'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($ente['Cod_REA'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($ente['Email'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($ente['Telefono'] ?? '-') ?></td>
                                    <td>
                                        <?php if ((int)$ente['Stato_Validazione'] === 0): ?>
                                            <span class="badge bg-warning text-dark">In attesa</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Bloccato</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="ente_id" value="<?= (int)$ente['ID_Ente'] ?>">
                                            <button type="submit" name="action" value="approva" class="btn btn-sm btn-success">Approva</button>
                                            <button type="submit" name="action" value="blocca" class="btn btn-sm btn-outline-danger">Blocca</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
