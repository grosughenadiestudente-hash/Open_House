<?php
require_once 'config.php';

$error = '';
$success = '';
$showSuccessPopup = false;
$lang = $_GET['lang'] ?? 'it';

function getTableColumns(PDO $pdo, string $table): array {
    $stmt = $pdo->query("SHOW COLUMNS FROM {$table}");
    $columns = [];
    foreach ($stmt->fetchAll() as $row) {
        $columns[] = $row['Field'];
    }
    return $columns;
}

function getUserTable(PDO $pdo): string {
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('utenti', $tables, true)) {
        return 'utenti';
    }
    if (in_array('utenti_finali', $tables, true)) {
        return 'utenti_finali';
    }
    throw new RuntimeException('Nessuna tabella utenti disponibile (utenti / utenti_finali).');
}

function insertIstitutoPartner(PDO $pdo, array $data): void {
    $available = getTableColumns($pdo, 'istituti_e_partner');
    $map = [
        'Ragione_Sociale' => $data['Ragione_Sociale'] ?? null,
        'Tipologia' => $data['Tipologia'] ?? null,
        'CF_PIVA' => $data['CF_PIVA'] ?? null,
        'Cod_Mecc' => $data['Cod_Mecc'] ?? null,
        'Cod_REA' => $data['Cod_REA'] ?? null,
        'Indirizzo' => $data['Indirizzo'] ?? null,
        'Comune' => $data['Comune'] ?? null,
        'Provincia' => $data['Provincia'] ?? null,
        'Regione' => $data['Regione'] ?? null,
        'Coordinate_GPS' => $data['Coordinate_GPS'] ?? null,
        'Email' => $data['Email'] ?? null,
        'Telefono' => $data['Telefono'] ?? null,
        'descrizione' => $data['descrizione'] ?? null,
        'password' => $data['password'] ?? null,
        'Stato_Validazione' => 0,
    ];

    $fields = [];
    $values = [];
    $params = [];
    foreach ($map as $field => $value) {
        if (in_array($field, $available, true)) {
            $fields[] = $field;
            $values[] = '?';
            $params[] = $value;
        }
    }

    if (empty($fields)) {
        throw new RuntimeException('Tabella istituti_e_partner non compatibile con la registrazione.');
    }

    $sql = 'INSERT INTO istituti_e_partner (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_type = $_POST['user_type'] ?? '';
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($user_type) || empty($email) || empty($password)) {
        $error = 'Compila tutti i campi obbligatori.';
    } elseif ($password !== $confirm_password) {
        $error = 'Le password non corrispondono.';
    } elseif (strlen($password) < 8) {
        $error = 'La password deve essere di almeno 8 caratteri.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email non valida.';
    } else {
        try {
            if ($user_type === 'utente') {
                $nome = sanitize($_POST['utente_nome'] ?? '');
                $cognome = sanitize($_POST['utente_cognome'] ?? '');
                $tipo_utente = $_POST['utente_tipo'] ?? '';
                $data_nascita = $_POST['utente_data_nascita'] ?? null;
                $telefono = sanitize($_POST['utente_telefono'] ?? '');

                if (empty($nome) || empty($cognome) || empty($tipo_utente)) {
                    throw new RuntimeException('Compila tutti i campi obbligatori per utente finale.');
                }

                $userTable = getUserTable($pdo);
                $stmt = $pdo->prepare("INSERT INTO {$userTable} (nome, cognome, email, password, tipo_utente, data_nascita, telefono) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $nome,
                    $cognome,
                    $email,
                    hashPassword($password),
                    $tipo_utente,
                    $data_nascita ?: null,
                    $telefono ?: null,
                ]);

                $success = 'Registrazione completata. Controlla la tua email per la conferma.';
                sendHtmlEmail(
                    $email,
                    'Conferma registrazione Open House',
                    '<p>Ciao ' . htmlspecialchars($nome) . ',</p><p>la registrazione come ' . htmlspecialchars($tipo_utente) . ' e stata completata con successo.</p><p>Open House</p>'
                );
            } elseif ($user_type === 'istituto') {
                $ragioneSociale = sanitize($_POST['istituto_ragione_sociale'] ?? '');
                $tipologia = sanitize($_POST['istituto_tipologia'] ?? '');
                $cfPiva = sanitize($_POST['istituto_cf_piva'] ?? '');
                $codMecc = sanitize($_POST['istituto_cod_mecc'] ?? '');
                $indirizzo = sanitize($_POST['istituto_indirizzo'] ?? '');
                $comune = sanitize($_POST['istituto_comune'] ?? '');
                $provincia = sanitize($_POST['istituto_provincia'] ?? '');
                $regione = sanitize($_POST['istituto_regione'] ?? '');
                $coordinate = sanitize($_POST['istituto_coordinate_gps'] ?? '');
                $telefono = sanitize($_POST['istituto_telefono'] ?? '');
                $descrizione = sanitize($_POST['istituto_descrizione'] ?? '');

                if (empty($ragioneSociale) || empty($tipologia) || empty($provincia) || empty($regione)) {
                    throw new RuntimeException('Compila tutti i campi obbligatori per istituto.');
                }

                insertIstitutoPartner($pdo, [
                    'Ragione_Sociale' => $ragioneSociale,
                    'Tipologia' => $tipologia,
                    'CF_PIVA' => $cfPiva ?: null,
                    'Cod_Mecc' => $codMecc ?: null,
                    'Cod_REA' => null,
                    'Indirizzo' => $indirizzo ?: null,
                    'Comune' => $comune ?: null,
                    'Provincia' => $provincia,
                    'Regione' => $regione,
                    'Coordinate_GPS' => $coordinate ?: null,
                    'Email' => $email,
                    'Telefono' => $telefono ?: null,
                    'descrizione' => $descrizione ?: null,
                    'password' => hashPassword($password),
                ]);

                $success = 'Registrazione istituto completata. Riceverai conferma email.';
                sendHtmlEmail(
                    $email,
                    'Conferma registrazione istituto Open House',
                    '<p>Gentile ' . htmlspecialchars($ragioneSociale) . ',</p><p>la registrazione e stata ricevuta ed e in attesa di validazione.</p><p>Open House</p>'
                );
            } elseif ($user_type === 'partner') {
                $ragioneSociale = sanitize($_POST['partner_ragione_sociale'] ?? '');
                $tipoPartner = 'AZIENDA_FSL';
                $cfPiva = sanitize($_POST['partner_cf_piva'] ?? '');
                $codRea = sanitize($_POST['partner_cod_rea'] ?? '');
                $indirizzo = sanitize($_POST['partner_indirizzo'] ?? '');
                $comune = sanitize($_POST['partner_comune'] ?? '');
                $provincia = sanitize($_POST['partner_provincia'] ?? '');
                $regione = sanitize($_POST['partner_regione'] ?? '');
                $coordinate = sanitize($_POST['partner_coordinate_gps'] ?? '');
                $telefono = sanitize($_POST['partner_telefono'] ?? '');
                $descrizione = sanitize($_POST['partner_descrizione'] ?? '');

                if (empty($ragioneSociale) || empty($codRea) || empty($provincia) || empty($regione)) {
                    throw new RuntimeException('Compila tutti i campi obbligatori per partner FSL (incluso Codice REA).');
                }

                insertIstitutoPartner($pdo, [
                    'Ragione_Sociale' => $ragioneSociale,
                    'Tipologia' => $tipoPartner,
                    'CF_PIVA' => $cfPiva ?: null,
                    'Cod_REA' => $codRea ?: null,
                    'Indirizzo' => $indirizzo ?: null,
                    'Comune' => $comune ?: null,
                    'Provincia' => $provincia,
                    'Regione' => $regione,
                    'Coordinate_GPS' => $coordinate ?: null,
                    'Email' => $email,
                    'Telefono' => $telefono ?: null,
                    'descrizione' => $descrizione ?: null,
                    'password' => hashPassword($password),
                ]);

                $success = 'Registrazione partner completata. Riceverai conferma email.';
                sendHtmlEmail(
                    $email,
                    'Conferma registrazione partner Open House',
                    '<p>Gentile ' . htmlspecialchars($ragioneSociale) . ',</p><p>la registrazione partner e stata ricevuta ed e in attesa di validazione.</p><p>Open House</p>'
                );
            } else {
                throw new RuntimeException('Tipo utente non valido.');
            }

            $showSuccessPopup = true;
        } catch (PDOException $e) {
            if ((string) $e->getCode() === '23000') {
                $error = 'Email gia registrata.';
            } else {
                $error = 'Errore durante la registrazione: ' . $e->getMessage();
            }
        } catch (RuntimeException $e) {
            $error = $e->getMessage();
        }
    }
}

