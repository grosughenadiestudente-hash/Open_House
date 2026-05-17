<?php
if (!isset($lang)) $lang = $_GET['lang'] ?? 'it';
if (!isset($user_type)) $user_type = $_SESSION['user_type'] ?? '';
?>
<style>
    .navbar {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        z-index: 9999 !important;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var nav = document.querySelector('nav.navbar');
        if (nav) {
            document.body.style.paddingTop = nav.offsetHeight + 'px';
        }
    });
</script>
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
                
                <?php if ($user_type === 'istituto'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard_istituto.php?lang=<?= $lang ?>">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="attivita_gestione.php?lang=<?= $lang ?>">Gestisci Attività</a>
                    </li>
                <?php elseif ($user_type === 'utente'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard_utente.php?lang=<?= $lang ?>">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="attivita_elenco.php?lang=<?= $lang ?>">Attività</a>
                    </li>
                <?php elseif ($user_type === 'partner'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard_partner.php?lang=<?= $lang ?>">Dashboard Partner</a>
                    </li>
                <?php elseif ($user_type === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard_admin.php?lang=<?= $lang ?>">Dashboard Admin</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_validazione_enti.php?lang=<?= $lang ?>">Valida Enti</a>
                    </li>
                <?php endif; ?>
            </ul>
            
            <ul class="navbar-nav">
                <?php if (!empty($user_type)): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-2">
                        <a href="login.php?lang=<?= $lang ?>" class="btn btn-light btn-sm">Accedi</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a href="register.php?lang=<?= $lang ?>" class="btn btn-light btn-sm">Registrati</a>
                    </li>
                <?php endif; ?>
                
                <li class="nav-item ms-3">
                    <a href="<?= basename($_SERVER['PHP_SELF']) ?>?lang=it" class="btn btn-outline-light btn-sm <?= $lang === 'it' ? 'active' : '' ?>">IT</a>
                </li>
                <li class="nav-item ms-1">
                    <a href="<?= basename($_SERVER['PHP_SELF']) ?>?lang=en" class="btn btn-outline-light btn-sm <?= $lang === 'en' ? 'active' : '' ?>">EN</a>
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
                    <p>Uno dei punti di forza del sistema risiede nella sua capacità di favorire l'inclusione sociale e territoriale. Spesso, la scelta della scuola superiore o dell'università è limitata da impedimenti logistici o economici. Con VR Open House, studenti fuori sede, persone con mobilità ridotta e famiglie con poco tempo possono visitare l'istituto senza affrontare lunghi viaggi.</p>
                    
                    <h6><strong>Innovazione e Visibilità per gli Istituti</strong></h6>
                    <p>Per gli istituti, aderire a VR Open House rappresenta un'opportunità strategica di marketing territoriale. In un panorama educativo sempre più competitivo, la piattaforma offre una vetrina internazionale che potenzia la visibilità e l'attrattiva verso i futuri iscritti.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
            </div>
        </div>
    </div>
</div>

