<?php
require_once 'config.php';

echo "<h2>Inserimento Attivita: Simulatore Spaziale VR</h2>";

$partnerName = 'Prova Aeronautica';
$partnerEmail = 'info@prova-aeronautica.it';
$simUrl = 'https://www.flightsimulator.com/';
$attivitaTitolo = 'Simulatore di Volo';

try {
    // Crea partner FSL se non esiste
    $stmt = $pdo->prepare("SELECT ID_Ente FROM istituti_e_partner WHERE Ragione_Sociale = ? LIMIT 1");
    $stmt->execute([$partnerName]);
    $partner = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$partner) {
        $stmt = $pdo->prepare("INSERT INTO istituti_e_partner (Ragione_Sociale, Tipologia, Email, Indirizzo, Comune, Provincia, Regione, Cod_REA, Telefono, Stato_Validazione)
                               VALUES (?, 'AZIENDA', ?, 'Via del Volo 12', 'Catania', 'CT', 'SICILIA', 'CTAERO123', '095555555', 1)");
        $stmt->execute([$partnerName, $partnerEmail]);
        $partnerId = (int) $pdo->lastInsertId();
        echo "✓ Partner FSL creato: {$partnerName}<br>";
    } else {
        $partnerId = (int) $partner['ID_Ente'];
        echo "✓ Partner FSL gia presente: {$partnerName}<br>";
    }

    // Inserisci attivita se non esiste
    $stmt = $pdo->prepare("SELECT ID_Attivita FROM attivita_eventi WHERE FK_Ente_Organizzatore = ? AND Titolo = ? LIMIT 1");
    $stmt->execute([$partnerId, $attivitaTitolo]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existing) {
        $stmt = $pdo->prepare("INSERT INTO attivita_eventi
            (FK_Ente_Organizzatore, Titolo, Descrizione, Link_WebXR, Data_Ora, Max_Posti, Flag_FSL, Tipo_Attivita, Durata_Minuti, Supporta_VR, Materiali_URL, Stato)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $partnerId,
            $attivitaTitolo,
            'Simulazione di volo con scenari realistici e cockpit interattivo.',
            $simUrl,
            '2026-05-20 10:00:00',
            30,
            0,
            'laboratorio',
            90,
            1,
            null,
            'pubblicata'
        ]);
        echo "✓ Attivita creata: {$attivitaTitolo}<br>";
    } else {
        $stmt = $pdo->prepare("UPDATE attivita_eventi
            SET Descrizione = ?, Link_WebXR = ?, Data_Ora = ?, Max_Posti = ?, Flag_FSL = ?, Tipo_Attivita = ?, Durata_Minuti = ?, Supporta_VR = ?, Materiali_URL = ?, Stato = ?
            WHERE ID_Attivita = ?");
        $stmt->execute([
            'Simulazione di volo con scenari realistici e cockpit interattivo.',
            $simUrl,
            '2026-05-20 10:00:00',
            30,
            0,
            'laboratorio',
            90,
            1,
            null,
            'pubblicata',
            $existing['ID_Attivita']
        ]);
        echo "✓ Attivita aggiornata: {$attivitaTitolo}<br>";
    }

    echo "<br><a href=\"attivita_elenco.php\">Vedi attivita</a>";
} catch (Exception $e) {
    echo "❌ Errore: " . $e->getMessage();
}
?>
