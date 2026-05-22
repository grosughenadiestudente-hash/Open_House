<?php
require_once 'config.php';

$lang = $_GET['lang'] ?? 'it';
$istituto_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT
                        ID_Ente as id,
                        Ragione_Sociale as nome,
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

<body class="bg-light">
    <?php include 'navbar.php'; ?>
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


