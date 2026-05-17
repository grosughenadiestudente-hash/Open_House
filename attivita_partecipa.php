<?php
require_once 'config.php';
requireLogin();

$lang = $_GET['lang'] ?? 'it';
$attivita_id = $_GET['id'] ?? 0;

function getUserTable(PDO $pdo): ?string {
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('utenti', $tables, true)) {
        return 'utenti';
    }
    if (in_array('utenti_finali', $tables, true)) {
        return 'utenti_finali';
    }
    return null;
}

// Verifica che l'utente abbia prenotato
if ($_SESSION['user_type'] === 'utente') {
    $stmt = $pdo->prepare("SELECT id FROM prenotazioni WHERE utente_id = ? AND attivita_id = ? AND stato = 'confermata'");
    $stmt->execute([$_SESSION['user_id'], $attivita_id]);
    if (!$stmt->fetch()) {
        header('Location: attivita_dettaglio.php?id=' . $attivita_id);
        exit;
    }
}

$stmt = $pdo->prepare("SELECT a.ID_Attivita as id, a.Titolo as titolo, a.Descrizione as descrizione, a.Data_Ora as data_ora,
                       a.Supporta_VR as supporta_vr, a.Max_Posti as max_partecipanti, a.Stato as stato,
                       a.Link_WebXR as url_vr, a.Materiali_URL as materiali_url,
                       i.Ragione_Sociale as istituto_nome FROM attivita_eventi a 
                       JOIN istituti_e_partner i ON a.FK_Ente_Organizzatore = i.ID_Ente 
                       WHERE a.ID_Attivita = ?");
$stmt->execute([$attivita_id]);
$attivita = $stmt->fetch();

if (!$attivita) {
    header('Location: index.php');
    exit;
}

// Carica messaggi chat
$userTable = getUserTable($pdo);
$userJoin = $userTable ? "LEFT JOIN {$userTable} u ON m.utente_id = u.id" : '';
$userSelect = $userTable ? 'u.nome as utente_nome' : 'NULL as utente_nome';

$stmt = $pdo->prepare("SELECT m.*, {$userSelect}, i.Ragione_Sociale as istituto_nome 
                       FROM messaggi_chat m 
                       {$userJoin}
                       LEFT JOIN istituti_e_partner i ON m.istituto_id = i.ID_Ente 
                       WHERE m.attivita_id = ? 
                       ORDER BY m.created_at ASC");
$stmt->execute([$attivita_id]);
$messaggi = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partecipa - <?= htmlspecialchars($attivita['titolo']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <?php if ($attivita['supporta_vr'] && $attivita['url_vr']): ?>
        <script src="https://aframe.io/releases/1.4.0/aframe.min.js"></script>
    <?php endif; ?>
</head>
<body class="bg-dark text-white">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand"><?= htmlspecialchars($attivita['titolo']) ?></span>
            <a href="attivita_dettaglio.php?id=<?= $attivita_id ?>&lang=<?= $lang ?>" class="btn btn-outline-light btn-sm">Esci</a>
        </div>
    </nav>

    <div class="container-fluid mt-3">
        <div class="row">
            <?php if ($attivita['supporta_vr'] && $attivita['url_vr']): ?>
                <div class="col-md-8">
                    <div class="card bg-dark border-secondary">
                        <div class="card-body p-0" style="height: 80vh;">
                            <iframe src="<?= htmlspecialchars($attivita['url_vr']) ?>" 
                                    style="width: 100%; height: 100%; border: none;"></iframe>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="col-md-8">
                    <div class="card bg-dark border-secondary">
                        <div class="card-body text-center p-5">
                            <h3><?= htmlspecialchars($attivita['titolo']) ?></h3>
                            <p class="lead"><?= htmlspecialchars($attivita['descrizione']) ?></p>
                            <?php if ($attivita['materiali_url']): ?>
                                <a href="<?= htmlspecialchars($attivita['materiali_url']) ?>" class="btn btn-primary" target="_blank">
                                    <i class="bi bi-download"></i> Scarica Materiali
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="col-md-4">
                <div class="card bg-dark border-secondary">
                    <div class="card-header">
                        <h5 class="mb-0">Chat e Q&A</h5>
                    </div>
                    <div class="card-body" style="height: 60vh; overflow-y: auto;" id="chatMessages">
                        <?php foreach ($messaggi as $msg): ?>
                            <div class="mb-2">
                                <small class="text-muted">
                                    <?= htmlspecialchars($msg['utente_nome'] ?: $msg['istituto_nome']) ?> - 
                                    <?= date('H:i', strtotime($msg['created_at'])) ?>
                                </small>
                                <p class="mb-0"><?= nl2br(htmlspecialchars($msg['messaggio'])) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="card-footer">
                        <form id="chatForm" method="POST" action="chat_invia.php">
                            <input type="hidden" name="attivita_id" value="<?= $attivita_id ?>">
                            <div class="input-group">
                                <input type="text" class="form-control" name="messaggio" id="messaggio" placeholder="Scrivi un messaggio..." required>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-scroll chat
        const chatMessages = document.getElementById('chatMessages');
        chatMessages.scrollTop = chatMessages.scrollHeight;

        // Auto-refresh chat ogni 3 secondi
        setInterval(function() {
            fetch('chat_messaggi.php?attivita_id=<?= $attivita_id ?>')
                .then(response => response.json())
                .then(data => {
                    chatMessages.innerHTML = '';
                    data.forEach(msg => {
                        const div = document.createElement('div');
                        div.className = 'mb-2';
                        div.innerHTML = `
                            <small class="text-muted">${msg.nome} - ${msg.time}</small>
                            <p class="mb-0">${msg.messaggio.replace(/\n/g, '<br>')}</p>
                        `;
                        chatMessages.appendChild(div);
                    });
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                });
        }, 3000);
    </script>
</body>
</html>
