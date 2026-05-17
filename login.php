<?php
require_once 'config.php';

$error = '';
$lang = $_GET['lang'] ?? 'it';

function getAuthUserTable(PDO $pdo): ?string {
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('utenti', $tables, true)) {
        return 'utenti';
    }
    if (in_array('utenti_finali', $tables, true)) {
        return 'utenti_finali';
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $user_type = $_POST['user_type'] ?? '';
    
    if (empty($email) || empty($password) || empty($user_type)) {
        $error = 'Compila tutti i campi';
    } else {
        $user = null;
        $session_type = $user_type;
        $user_subtype = null;

        if ($user_type === 'istituto') {
            $stmt = $pdo->prepare("SELECT ID_Ente as id, Ragione_Sociale as nome, email, password FROM istituti_e_partner WHERE email = ? AND password IS NOT NULL");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
        } elseif ($user_type === 'partner') {
            $stmt = $pdo->prepare("SELECT ID_Ente as id, Ragione_Sociale as nome, Email as email, password, Tipologia as tipo_utente
                                   FROM istituti_e_partner
                                   WHERE Email = ?
                                   AND password IS NOT NULL
                                   AND (Tipologia LIKE 'ARENA%' OR Tipologia LIKE 'AZIENDA%' OR Tipologia = 'ENTE_PUBBLICO' OR Tipologia LIKE 'PARTNER%')");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            $user_subtype = $user['tipo_utente'] ?? null;
        } elseif ($user_type === 'admin') {
            $authTable = getAuthUserTable($pdo);
            if ($authTable !== null) {
                $stmt = $pdo->prepare("SELECT id, nome, cognome, email, password, tipo_utente FROM {$authTable} WHERE email = ? AND tipo_utente = 'admin'");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                $user_subtype = $user['tipo_utente'] ?? null;
            }
        } else {
            $authTable = getAuthUserTable($pdo);
            if ($authTable !== null) {
                $stmt = $pdo->prepare("SELECT id, nome, cognome, email, password, tipo_utente FROM {$authTable} WHERE email = ? AND tipo_utente IN ('studente', 'genitore', 'docente', 'utente')");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                $user_subtype = $user['tipo_utente'] ?? null;
            }
        }
        
        if ($user && verifyPassword($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $session_type;
            $_SESSION['user_subtype'] = $user_subtype;
            $_SESSION['user_name'] = $user['nome'] . (isset($user['cognome']) ? ' ' . $user['cognome'] : '');
            $_SESSION['user_email'] = $user['email'];
            
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Credenziali non valide';
        }
    }
}

$translations = [
    'it' => [
        'title' => 'Login - Open House',
        'login' => 'Accedi',
        'email' => 'Email',
        'password' => 'Password',
        'user_type' => 'Tipo utente',
        'istituto' => 'Istituto',
        'utente' => 'Utente',
        'partner' => 'Partner VR/FSL',
        'admin' => 'Amministratore',
        'no_account' => 'Non hai un account?',
        'register' => 'Registrati',
        'error' => 'Errore'
    ],
    'en' => [
        'title' => 'Login - Open House',
        'login' => 'Login',
        'email' => 'Email',
        'password' => 'Password',
        'user_type' => 'User type',
        'istituto' => 'Institution',
        'utente' => 'User',
        'partner' => 'VR/FSL Partner',
        'admin' => 'Administrator',
        'no_account' => "Don't have an account?",
        'register' => 'Register',
        'error' => 'Error'
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

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h2 class="card-title text-center mb-4"><?= $t['login'] ?></h2>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="user_type" class="form-label"><?= $t['user_type'] ?></label>
                                <select class="form-select" id="user_type" name="user_type" required>
                                    <option value="">-- Seleziona --</option>
                                    <option value="istituto"><?= $t['istituto'] ?></option>
                                    <option value="utente"><?= $t['utente'] ?></option>
                                    <option value="partner"><?= $t['partner'] ?></option>
                                    <option value="admin"><?= $t['admin'] ?></option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label"><?= $t['email'] ?></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label"><?= $t['password'] ?></label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3"><?= $t['login'] ?></button>
                        </form>
                        
                        <div class="text-center">
                            <p class="mb-0"><?= $t['no_account'] ?> <a href="register.php?lang=<?= $lang ?>"><?= $t['register'] ?></a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


