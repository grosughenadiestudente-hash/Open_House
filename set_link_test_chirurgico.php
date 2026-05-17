<?php
require_once 'config.php';

try {
    $link = 'simulazione_test_chirurgico.html';
    $stmt = $pdo->prepare("UPDATE attivita_eventi SET Link_WebXR = ? WHERE Titolo = ? LIMIT 1");
    $stmt->execute([$link, 'test chirurgico']);
    $updated = $stmt->rowCount();
    echo "Aggiornate {$updated} righe.\n";
    if ($updated > 0) {
        $stmt = $pdo->prepare("SELECT ID_Attivita, Titolo, Link_WebXR FROM attivita_eventi WHERE Titolo = ? LIMIT 1");
        $stmt->execute(['test chirurgico']);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "ID: {$r['ID_Attivita']}, Titolo: {$r['Titolo']}, Link: {$r['Link_WebXR']}\n";
    }
} catch (Exception $e) {
    echo 'Errore: ' . $e->getMessage();
}
