<?php
require_once 'config.php';

echo "<h2>Crea partner 'Prova Produzione' e associa Graphiti</h2>\n";

$partnerName = 'Prova Produzione';
$partnerEmail = 'info@provaproduzione.it';
$graphitiUrl = 'https://www.virtualtour-360.it/vt/graphiti-h1d2/vt-web/';
$attivitaTitolo = 'Tour Virtuale Graphiti';

try {
    // Controlla se esiste già il partner
    $stmt = $pdo->prepare("SELECT ID_Ente FROM istituti_e_partner WHERE Ragione_Sociale = ? LIMIT 1");
    $stmt->execute([$partnerName]);
    $partner = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$partner) {
        $stmt = $pdo->prepare("INSERT INTO istituti_e_partner (Ragione_Sociale, Tipologia, Email, Indirizzo, Comune, Provincia, Regione, Cod_REA, Telefono, Stato_Validazione)
                               VALUES (?, 'AZIENDA', ?, 'Via Produzione 1', 'Milano', 'MI', 'LOMBARDIA', 'PRPROD001', '02-000000', 1)");
        $stmt->execute([$partnerName, $partnerEmail]);
        $partnerId = (int)$pdo->lastInsertId();
        echo "✓ Partner creato: {$partnerName} (ID: {$partnerId})<br>\n";
    } else {
        $partnerId = (int)$partner['ID_Ente'];
        echo "✓ Partner già presente: {$partnerName} (ID: {$partnerId})<br>\n";
        // Assicura che sia validato
        $pdo->prepare("UPDATE istituti_e_partner SET Stato_Validazione = 1 WHERE ID_Ente = ?")->execute([$partnerId]);
        echo "✓ Stato_Validazione impostato a 1 per ID {$partnerId}<br>\n";
    }

    // Associa/crea l'attività Graphiti per questo partner
    $stmt = $pdo->prepare("SELECT ID_Attivita FROM attivita_eventi WHERE FK_Ente_Organizzatore = ? AND Titolo = ? LIMIT 1");
    $stmt->execute([$partnerId, $attivitaTitolo]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    $dataOra = '2026-06-01 10:00:00';

    if (!$existing) {
        $stmt = $pdo->prepare("INSERT INTO attivita_eventi (FK_Ente_Organizzatore, Titolo, Descrizione, Link_WebXR, Data_Ora, Max_Posti, Flag_FSL, Tipo_Attivita, Durata_Minuti, Supporta_VR, Materiali_URL, Stato)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $partnerId,
            $attivitaTitolo,
            'Tour virtuale 360° fornito da Graphiti (virtual tour embed).',
            $graphitiUrl,
            $dataOra,
            50,
            1,
            'tour_virtuale',
            30,
            1,
            null,
            'pubblicata'
        ]);
        $attId = (int)$pdo->lastInsertId();
        echo "✓ Attività creata (ID: {$attId}) e associata al partner ID {$partnerId}<br>\n";
    } else {
        $attId = (int)$existing['ID_Attivita'];
        $stmt = $pdo->prepare("UPDATE attivita_eventi SET Descrizione = ?, Link_WebXR = ?, Data_Ora = ?, Max_Posti = ?, Flag_FSL = ?, Tipo_Attivita = ?, Durata_Minuti = ?, Supporta_VR = ?, Materiali_URL = ?, Stato = ? WHERE ID_Attivita = ?");
        $stmt->execute([
            'Tour virtuale 360° fornito da Graphiti (virtual tour embed).',
            $graphitiUrl,
            $dataOra,
            50,
            1,
            'tour_virtuale',
            30,
            1,
            null,
            'pubblicata',
            $attId
        ]);
        echo "✓ Attività aggiornata (ID: {$attId}) per partner ID {$partnerId}<br>\n";
    }

    // Optional: assegna attività senza ente (FK_Ente_Organizzatore IS NULL o 0) a questo partner
    $stmt = $pdo->prepare("UPDATE attivita_eventi SET FK_Ente_Organizzatore = ? WHERE FK_Ente_Organizzatore IS NULL OR FK_Ente_Organizzatore = 0");
    $stmt->execute([$partnerId]);
    $updated = $stmt->rowCount();
    if ($updated > 0) {
        echo "✓ Assegnate {$updated} attività orfane al partner ID {$partnerId}<br>\n";
    }

    echo "<br><a href=\"debug_attivita.php\">Apri debug_attivita</a><br>";
    echo "<a href=\"attivita_elenco.php?lang=it\">Vai a elenco attività</a>\n";

} catch (Exception $e) {
    echo "❌ Errore: " . $e->getMessage();
}
?>