$translations = [
    'it' => [
        'title' => 'Registrazione - Open House',
        'register' => 'Registrati',
        'user_type' => 'Tipo utente',
        'istituto' => 'Istituto',
        'utente' => 'Utente',
        'partner' => 'Partner VR/FSL',
        'nome' => 'Nome',
        'cognome' => 'Cognome',
        'ragione_sociale' => 'Ragione sociale',
        'tipo_partner' => 'Tipologia partner',
        'citta' => 'Citta',
        'email' => 'Email',
        'password' => 'Password',
        'confirm_password' => 'Conferma Password',
        'tipo_scuola' => 'Tipo scuola',
        'tipo_utente' => 'Tipo utente',
        'indirizzo' => 'Indirizzo',
        'telefono' => 'Telefono',
        'descrizione' => 'Descrizione',
        'data_nascita' => 'Data di nascita',
        'has_account' => 'Hai già un account?',
        'login' => 'Accedi'
    ],
    'en' => [
        'title' => 'Register - Open House',
        'register' => 'Register',
        'user_type' => 'User type',
        'istituto' => 'Institution',
        'utente' => 'User',
        'partner' => 'VR/FSL Partner',
        'nome' => 'Name',
        'cognome' => 'Surname',
        'ragione_sociale' => 'Company name',
        'tipo_partner' => 'Partner type',
        'citta' => 'City',
        'email' => 'Email',
        'password' => 'Password',
        'confirm_password' => 'Confirm Password',
        'tipo_scuola' => 'School type',
        'tipo_utente' => 'User type',
        'indirizzo' => 'Address',
        'telefono' => 'Phone',
        'descrizione' => 'Description',
        'data_nascita' => 'Date of birth',
        'has_account' => 'Already have an account?',
        'login' => 'Login'
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

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h2 class="card-title text-center mb-4"><?= $t['register'] ?></h2>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" id="registerForm">
                            <div class="mb-3">
                                <label for="user_type" class="form-label"><?= $t['user_type'] ?> *</label>
                                <select class="form-select" id="user_type" name="user_type" required onchange="toggleFields()">
                                    <option value="">-- Seleziona --</option>
                                    <option value="istituto"><?= $t['istituto'] ?></option>
                                    <option value="utente"><?= $t['utente'] ?></option>
                                    <option value="partner"><?= $t['partner'] ?></option>
                                </select>
                            </div>
                            
                            <!-- Campi Istituto -->
                            <div id="istitutoFields" style="display: none;">
                                <div class="mb-3">
                                    <label for="istituto_ragione_sociale" class="form-label">Ragione sociale *</label>
                                    <input type="text" class="form-control" id="istituto_ragione_sociale" name="istituto_ragione_sociale" data-required-for="istituto">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="istituto_tipologia" class="form-label">Tipologia *</label>
                                    <select class="form-select" id="istituto_tipologia" name="istituto_tipologia" data-required-for="istituto">
                                        <option value="">-- Seleziona --</option>
                                        <option value="SCUOLA INFANZIA">Scuola dell'Infanzia</option>
                                        <option value="SCUOLA PRIMARIA">Scuola Primaria</option>
                                        <option value="SCUOLA PRIMO GRADO">Scuola Secondaria di Primo Grado</option>
                                        <option value="SCUOLA SECONDARIA DI SECONDO GRADO">Scuola Secondaria di Secondo Grado</option>
                                        <option value="UNIVERSITA">Universita</option>
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="istituto_cf_piva" class="form-label">CF/PIVA</label>
                                        <input type="text" class="form-control" id="istituto_cf_piva" name="istituto_cf_piva">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="istituto_cod_mecc" class="form-label">Codice Meccanografico *</label>
                                        <input type="text" class="form-control" id="istituto_cod_mecc" name="istituto_cod_mecc" data-required-for="istituto">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="istituto_indirizzo" class="form-label"><?= $t['indirizzo'] ?></label>
                                    <textarea class="form-control" id="istituto_indirizzo" name="istituto_indirizzo" rows="2"></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="istituto_regione" class="form-label">Regione *</label>
                                        <select class="form-select" id="istituto_regione" name="istituto_regione" data-required-for="istituto" onchange="populateProvince('istituto_regione', 'istituto_provincia')">
                                            <option value="">-- Seleziona una Regione --</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="istituto_provincia" class="form-label">Provincia *</label>
                                        <select class="form-select" id="istituto_provincia" name="istituto_provincia" data-required-for="istituto">
                                            <option value="">-- Seleziona una Provincia --</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="istituto_comune" class="form-label">Comune/Citta</label>
                                    <input type="text" class="form-control" id="istituto_comune" name="istituto_comune" placeholder="Es. Milano">
                                </div>

                                <div class="mb-3">
                                    <label for="istituto_coordinate_gps" class="form-label">Coordinate GPS</label>
                                    <input type="text" class="form-control" id="istituto_coordinate_gps" name="istituto_coordinate_gps" placeholder="Es. 45.4642, 9.1900">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="istituto_telefono" class="form-label"><?= $t['telefono'] ?></label>
                                    <input type="tel" class="form-control" id="istituto_telefono" name="istituto_telefono">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="istituto_descrizione" class="form-label"><?= $t['descrizione'] ?></label>
                                    <textarea class="form-control" id="istituto_descrizione" name="istituto_descrizione" rows="3"></textarea>
                                </div>
                            </div>

                            <!-- Campi Partner -->
                            <div id="partnerFields" style="display: none;">
                                <div class="mb-3">
                                    <label for="partner_ragione_sociale" class="form-label"><?= $t['ragione_sociale'] ?> *</label>
                                    <input type="text" class="form-control" id="partner_ragione_sociale" name="partner_ragione_sociale" data-required-for="partner">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label"><?= $t['tipo_partner'] ?></label>
                                    <input type="text" class="form-control" value="Azienda FSL" readonly>
                                    <input type="hidden" id="partner_tipologia" name="partner_tipologia" value="AZIENDA_FSL">
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="partner_cf_piva" class="form-label">CF/PIVA</label>
                                        <input type="text" class="form-control" id="partner_cf_piva" name="partner_cf_piva">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="partner_cod_rea" class="form-label">Codice REA *</label>
                                        <input type="text" class="form-control" id="partner_cod_rea" name="partner_cod_rea" data-required-for="partner">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="partner_comune" class="form-label"><?= $t['citta'] ?></label>
                                        <input type="text" class="form-control" id="partner_comune" name="partner_comune">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="partner_indirizzo" class="form-label"><?= $t['indirizzo'] ?></label>
                                    <textarea class="form-control" id="partner_indirizzo" name="partner_indirizzo" rows="2"></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="partner_regione" class="form-label">Regione *</label>
                                        <select class="form-select" id="partner_regione" name="partner_regione" data-required-for="partner" onchange="populateProvince('partner_regione', 'partner_provincia')">
                                            <option value="">-- Seleziona una Regione --</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="partner_provincia" class="form-label">Provincia *</label>
                                        <select class="form-select" id="partner_provincia" name="partner_provincia" data-required-for="partner">
                                            <option value="">-- Seleziona una Provincia --</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="partner_coordinate_gps" class="form-label">Coordinate GPS</label>
                                    <input type="text" class="form-control" id="partner_coordinate_gps" name="partner_coordinate_gps" placeholder="Es. 45.4642, 9.1900">
                                </div>

                                <div class="mb-3">
                                    <label for="partner_telefono" class="form-label"><?= $t['telefono'] ?></label>
                                    <input type="tel" class="form-control" id="partner_telefono" name="partner_telefono">
                                </div>

                                <div class="mb-3">
                                    <label for="partner_descrizione" class="form-label"><?= $t['descrizione'] ?></label>
                                    <textarea class="form-control" id="partner_descrizione" name="partner_descrizione" rows="3"></textarea>
                                </div>
                            </div>
                            
                            <!-- Campi Utente -->
                            <div id="utenteFields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="utente_nome" class="form-label"><?= $t['nome'] ?> *</label>
                                        <input type="text" class="form-control" id="utente_nome" name="utente_nome" data-required-for="utente">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="utente_cognome" class="form-label"><?= $t['cognome'] ?> *</label>
                                        <input type="text" class="form-control" id="utente_cognome" name="utente_cognome" data-required-for="utente">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="utente_tipo" class="form-label"><?= $t['tipo_utente'] ?> *</label>
                                    <select class="form-select" id="utente_tipo" name="utente_tipo" data-required-for="utente">
                                        <option value="">-- Seleziona --</option>
                                        <option value="studente">Studente</option>
                                        <option value="genitore">Genitore</option>
                                        <option value="docente">Docente</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="utente_data_nascita" class="form-label"><?= $t['data_nascita'] ?></label>
                                    <input type="date" class="form-control" id="utente_data_nascita" name="utente_data_nascita">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="utente_telefono" class="form-label"><?= $t['telefono'] ?></label>
                                    <input type="tel" class="form-control" id="utente_telefono" name="utente_telefono">
                                </div>
                            </div>
                            
                            <!-- Campi comuni -->
                            <div class="mb-3">
                                <label for="email" class="form-label"><?= $t['email'] ?> *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label"><?= $t['password'] ?> *</label>
                                <input type="password" class="form-control" id="password" name="password" required minlength="8">
                                <small class="form-text text-muted">Minimo 8 caratteri</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label"><?= $t['confirm_password'] ?> *</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3"><?= $t['register'] ?></button>
                        </form>
                        
                        <div class="text-center">
                            <p class="mb-0"><?= $t['has_account'] ?> <a href="login.php?lang=<?= $lang ?>"><?= $t['login'] ?></a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="province_regioni.js"></script>
    <script>
        function toggleFields() {
            const userType = document.getElementById('user_type').value;
            document.getElementById('istitutoFields').style.display = userType === 'istituto' ? 'block' : 'none';
            document.getElementById('utenteFields').style.display = userType === 'utente' ? 'block' : 'none';
            document.getElementById('partnerFields').style.display = userType === 'partner' ? 'block' : 'none';

            // Attiva required solo per i campi del blocco visibile
            document.querySelectorAll('[data-required-for]').forEach(function(field) {
                field.required = field.getAttribute('data-required-for') === userType;
            });

            if (userType === 'istituto') {
                initRegionProvinceSelects('istituto_regione', 'istituto_provincia');
            }
            if (userType === 'partner') {
                initRegionProvinceSelects('partner_regione', 'partner_provincia');
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            initRegionProvinceSelects('istituto_regione', 'istituto_provincia');
            initRegionProvinceSelects('partner_regione', 'partner_provincia');
            toggleFields();
        });
    </script>

    <?php if ($showSuccessPopup): ?>
    <script>
        alert('Registrazione completata. Ti abbiamo inviato una email di conferma.');
    </script>
    <?php endif; ?>
</body>
</html>


