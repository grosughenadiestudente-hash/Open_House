<?php
if (!isset($lang)) {
    $lang = 'it';
}

$nav_translations = [
    'it' => [
        'brand' => 'VR Open House',
        'istituti' => 'Istituti',
        'attivita' => 'Attività',
        'partner' => 'Partner',
        'chi_siamo' => 'Chi siamo',
        'accedi' => 'Accedi',
        'registrati' => 'Registrati',
        'dashboard' => 'Dashboard',
        'logout' => 'Logout',
        'chi_siamo_title' => 'Chi Siamo - VR Open House',
        'chiudi' => 'Chiudi',
    ],
    'en' => [
        'brand' => 'VR Open House',
        'istituti' => 'Institutions',
        'attivita' => 'Activities',
        'partner' => 'Partners',
        'chi_siamo' => 'About us',
        'accedi' => 'Login',
        'registrati' => 'Register',
        'dashboard' => 'Dashboard',
        'logout' => 'Logout',
        'chi_siamo_title' => 'About Us - VR Open House',
        'chiudi' => 'Close',
    ],
];

$nt = $nav_translations[$lang] ?? $nav_translations['it'];
$active_page = $active_page ?? '';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary site-header">
    <div class="container">
        <a class="navbar-brand" href="index.php?lang=<?= htmlspecialchars($lang) ?>">
            <i class="bi bi-mortarboard"></i> <?= htmlspecialchars($nt['brand']) ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link<?= $active_page === 'istituti' ? ' active' : '' ?>" href="istituti_elenco.php?lang=<?= htmlspecialchars($lang) ?>"><?= htmlspecialchars($nt['istituti']) ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $active_page === 'attivita' ? ' active' : '' ?>" href="attivita_elenco.php?lang=<?= htmlspecialchars($lang) ?>"><?= htmlspecialchars($nt['attivita']) ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $active_page === 'partner' ? ' active' : '' ?>" href="partner_istituti.php?lang=<?= htmlspecialchars($lang) ?>"><?= htmlspecialchars($nt['partner']) ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $active_page === 'chi_siamo' ? ' active' : '' ?>" href="#" data-bs-toggle="modal" data-bs-target="#chiSiamoModal"><?= htmlspecialchars($nt['chi_siamo']) ?></a>
                </li>
                <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php?lang=<?= htmlspecialchars($lang) ?>"><?= htmlspecialchars($nt['dashboard']) ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php"><?= htmlspecialchars($nt['logout']) ?></a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link<?= $active_page === 'login' ? ' active' : '' ?>" href="login.php?lang=<?= htmlspecialchars($lang) ?>"><?= htmlspecialchars($nt['accedi']) ?></a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a href="?lang=it" class="btn btn-sm btn-outline-light ms-lg-2<?= $lang === 'it' ? ' active' : '' ?>">IT</a>
                </li>
                <li class="nav-item">
                    <a href="?lang=en" class="btn btn-sm btn-outline-light ms-lg-2<?= $lang === 'en' ? ' active' : '' ?>">EN</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="modal fade" id="chiSiamoModal" tabindex="-1" aria-labelledby="chiSiamoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="chiSiamoModalLabel"><?= htmlspecialchars($nt['chi_siamo_title']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= htmlspecialchars($nt['chiudi']) ?>"></button>
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= htmlspecialchars($nt['chiudi']) ?></button>
            </div>
        </div>
    </div>
</div>
