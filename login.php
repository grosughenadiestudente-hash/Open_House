<?php
require_once 'config.php';

$error = '';
$lang = $_GET['lang'] ?? 'it';

// Simple translations used in the login form
$translations = [
    'it' => [
        'title' => 'Login - Open House',
        'login' => 'Accedi',
        'user_type' => 'Tipo utente',
        'istituto' => 'Istituto',
        'utente' => 'Utente',
        'partner' => 'Partner',
        'admin' => 'Amministratore',
        'email' => 'Email',
        'password' => 'Password',
        'no_account' => 'Non hai un account?',
        'register' => 'Registrati'
    ],
    'en' => [
        'title' => 'Login - Open House',
        'login' => 'Login',
        'user_type' => 'User type',
        'istituto' => 'Institution',
        'utente' => 'User',
        'partner' => 'Partner',
        'admin' => 'Admin',
        'email' => 'Email',
        'password' => 'Password',
        'no_account' => 'No account?',
        'register' => 'Register'
    ]
];

$t = $translations[$lang] ?? $translations['it'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $user_type = $_POST['user_type'] ?? '';

    if (empty($email) || empty($password) || empty($user_type)) {
        $error = 'Compila tutti i campi';
    } else {
        try {
            $email = mb_strtolower($email);

            // Default: utenti table (local users)
            if ($user_type === 'utente') {
                $stmt = $pdo->prepare('SELECT id, nome, cognome, email, password FROM utenti WHERE email = ? LIMIT 1');
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user && verifyPassword($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_type'] = 'utente';
                    $_SESSION['user_name'] = trim(($user['nome'] ?? '') . ' ' . ($user['cognome'] ?? '')) ?: $user['email'];

                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error = 'Credenziali non valide';
                }

            } else {
                // For other types attempt to find a password column in related tables
                $tableMap = [
                    'istituto' => 'istituti_e_partner',
                    'partner' => 'istituti_e_partner',
                    'admin' => 'admin'
                ];

                $table = $tableMap[$user_type] ?? null;

                if ($table) {
                    // Check if table has a password column
                    $colStmt = $pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE 'password'");
                    $colStmt->execute();
                    $col = $colStmt->fetch();

                    if ($col) {
                        $stmt = $pdo->prepare("SELECT * FROM `{$table}` WHERE email = ? LIMIT 1");
                        $stmt->execute([$email]);
                        $row = $stmt->fetch();

                        if ($row && verifyPassword($password, $row['password'])) {
                            $_SESSION['user_id'] = $row['ID_Ente'] ?? $row['id'] ?? $row['ID'] ?? 0;
                            $_SESSION['user_type'] = $user_type;
                            $_SESSION['user_name'] = $row['Ragione_Sociale'] ?? $row['nome'] ?? $row['email'] ?? $email;

                            // Redirect by role
                            if ($user_type === 'istituto') {
                                header('Location: dashboard_istituto.php');
                            } elseif ($user_type === 'partner') {
                                header('Location: dashboard_partner.php');
                            } else {
                                header('Location: dashboard_admin.php');
                            }
                            exit;
                        } else {
                            $error = 'Credenziali non valide';
                        }
                    } else {
                        $error = 'Login non disponibile per questo tipo di account. Usa la procedura di accesso dedicata o contatta l\'amministratore.';
                    }
                } else {
                    $error = 'Tipo utente non valido.';
                }
            }
        } catch (PDOException $e) {
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                $error = 'Errore database: ' . $e->getMessage();
            } else {
                $error = 'Errore di sistema. Riprovare più tardi.';
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($t['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
    <?php $active_page = 'login'; include 'header.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h2 class="card-title text-center mb-4"><?= htmlspecialchars($t['login']) ?></h2>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="user_type" class="form-label"><?= htmlspecialchars($t['user_type']) ?></label>
                                <select class="form-select" id="user_type" name="user_type" required>
                                    <option value="">-- Seleziona --</option>
                                    <option value="istituto"><?= htmlspecialchars($t['istituto']) ?></option>
                                    <option value="utente"><?= htmlspecialchars($t['utente']) ?></option>
                                    <option value="partner"><?= htmlspecialchars($t['partner']) ?></option>
                                    <option value="admin"><?= htmlspecialchars($t['admin']) ?></option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label"><?= htmlspecialchars($t['email']) ?></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label"><?= htmlspecialchars($t['password']) ?></label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3"><?= htmlspecialchars($t['login']) ?></button>
                        </form>
                        
                        <div class="text-center">
                            <p class="mb-2"><?= $t['no_account'] ?></p>
                            <a href="register.php?lang=<?= $lang ?>" class="btn btn-outline-primary w-100"><?= $t['register'] ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


