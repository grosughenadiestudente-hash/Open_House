<?php
require_once 'config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attivita_id = intval($_POST['attivita_id'] ?? 0);
    $messaggio = sanitize($_POST['messaggio'] ?? '');
    
    if (!empty($messaggio) && $attivita_id > 0) {
        $user_id = $_SESSION['user_id'];
        $user_type = $_SESSION['user_type'];
        
        if ($user_type === 'istituto') {
            $stmt = $pdo->prepare("INSERT INTO messaggi_chat (attivita_id, istituto_id, messaggio) VALUES (?, ?, ?)");
            $stmt->execute([$attivita_id, $user_id, $messaggio]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO messaggi_chat (attivita_id, utente_id, messaggio) VALUES (?, ?, ?)");
            $stmt->execute([$attivita_id, $user_id, $messaggio]);
        }
    }
}

header('Location: attivita_partecipa.php?id=' . $attivita_id);
exit;
?>
