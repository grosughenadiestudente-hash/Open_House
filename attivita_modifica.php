<?php
require_once 'config.php';
requireIstituto();

$lang = $_GET['lang'] ?? 'it';
$attivita_id = $_GET['id'] ?? 0;
$istituto_id = $_SESSION['user_id'];

// Verifica che l'attività appartenga all'istituto
$stmt = $pdo->prepare("SELECT * FROM attivita_eventi WHERE ID_Attivita = ? AND FK_Ente_Organizzatore = ?");
$stmt->execute([$attivita_id, $istituto_id]);
$attivita = $stmt->fetch();

if (!$attivita) {
    header('Location: attivita_gestione.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titolo = sanitize($_POST['titolo'] ?? '');
    $descrizione = sanitize($_POST['descrizione'] ?? '');
    $tipo_attivita = $_POST['tipo_attivita'] ?? '';
    $data_ora = $_POST['data_ora'] ?? '';
    $durata_minuti = intval($_POST['durata_minuti'] ?? 60);
    $max_partecipanti = intval($_POST['max_partecipanti'] ?? 50);
    $supporta_vr = isset($_POST['supporta_vr']) ? 1 : 0;
    $url_vr = sanitize($_POST['url_vr'] ?? '');
    $materiali_url = sanitize($_POST['materiali_url'] ?? '');
    $stato = $_POST['stato'] ?? 'bozza';
    
    if (empty($titolo) || empty($descrizione) || empty($tipo_attivita) || empty($data_ora)) {
        $error = 'Compila tutti i campi obbligatori';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE attivita SET 
                titolo = ?, descrizione = ?, tipo_attivita = ?, data_ora = ?, 
                durata_minuti = ?, max_partecipanti = ?, supporta_vr = ?, 
                url_vr = ?, materiali_url = ?, stato = ? 
                WHERE id = ? AND istituto_id = ?");
            $stmt->execute([
                $titolo, $descrizione, $tipo_attivita, $data_ora, 
                $durata_minuti, $max_partecipanti, $supporta_vr, 
                $url_vr, $materiali_url, $stato, $attivita_id, $istituto_id
            ]);
            $success = 'Attività aggiornata con successo!';
            header('Location: attivita_gestione.php?lang=' . $lang);
            exit;
        } catch(PDOException $e) {
            $error = 'Errore durante l\'aggiornamento: ' . $e->getMessage();
        }
    }
}

$translations = [
    'it' => [
        'title' => 'Modifica Attività',
        'modifica_attivita' => 'Modifica Attività',
        'salva' => 'Salva',
        'annulla' => 'Annulla'
    ],
    'en' => [
        'title' => 'Edit Activity',
        'modifica_attivita' => 'Edit Activity',
        'salva' => 'Save',
        'annulla' => 'Cancel'
    ]
];

$t = $translations[$lang];
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t['title'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container mt-4 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header">
                        <h4 class="mb-0"><?= $t['modifica_attivita'] ?></h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="titolo" class="form-label">Titolo *</label>
                                <input type="text" class="form-control" id="titolo" name="titolo" value="<?= htmlspecialchars($attivita['titolo']) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="descrizione" class="form-label">Descrizione *</label>
                                <textarea class="form-control" id="descrizione" name="descrizione" rows="5" required><?= htmlspecialchars($attivita['descrizione']) ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="tipo_attivita" class="form-label">Tipo Attività *</label>
                                    <select class="form-select" id="tipo_attivita" name="tipo_attivita" required>
                                        <option value="presentazione" <?= $attivita['tipo_attivita'] === 'presentazione' ? 'selected' : '' ?>>Presentazione</option>
                                        <option value="laboratorio" <?= $attivita['tipo_attivita'] === 'laboratorio' ? 'selected' : '' ?>>Laboratorio</option>
                                        <option value="tour_virtuale" <?= $attivita['tipo_attivita'] === 'tour_virtuale' ? 'selected' : '' ?>>Tour Virtuale</option>
                                        <option value="workshop" <?= $attivita['tipo_attivita'] === 'workshop' ? 'selected' : '' ?>>Workshop</option>
                                        <option value="altro" <?= $attivita['tipo_attivita'] === 'altro' ? 'selected' : '' ?>>Altro</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="data_ora" class="form-label">Data e Ora *</label>
                                    <input type="datetime-local" class="form-control" id="data_ora" name="data_ora" value="<?= date('Y-m-d\TH:i', strtotime($attivita['data_ora'])) ?>" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="durata_minuti" class="form-label">Durata (minuti)</label>
                                    <input type="number" class="form-control" id="durata_minuti" name="durata_minuti" value="<?= $attivita['durata_minuti'] ?>" min="15" step="15">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="max_partecipanti" class="form-label">Massimo Partecipanti</label>
                                    <input type="number" class="form-control" id="max_partecipanti" name="max_partecipanti" value="<?= $attivita['max_partecipanti'] ?>" min="1">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="supporta_vr" name="supporta_vr" <?= $attivita['supporta_vr'] ? 'checked' : '' ?> onchange="toggleVRFields()">
                                    <label class="form-check-label" for="supporta_vr">
                                        Supporta VR
                                    </label>
                                </div>
                            </div>
                            
                            <div id="vrFields" style="display: <?= $attivita['supporta_vr'] ? 'block' : 'none' ?>;">
                                <div class="mb-3">
                                    <label for="url_vr" class="form-label">URL Ambiente VR</label>
                                    <input type="url" class="form-control" id="url_vr" name="url_vr" value="<?= htmlspecialchars($attivita['url_vr']) ?>" placeholder="https://...">
                                    <small class="form-text text-muted">URL dell'ambiente VR (es. A-Frame scene o vr_example.html)</small>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="materiali_url" class="form-label">URL Materiali</label>
                                <input type="url" class="form-control" id="materiali_url" name="materiali_url" value="<?= htmlspecialchars($attivita['materiali_url']) ?>" placeholder="https://...">
                            </div>
                            
                            <div class="mb-3">
                                <label for="stato" class="form-label">Stato</label>
                                <select class="form-select" id="stato" name="stato">
                                    <option value="bozza" <?= $attivita['stato'] === 'bozza' ? 'selected' : '' ?>>Bozza</option>
                                    <option value="pubblicata" <?= $attivita['stato'] === 'pubblicata' ? 'selected' : '' ?>>Pubblicata</option>
                                    <option value="cancellata" <?= $attivita['stato'] === 'cancellata' ? 'selected' : '' ?>>Cancellata</option>
                                </select>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary"><?= $t['salva'] ?></button>
                                <a href="attivita_gestione.php?lang=<?= $lang ?>" class="btn btn-secondary"><?= $t['annulla'] ?></a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleVRFields() {
            const checkbox = document.getElementById('supporta_vr');
            const fields = document.getElementById('vrFields');
            fields.style.display = checkbox.checked ? 'block' : 'none';
        }
    </script>
</body>
</html>
