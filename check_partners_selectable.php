<?php
require_once 'config.php';

echo "<h2>Verifica Partner</h2>";

try {
    $stmt = $pdo->query("SELECT ID_Ente, Ragione_Sociale, Tipologia, Stato_Validazione, Email FROM istituti_e_partner ORDER BY Tipologia, Ragione_Sociale");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$rows) {
        echo "❌ Nessun partner/istituto trovato nel database.<br>";
        exit;
    }

    echo "<table border='1' cellpadding='8'>";
    echo "<tr><th>ID</th><th>Nome</th><th>Tipologia</th><th>Stato_Validazione</th><th>Email</th><th>Selezionabile</th><th>Azione</th></tr>";
    foreach ($rows as $r) {
        $sel = ($r['Stato_Validazione'] == 1) ? '✓' : '✖';
        $action = '';
        if ($r['Stato_Validazione'] == 1) {
            $action = "<a href=\"associate_graphiti.php?partner_id={$r['ID_Ente']}\">Associa Graphiti a questo partner</a>";
        }
        echo "<tr>";
        echo "<td>{$r['ID_Ente']}</td>";
        echo "<td>" . htmlspecialchars($r['Ragione_Sociale']) . "</td>";
        echo "<td>" . htmlspecialchars($r['Tipologia']) . "</td>";
        echo "<td>" . $r['Stato_Validazione'] . "</td>";
        echo "<td>" . htmlspecialchars($r['Email']) . "</td>";
        echo "<td>$sel</td>";
        echo "<td>$action</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "❌ Errore: " . $e->getMessage();
}

echo "<br><a href=\"attivita_elenco.php?lang=it\">Torna a elenco attività</a>";
?>