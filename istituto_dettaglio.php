<?php
require_once 'config.php';

$lang = $_GET['lang'] ?? 'it';
$istituto_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT
                        ID_Ente as id,
                        Ragione_Sociale as nome,
                        Tipologia as tipo_scuola,
                        Cod_Mecc as codice_istituto,
                        Cod_REA as codice_rea,
                        Indirizzo as indirizzo,
                        Comune as comune,
                        Provincia as provincia,
                        Regione as regione,
                        Email as email,
                        Telefono as telefono
                       FROM istituti_e_partner
                       WHERE ID_Ente = ?");
$stmt->execute([$istituto_id]);
$istituto = $stmt->fetch();

if (!$istituto) {
    header('Location: istituti_elenco.php');
    exit;
}

// Ottieni attività dell'istituto
$stmt = $pdo->prepare("SELECT a.ID_Attivita as id, a.Titolo as titolo, a.Descrizione as descrizione, a.Data_Ora as data_ora,
                       a.Supporta_VR as supporta_vr, a.Max_Posti as max_partecipanti, a.Stato as stato,
                       COUNT(p.id) as prenotazioni_count 
                       FROM attivita_eventi a 
                       LEFT JOIN prenotazioni p ON a.ID_Attivita = p.attivita_id AND p.stato = 'confermata'
                       WHERE a.FK_Ente_Organizzatore = ? AND a.Stato = 'pubblicata'
                       GROUP BY a.ID_Attivita 
                       ORDER BY a.Data_Ora ASC");
$stmt->execute([$istituto_id]);
$attivita = $stmt->fetchAll();

$tipi_scuola_map = [
    'it' => [
        'infanzia' => 'Scuola dell\'Infanzia',
        'primaria' => 'Scuola Primaria',
        'secondaria_primo' => 'Scuola Secondaria di Primo Grado',
        'secondaria_secondo' => 'Scuola Secondaria di Secondo Grado',
        'universita' => 'Università'
    ],
    'en' => [
        'infanzia' => 'Kindergarten',
        'primaria' => 'Primary School',
        'secondaria_primo' => 'Lower Secondary School',
        'secondaria_secondo' => 'Upper Secondary School',
        'universita' => 'University'
    ]
];
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($istituto['nome']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
    <?php if (isLoggedIn()): include 'navbar.php'; else: ?>
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container-fluid">
                <a class="navbar-brand" href="index.php"><i class="bi bi-mortarboard"></i> Open House</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?lang=<?= $lang ?>">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="istituti_elenco.php?lang=<?= $lang ?>">Istituti</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="partner_istituti.php?lang=<?= $lang ?>">🥽 Partner</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#chiSiamoModal">Chi siamo</a>
                        </li>
                    </ul>
                    <ul class="navbar-nav">
                        <li class="nav-item ms-3">
                            <a href="?lang=it" class="btn btn-outline-light btn-sm <?= $lang === 'it' ? 'active' : '' ?>">IT</a>
                        </li>
                        <li class="nav-item ms-1">
                            <a href="?lang=en" class="btn btn-outline-light btn-sm <?= $lang === 'en' ? 'active' : '' ?>">EN</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Modal "Chi siamo" -->
        <div class="modal fade" id="chiSiamoModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Chi Siamo - VR Open House</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <img src="image/745d5f52-0e02-42ee-b3f5-1a39e2aa9f9a.webp" alt="VR Open House" class="img-fluid mb-3" style="max-height: 300px; object-fit: cover;">
                        <div class="text-muted" style="font-size: 0.95rem; line-height: 1.6; max-height: 400px; overflow-y: auto;">
                            <h6><strong>L'innovazione al servizio dell'orientamento scolastico e della formazione</strong></h6>
                            <p>Negli ultimi anni, l'evoluzione tecnologica ha trasformato radicalmente i paradigmi della comunicazione e della formazione. Tra le innovazioni più significative, la Realtà Virtuale (VR) si è imposta come uno strumento capace di abbattere i confini tra spazio fisico e digitale, rivoluzionando il modo in cui viviamo eventi e attività didattiche.</p>
                            
                            <h6><strong>La Visione del Progetto</strong></h6>
                            <p>L'obiettivo primario è la creazione di un ecosistema digitale intuitivo che consenta agli Istituti di ogni ordine e grado di superare i limiti della presenza fisica. La piattaforma non è un semplice sito vetrina, ma un vero e proprio hub immersivo.</p>
                            
                            <h6><strong>Inclusività e Accessibilità</strong></h6>
                            <p>Uno dei punti di forza del sistema risiede nella sua capacità di favorire l'inclusione sociale e territoriale. Con VR Open House, studenti fuori sede, persone con mobilità ridotta e famiglie con poco tempo possono visitare l'istituto senza affrontare lunghi viaggi.</p>
                            
                            <h6><strong>Innovazione e Visibilità per gli Istituti</strong></h6>
                            <p>Per gli istituti, aderire a VR Open House rappresenta un'opportunità strategica di marketing territoriale. La piattaforma offre una vetrina internazionale che potenzia la visibilità e l'attrattiva verso i futuri iscritti.</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="container mt-4 mb-5">
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-body">
                        <h2><?= htmlspecialchars($istituto['nome']) ?></h2>
                        <p class="text-muted">
                            <i class="bi bi-mortarboard"></i> 
                            <?= $tipi_scuola_map[$lang][$istituto['tipo_scuola']] ?? $istituto['tipo_scuola'] ?>
                        </p>
                        
                        <?php if ($istituto['regione'] || $istituto['provincia']): ?>
                            <p>
                                <i class="bi bi-geo-alt"></i> 
                                <?php if ($istituto['provincia']): ?>
                                    <?= htmlspecialchars($istituto['provincia']) ?>
                                <?php endif; ?>
                                <?php if ($istituto['regione']): ?>
                                    <?php if ($istituto['provincia']): ?>, <?php endif; ?>
                                    <?= htmlspecialchars($istituto['regione']) ?>
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if ($istituto['indirizzo']): ?>
                            <p>
                                <i class="bi bi-house"></i> 
                                <?= nl2br(htmlspecialchars($istituto['indirizzo'])) ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if ($istituto['telefono']): ?>
                            <p>
                                <i class="bi bi-telephone"></i> 
                                <?= htmlspecialchars($istituto['telefono']) ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if ($istituto['email']): ?>
                            <p>
                                <i class="bi bi-envelope"></i> 
                                <a href="mailto:<?= htmlspecialchars($istituto['email']) ?>">
                                    <?= htmlspecialchars($istituto['email']) ?>
                                </a>
                            </p>
                        <?php endif; ?>
                        
                        <hr>
                        
                        <?php if (isset($istituto['descrizione']) && $istituto['descrizione']): ?>
                            <h5>Descrizione</h5>
                            <p><?= nl2br(htmlspecialchars($istituto['descrizione'])) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-header">
                        <h5 class="mb-0">Attività Disponibili</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($attivita)): ?>
                            <p class="text-muted">Nessuna attività disponibile al momento</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($attivita as $a): ?>
                                    <div class="list-group-item">
                                        <h6><?= htmlspecialchars($a['titolo']) ?></h6>
                                        <p class="small mb-1">
                                            <i class="bi bi-calendar"></i> 
                                            <?= date('d/m/Y H:i', strtotime($a['data_ora'])) ?>
                                        </p>
                                        <?php if ($a['supporta_vr']): ?>
                                            <span class="badge bg-info"><i class="bi bi-vr"></i> VR</span>
                                        <?php endif; ?>
                                        <span class="badge bg-secondary">
                                            <?= $a['prenotazioni_count'] ?>/<?= $a['max_partecipanti'] ?>
                                        </span>
                                        <a href="attivita_dettaglio.php?id=<?= $a['id'] ?>&lang=<?= $lang ?>" 
                                           class="btn btn-sm btn-primary mt-2 w-100">
                                            Dettagli
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


