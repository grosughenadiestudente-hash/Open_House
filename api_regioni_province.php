<?php
/**
 * API per Province e Regioni
 * Restituisce le liste di regioni e province per i dropdown
 * 
 * Endpoint:
 * - /api_regioni_province.php?action=regioni - restituisce lista regioni
 * - /api_regioni_province.php?action=province&regione=Lombardia - restituisce province per regione
 * - /api_regioni_province.php - restituisce tutte le regioni e province
 */

header('Content-Type: application/json; charset=utf-8');

$regioni_province = [
    'Abruzzo' => ['L\'Aquila', 'Teramo', 'Pescara', 'Chieti'],
    'Basilicata' => ['Potenza', 'Matera'],
    'Calabria' => ['Cosenza', 'Catanzaro', 'Reggio Calabria', 'Crotone', 'Vibo Valentia'],
    'Campania' => ['Napoli', 'Caserta', 'Benevento', 'Avellino', 'Salerno'],
    'Emilia-Romagna' => ['Piacenza', 'Parma', 'Reggio Emilia', 'Modena', 'Bologna', 'Ferrara', 'Ravenna', 'Forlì-Cesena', 'Rimini'],
    'Friuli-Venezia Giulia' => ['Udine', 'Gorizia', 'Trieste', 'Pordenone'],
    'Lazio' => ['Roma', 'Frosinone', 'Latina', 'Rieti', 'Viterbo'],
    'Liguria' => ['Imperia', 'Savona', 'Genova', 'La Spezia'],
    'Lombardia' => ['Varese', 'Como', 'Lecco', 'Sondrio', 'Milano', 'Bergamo', 'Brescia', 'Pavia', 'Cremona', 'Mantova', 'Monza', 'Lodi'],
    'Marche' => ['Pesaro', 'Ancona', 'Macerata', 'Ascoli Piceno', 'Fermo'],
    'Molise' => ['Campobasso', 'Isernia'],
    'Piemonte' => ['Vercelli', 'Novara', 'Cuneo', 'Asti', 'Alessandria', 'Torino', 'Biella', 'Verbano-Cusio-Ossola'],
    'Puglia' => ['Foggia', 'Barletta-Andria-Trani', 'Bari', 'Taranto', 'Brindisi', 'Lecce'],
    'Sardegna' => ['Sassari', 'Nuoro', 'Oristano', 'Cagliari', 'Medio Campidano', 'Olbia-Tempio', 'Carbonia-Iglesias'],
    'Sicilia' => ['Palermo', 'Trapani', 'Agrigento', 'Caltanissetta', 'Enna', 'Catania', 'Ragusa', 'Siracusa', 'Messina'],
    'Toscana' => ['Massa', 'Lucca', 'Pistoia', 'Firenze', 'Prato', 'Livorno', 'Pisa', 'Arezzo', 'Siena', 'Grosseto'],
    'Trentino-Alto Adige' => ['Bolzano', 'Trento'],
    'Umbria' => ['Perugia', 'Terni'],
    'Valle d\'Aosta' => ['Aosta'],
    'Veneto' => ['Belluno', 'Treviso', 'Venezia', 'Padova', 'Vicenza', 'Verona', 'Rovigo']
];

try {
    $action = $_GET['action'] ?? '';
    $regione = $_GET['regione'] ?? '';
    
    if ($action === 'regioni') {
        // Restituisci solo le regioni
        echo json_encode([
            'success' => true,
            'regioni' => array_keys($regioni_province)
        ]);
    } elseif ($action === 'province' && !empty($regione)) {
        // Restituisci le province per una regione specifica
        if (isset($regioni_province[$regione])) {
            echo json_encode([
                'success' => true,
                'regione' => $regione,
                'province' => $regioni_province[$regione]
            ]);
        } else {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Regione non trovata'
            ]);
        }
    } else {
        // Restituisci tutto
        echo json_encode([
            'success' => true,
            'regioni_province' => $regioni_province,
            'regioni' => array_keys($regioni_province),
            'count_regioni' => count($regioni_province),
            'count_province_total' => array_sum(array_map('count', $regioni_province))
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
