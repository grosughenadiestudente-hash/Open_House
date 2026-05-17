<?php
require_once 'config.php';

echo "<h2>Crea partner 'FSL Medpark' e attività 'test chirurgico'</h2>\n";

$partnerName = 'FSL Medpark';
$partnerEmail = 'info@fslmedpark.it';
$attivitaTitolo = 'test chirurgico';

try {
    // Controlla se esiste già il partner
    $stmt = $pdo->prepare("SELECT ID_Ente FROM istituti_e_partner WHERE Ragione_Sociale = ? LIMIT 1");
    $stmt->execute([$partnerName]);
    $partner = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$partner) {
        $stmt = $pdo->prepare("INSERT INTO istituti_e_partner (Ragione_Sociale, Tipologia, Email, Indirizzo, Comune, Provincia, Regione, Stato_Validazione)
                               VALUES (?, 'AZIENDA', ?, 'Via Medpark 1', 'Milano', 'MI', 'LOMBARDIA', 1)");
        $stmt->execute([$partnerName, $partnerEmail]);
        $partnerId = (int)$pdo->lastInsertId();
        echo "✓ Partner creato: {$partnerName} (ID: {$partnerId})<br>\n";
    } else {
        $partnerId = (int)$partner['ID_Ente'];
        echo "✓ Partner già presente: {$partnerName} (ID: {$partnerId})<br>\n";
        $pdo->prepare("UPDATE istituti_e_partner SET Stato_Validazione = 1 WHERE ID_Ente = ?")->execute([$partnerId]);
        echo "✓ Stato_Validazione impostato a 1 per ID {$partnerId}<br>\n";
    }

    // Crea o aggiorna l'attività 'test chirurgico' per questo partner nella tabella attivita_eventi
    $stmt = $pdo->prepare("SELECT ID_Attivita FROM attivita_eventi WHERE FK_Ente_Organizzatore = ? AND Titolo = ? LIMIT 1");
    $stmt->execute([$partnerId, $attivitaTitolo]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    $dataOra = date('Y-m-d H:i:s', strtotime('+7 days'));

    if (!$existing) {
        $stmt = $pdo->prepare("INSERT INTO attivita_eventi (FK_Ente_Organizzatore, Titolo, Descrizione, Link_WebXR, Data_Ora, Max_Posti, Flag_FSL, Tipo_Attivita, Durata_Minuti, Supporta_VR, Materiali_URL, Stato)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $partnerId,
            $attivitaTitolo,
            'Attività di prova: test chirurgico associato a partner FSL Medpark.',
            null,
            $dataOra,
            20,
            1,
            'laboratorio',
            60,
            0,
            null,
            'pubblicata'
        ]);
        $attId = (int)$pdo->lastInsertId();
        echo "✓ Attività creata (ID: {$attId}) e associata al partner ID {$partnerId}<br>\n";
    } else {
        $attId = (int)$existing['ID_Attivita'];
        $stmt = $pdo->prepare("UPDATE attivita_eventi SET Descrizione = ?, Link_WebXR = ?, Data_Ora = ?, Max_Posti = ?, Flag_FSL = ?, Tipo_Attivita = ?, Durata_Minuti = ?, Supporta_VR = ?, Materiali_URL = ?, Stato = ? WHERE ID_Attivita = ?");
        $stmt->execute([
            'Attività di prova: test chirurgico associato a partner FSL Medpark.',
            null,
            $dataOra,
            20,
            1,
            'laboratorio',
            60,
            0,
            null,
            'pubblicata',
            $attId
        ]);
        echo "✓ Attività aggiornata (ID: {$attId}) per partner ID {$partnerId}<br>\n";
    }

    echo "<br><a href=\"attivita_elenco.php?lang=it\">Vai a elenco attività</a>\n";

} catch (Exception $e) {
    echo "❌ Errore: " . $e->getMessage();
}

?>
