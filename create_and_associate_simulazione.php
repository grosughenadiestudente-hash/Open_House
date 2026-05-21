<?php
require_once 'config.php';

echo "<h2>Crea partner demo e associa simulazione chirurgica VR</h2>\n";

function tableHasColumn(PDO $pdo, string $tableName, string $columnName): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    $stmt->execute([$tableName, $columnName]);
    return (int)$stmt->fetchColumn() > 0;
}

$partnerName = 'Prova Produzione';
$partnerEmail = 'info@provaproduzione.it';
$partnerUrl = 'imstk_demo/index.html';
$attivitaTitolo = 'Tour Virtuale Graphiti';
$attivitaDescrizione = 'Demo web con simulazione procedurale di ago e tessuto deformabile, pensata per browser e visori VR supportati dal device. Dati precomputati su 3 minuti di durata.';
$dataOra = date('Y-m-d H:i:s', strtotime('+1 day'));

try {
    $hasTelefono = tableHasColumn($pdo, 'istituti_e_partner', 'Telefono');

    $stmt = $pdo->prepare("SELECT ID_Ente FROM istituti_e_partner WHERE Ragione_Sociale = ? LIMIT 1");
    $stmt->execute([$partnerName]);
    $partner = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$partner) {
        if ($hasTelefono) {
            $stmt = $pdo->prepare("INSERT INTO istituti_e_partner (
                Ragione_Sociale, Tipologia, Email, Indirizzo, Comune, Provincia, Regione,
                Cod_REA, Telefono, Stato_Validazione
            ) VALUES (?, 'AZIENDA', ?, 'Via Demo 1', 'Milano', 'MI', 'LOMBARDIA', 'VRDEMO001', NULL, 1)");
            $stmt->execute([$partnerName, $partnerEmail]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO istituti_e_partner (
                Ragione_Sociale, Tipologia, Email, Indirizzo, Comune, Provincia, Regione,
                Cod_REA, Stato_Validazione
            ) VALUES (?, 'AZIENDA', ?, 'Via Demo 1', 'Milano', 'MI', 'LOMBARDIA', 'VRDEMO001', 1)");
            $stmt->execute([$partnerName, $partnerEmail]);
        }
        $partnerId = (int)$pdo->lastInsertId();
        echo "✓ Partner creato: {$partnerName} (ID: {$partnerId})<br>\n";
    } else {
        $partnerId = (int)$partner['ID_Ente'];
        echo "✓ Partner già presente: {$partnerName} (ID: {$partnerId})<br>\n";
        $pdo->prepare("UPDATE istituti_e_partner SET Stato_Validazione = 1 WHERE ID_Ente = ?")->execute([$partnerId]);
        echo "✓ Partner validato (Stato_Validazione=1) per ID {$partnerId}<br>\n";
    }

    $stmt = $pdo->prepare("SELECT ID_Attivita, Titolo FROM attivita_eventi WHERE FK_Ente_Organizzatore = ? AND Titolo = ? LIMIT 1");
    $stmt->execute([$partnerId, $attivitaTitolo]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existing) {
        $stmt = $pdo->prepare("SELECT ID_Attivita, Titolo FROM attivita_eventi WHERE FK_Ente_Organizzatore = ? ORDER BY ID_Attivita ASC LIMIT 1");
        $stmt->execute([$partnerId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            echo "✓ Attività già presente trovata per il partner: ID {$existing['ID_Attivita']} ({$existing['Titolo']})<br>\n";
        }
    }

    if (!$existing) {
        $stmt = $pdo->prepare("INSERT INTO attivita_eventi (
            FK_Ente_Organizzatore, Titolo, Descrizione, Link_WebXR, Data_Ora, Max_Posti,
            Flag_FSL, Tipo_Attivita, Durata_Minuti, Supporta_VR, Materiali_URL, Stato
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $partnerId,
            $attivitaTitolo,
            $attivitaDescrizione,
            $partnerUrl,
            $dataOra,
            200,
            1,
            'laboratorio',
            180,
            1,
            'imstk_demo/data/simulation.json',
            'pubblicata'
        ]);
        $attId = (int)$pdo->lastInsertId();
        echo "✓ Attività creata (ID: {$attId}) e associata al partner ID {$partnerId}<br>\n";
    } else {
        $attId = (int)$existing['ID_Attivita'];
        $stmt = $pdo->prepare("UPDATE attivita_eventi SET
            Titolo = ?, Descrizione = ?, Link_WebXR = ?, Data_Ora = ?, Max_Posti = ?, Flag_FSL = ?,
            Tipo_Attivita = ?, Durata_Minuti = ?, Supporta_VR = ?, Materiali_URL = ?, Stato = ?
            WHERE ID_Attivita = ?");
        $stmt->execute([
            $attivitaTitolo,
            $attivitaDescrizione,
            $partnerUrl,
            $dataOra,
            200,
            1,
            'laboratorio',
            180,
            1,
            'imstk_demo/data/simulation.json',
            'pubblicata',
            $attId
        ]);
        echo "✓ Attività aggiornata/allineata (ID: {$attId}) per partner ID {$partnerId}<br>\n";
    }

    echo "<br><strong>Pronta per il web:</strong> la simulazione è pubblicata e associata al partner.<br>";
    echo "<a href=\"attivita_elenco.php?lang=it\">Vai all'elenco attività</a><br>";
    echo "<a href=\"attivita_dettaglio.php?id={$attId}&lang=it\">Apri dettaglio attività</a>\n";

} catch (Exception $e) {
    echo "❌ Errore: " . htmlspecialchars($e->getMessage());
}
?>
