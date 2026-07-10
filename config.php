<?php
/**
 * Configurazione Open Day Virtuale Platform
 * 
 * PER INFINITYFREE:
 * 1. Modifica DB_HOST, DB_NAME, DB_USER, DB_PASS con le credenziali del tuo hosting
 * 2. Modifica BASE_URL con il tuo dominio
 * 3. Genera una SECRET_KEY sicura
 */

// Configurazione database
// ⚠️ MODIFICA QUESTI VALORI CON LE CREDENZIALI DEL TUO HOSTING
define('DB_HOST', '127.0.0.1');  // In locale XAMPP usa TCP esplicito
define('DB_PORT', '3306');  // Porta MySQL locale
define('DB_NAME', 'open_house');  // Nome database MySQL/MariaDB in XAMPP (phpMyAdmin)
define('DB_USER', 'root');  // Utente MySQL locale di default
define('DB_PASS', '');  // Password vuota di default in XAMPP

// Credenziali InfinityFree (da riattivare quando pubblichi)
// define('DB_HOST', 'sqlXXX.infinityfree.com');  // Oppure 'localhost' se indicato dal tuo hosting
// define('DB_NAME', 'if0_40204014_vr_client');  // Nome COMPLETO con prefisso
// define('DB_USER', 'if0_40204014');  // Nome COMPLETO con prefisso (es. if0_40204014_dbuser)
// define('DB_PASS', '2jXH8Vrxru8ww');

// Configurazione sicurezza
// ⚠️ GENERA UNA CHIAVE SICURA: https://randomkeygen.com/
define('SECRET_KEY', 'n-k(VH=O:><6PT=q');
define('SESSION_LIFETIME', 43200); // 12 ore

// Configurazione percorso
// ⚠️ MODIFICA CON IL TUO DOMINIO
// XAMPP default: cartella sotto htdocs (Apache porta 80). Se usi un altro host/porta, aggiorna qui.
define('BASE_URL', 'http://localhost/Open_House');
// In produzione imposta il dominio pubblico, es: 'https://ticreators.great-site.net'

// Timezone
date_default_timezone_set('Europe/Rome');

// Modalità debug (disabilita in produzione)
define('DEBUG_MODE', true); // In locale: true per vedere errore reale di connessione

// Connessione database
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        die("Errore connessione database: " . $e->getMessage());
    } else {
        // In produzione, mostra messaggio generico
        die("Errore connessione database. Verifica le credenziali in config.php");
    }
}

// Avvia sessione
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', (string) SESSION_LIFETIME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Funzioni di utilità
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireRole($roles) {
    requireLogin();
    if (!in_array($_SESSION['user_type'], $roles, true)) {
        header('Location: dashboard.php');
        exit;
    }
}

function requireIstituto() {
    requireRole(['istituto']);
}

function requireUtente() {
    requireRole(['utente']);
}

function requirePartner() {
    requireRole(['partner']);
}

function requireAdmin() {
    requireRole(['admin']);
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function sendHtmlEmail($to, $subject, $htmlBody) {
    if (empty($to)) {
        return false;
    }

    $host = parse_url(BASE_URL, PHP_URL_HOST) ?: 'localhost';
    $fromAddress = 'no-reply@' . $host;

    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=UTF-8';
    $headers[] = 'From: Open House <' . $fromAddress . '>';
    $headers[] = 'Reply-To: ' . $fromAddress;

    return @mail($to, $subject, $htmlBody, implode("\r\n", $headers));
}

function tableHasColumn(PDO $pdo, string $tableName, string $columnName): bool {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    $stmt->execute([$tableName, $columnName]);
    return (int) $stmt->fetchColumn() > 0;
}

function getTableColumns(PDO $pdo, string $table): array {
    $stmt = $pdo->query('SHOW COLUMNS FROM ' . $table);
    $columns = [];
    foreach ($stmt->fetchAll() as $row) {
        $columns[] = $row['Field'];
    }
    return $columns;
}

function getUserTable(PDO $pdo, bool $required = true): ?string {
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('utenti', $tables, true)) {
        $cached = 'utenti';
        return $cached;
    }
    if (in_array('utenti_finali', $tables, true)) {
        $cached = 'utenti_finali';
        return $cached;
    }
    if ($required) {
        throw new RuntimeException('Nessuna tabella utenti disponibile (utenti / utenti_finali).');
    }
    return null;
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

/** Normalizza riga attivita_eventi per i form legacy (chiavi minuscole). */
function normalizeAttivitaRow(array $row): array {
    return [
        'titolo' => $row['Titolo'] ?? $row['titolo'] ?? '',
        'descrizione' => $row['Descrizione'] ?? $row['descrizione'] ?? '',
        'tipo_attivita' => $row['Tipo_Attivita'] ?? $row['tipo_attivita'] ?? 'presentazione',
        'data_ora' => $row['Data_Ora'] ?? $row['data_ora'] ?? '',
        'durata_minuti' => $row['Durata_Minuti'] ?? $row['durata_minuti'] ?? 60,
        'max_partecipanti' => $row['Max_Posti'] ?? $row['max_partecipanti'] ?? 50,
        'supporta_vr' => $row['Supporta_VR'] ?? $row['supporta_vr'] ?? 0,
        'url_vr' => $row['Link_WebXR'] ?? $row['url_vr'] ?? '',
        'materiali_url' => $row['Materiali_URL'] ?? $row['materiali_url'] ?? '',
        'stato' => $row['Stato'] ?? $row['stato'] ?? 'bozza',
    ];
}
?>
