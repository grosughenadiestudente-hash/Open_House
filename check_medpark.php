<?php
require 'config.php';

// Cerca MedPark nel database
$stmt = $pdo->prepare("SELECT ID_Ente, Ragione_Sociale, Tipologia, Email, Cod_Mecc, Cod_REA FROM istituti_e_partner WHERE Ragione_Sociale LIKE ? OR Ragione_Sociale LIKE ? OR Ragione_Sociale LIKE ?");
$stmt->execute(['%MedPark%', '%MEDPARK%', '%Med Park%']);
$result = $stmt->fetchAll();

echo "=== RICERCA MEDPARK ===\n";
if (empty($result)) {
    echo "❌ MedPark NON trovato nel database\n";
    echo "\nTutti gli enti nel database:\n";
    $all = $pdo->query("SELECT ID_Ente, Ragione_Sociale, Tipologia, Email FROM istituti_e_partner ORDER BY Ragione_Sociale LIMIT 30");
    foreach ($all as $row) {
        echo "  ID: " . $row['ID_Ente'] . " | " . $row['Ragione_Sociale'] . " | " . $row['Tipologia'] . "\n";
    }
} else {
    echo "✅ MedPark TROVATO:\n";
    foreach ($result as $row) {
        echo "  ID_Ente: " . $row['ID_Ente'] . "\n";
        echo "  Ragione_Sociale: " . $row['Ragione_Sociale'] . "\n";
        echo "  Tipologia: " . $row['Tipologia'] . "\n";
        echo "  Email: " . $row['Email'] . "\n";
        echo "  Cod_Mecc: " . $row['Cod_Mecc'] . "\n";
        echo "  Cod_REA: " . $row['Cod_REA'] . "\n\n";
    }
}
?>
