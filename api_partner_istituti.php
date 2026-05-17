<?php
/**
 * API per filtro istituti partner VR e FSL
 * Endpoint: api_partner_istituti.php
 * 
 * Restituisce lista di istituti filtrati per tipo (partner_vr, partner_fsl)
 */

require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $partner_type = $_GET['partner_type'] ?? $_POST['partner_type'] ?? '';
    $regione = $_GET['regione'] ?? '';
    $provincia = $_GET['provincia'] ?? '';
    $search = $_GET['search'] ?? '';

    $query = "SELECT i.ID_Ente, i.Ragione_Sociale, i.Tipologia, i.Email, i.Indirizzo, 
                     i.Comune, i.Provincia, i.Regione, i.CF_PIVA, i.Coordinate_GPS,
                     i.Stato_Validazione
              FROM istituti_e_partner i
              WHERE i.Stato_Validazione = 1";

    $params = [];

    // Filtra per tipo partner
    if ($partner_type === 'partner_vr') {
        // Partner VR: Aziende e strutture specializzate in realtà virtuale
        $query .= " AND i.Tipologia IN ('AZIENDA', 'ARENA_VR', 'ARENA_MOBILE', 'PARTNER_VR')";
    } elseif ($partner_type === 'partner_fsl') {
        // Partner FSL: Enti che erogano formazione con certificazione FSL
        $query .= " AND (i.Tipologia LIKE '%AZIENDA%' OR i.Tipologia LIKE '%PARTNER%')";
    } elseif ($partner_type === 'istituti') {
        // Istituti scolastici
        $query .= " AND i.Tipologia IN ('SCUOLA PRIMARIA', 'SCUOLA INFANZIA', 'SCUOLA PRIMO GRADO', 
                                         'ISTITUTO COMPRENSIVO', 'LICEO CLASSICO', 'LICEO SCIENTIFICO',
                                         'ISTITUTO TECNICO', 'ISTITUTO PROFESSIONALE', 'ISTITUTO MAGISTRALE')";
    }

    // Filtra per regione
    if (!empty($regione)) {
        $query .= " AND i.Regione = ?";
        $params[] = $regione;
    }

    // Filtra per provincia
    if (!empty($provincia)) {
        $query .= " AND i.Provincia = ?";
        $params[] = $provincia;
    }

    // Filtra per ricerca nel nome
    if (!empty($search)) {
        $query .= " AND i.Ragione_Sociale LIKE ?";
        $params[] = "%{$search}%";
    }

    $query .= " ORDER BY i.Ragione_Sociale ASC LIMIT 100";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $istituti = $stmt->fetchAll();

    // Restituisci risultati
    echo json_encode([
        'success' => true,
        'partner_type' => $partner_type,
        'count' => count($istituti),
        'data' => $istituti
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
