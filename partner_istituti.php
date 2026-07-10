<?php
/**
 * Pagina di visualizzazione degli istituti partner
 * Mostra liste filtrabili di partner VR e partner FSL
 */

require_once 'config.php';

$page_title = "Partner VR e FSL";
$lang = $_GET['lang'] ?? 'it';

// Determina il tipo di visualizzazione
$view_type = trim($_GET['view'] ?? 'tutti');  // partner_vr, partner_fsl, istituti, tutti
$search = trim($_GET['search'] ?? '');
$regione = trim($_GET['regione'] ?? '');

$allowed_views = ['partner_vr', 'partner_fsl', 'istituti', 'tutti'];
if (!in_array($view_type, $allowed_views, true)) {
    $view_type = '';
}

// Debug: Log dei parametri ricevuti
// error_log("view_type: $view_type, search: $search, regione: $regione");

// Funzione per ottenere gli istituti
$istituti = [];
if ($view_type === '') {
    $view_type = 'tutti';
}

// default: show all (including non-validati) but indicate validation status
// if user selected a specific view, filter accordingly

if ($view_type === '') {
    $info = 'Seleziona una categoria per vedere i risultati.';
}

try {
    $query = "SELECT i.ID_Ente, i.Ragione_Sociale, i.Tipologia, i.Email, i.Telefono, 
                     i.Indirizzo, i.Comune, i.Provincia, i.Regione, i.CF_PIVA,
                     i.Coordinate_GPS, i.Stato_Validazione
              FROM istituti_e_partner i
              WHERE 1=1";

    $params = [];

    // Filtra per tipo visualizzazione
    if ($view_type === 'partner_vr') {
        $query .= " AND i.Tipologia IN ('ARENA_VR', 'ARENA_MOBILE', 'PARTNER_VR')";
    } elseif ($view_type === 'partner_fsl') {
        $query .= " AND i.Cod_REA IS NOT NULL AND i.Cod_REA != ''";
    } elseif ($view_type === 'istituti') {
        $query .= " AND i.Tipologia IN ('SCUOLA PRIMARIA', 'SCUOLA INFANZIA', 'SCUOLA PRIMO GRADO', 
                                         'ISTITUTO COMPRENSIVO', 'LICEO CLASSICO', 'LICEO SCIENTIFICO',
                                         'ISTITUTO TECNICO', 'ISTITUTO PROFESSIONALE', 'ISTITUTO MAGISTRALE')";
    } else {
        // 'tutti' -> no extra filter
    }

    // Filtra per regione
    if (!empty($regione)) {
        $query .= " AND i.Regione = ?";
        $params[] = $regione;
    }

    // Filtra per ricerca
    if (!empty($search)) {
        $query .= " AND (i.Ragione_Sociale LIKE ? OR i.Comune LIKE ? OR i.Provincia LIKE ? OR i.Regione LIKE ?)";
        $searchParam = "%{$search}%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }

    $query .= " ORDER BY i.Regione, i.Provincia, i.Ragione_Sociale";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $istituti = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error = "Errore nel caricamento: " . $e->getMessage();
}

// Ottieni lista regioni
$regioni = [];
try {
    $stmt = $pdo->query("SELECT DISTINCT Regione FROM istituti_e_partner WHERE Regione IS NOT NULL ORDER BY Regione");
    $regioni = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    // Log error silently
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="partner-page">
    <?php $active_page = 'partner'; include 'header.php'; ?>
    
    <main class="container">
        <div class="header-section">
            <img src="image/Logo.png" alt="Logo VR Open House">
            <h1><?php echo htmlspecialchars($page_title); ?></h1>
        </div>
        
        <!-- Filtri -->
        <form method="GET" class="filter-section">
            <input type="hidden" name="lang" value="<?php echo htmlspecialchars($lang); ?>">
            <!-- Selezione tipo visualizzazione -->
            <div class="view-selector">
                <label for="viewType">Seleziona cosa visualizzare:</label>
                <select id="viewType" name="view" onchange="this.form.submit()">
                    <option value="">Seleziona</option>
                        <option value="partner_vr" <?php echo ($view_type === 'partner_vr') ? 'selected' : ''; ?>>🥽 Partner VR</option>
                        <option value="partner_fsl" <?php echo ($view_type === 'partner_fsl') ? 'selected' : ''; ?>>📚 Partner FSL</option>
                        <option value="istituti" <?php echo ($view_type === 'istituti') ? 'selected' : ''; ?>>🏫 Istituti</option>
                </select>
            </div>
            
            <div class="filter-row">
                <div class="filter-group">
                    <label for="search">Ricerca Nome</label>
                    <input type="text" id="search" name="search" placeholder="Cerca per nome..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="filter-group">
                    <label for="regione">Regione</label>
                    <select id="regione" name="regione">
                        <option value="">Tutte le regioni</option>
                        <?php foreach ($regioni as $reg): ?>
                            <option value="<?php echo htmlspecialchars($reg); ?>" 
                                    <?php echo ($regione === $reg) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($reg); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <button type="submit" class="btn-filter">🔍 Filtra</button>
                </div>
                
                <div class="filter-group">
                    <a href="?view=<?php echo htmlspecialchars($view_type); ?>&lang=<?php echo htmlspecialchars($lang); ?>" class="btn-reset">Azzera Filtri</a>
                </div>
            </div>
        </form>

        <!-- Risultati -->
        <?php if (isset($info)): ?>
            <div class="partner-alert-info">
                <?php echo htmlspecialchars($info); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="partner-alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($view_type !== ''): ?>
            <div class="results-count">
                ✓ <?php echo count($istituti); ?> risultati trovati
            </div>

            <?php if (empty($istituti)): ?>
                <div class="no-results">
                    <h3>Nessun risultato trovato</h3>
                    <p>Prova a modificare i filtri di ricerca</p>
                </div>
            <?php else: ?>
                <div class="istituti-grid">
                    <?php foreach ($istituti as $istituto): ?>
                        <div class="istituto-card">
                            <div class="istituto-name"><?php echo htmlspecialchars($istituto['Ragione_Sociale']); ?></div>
                        
                        <div class="istituto-type">
                            <?php 
                            $tipologie_map = [
                                'AZIENDA' => '🏢',
                                'ARENA_VR' => '🥽',
                                'PARTNER_VR' => '🥽',
                                'SCUOLA PRIMARIA' => '🏫',
                                'SCUOLA INFANZIA' => '👶',
                                'ISTITUTO TECNICO' => '⚙️',
                                'LICEO' => '📚',
                            ];
                            $icon = '📍';
                            foreach ($tipologie_map as $key => $value) {
                                if (strpos($istituto['Tipologia'], $key) !== false) {
                                    $icon = $value;
                                    break;
                                }
                            }
                            echo $icon . ' ' . htmlspecialchars($istituto['Tipologia']);
                            ?>
                        </div>
                        
                        <div class="istituto-info">
                            <?php if (!empty($istituto['Provincia'])): ?>
                                <p><span class="info-label">Provincia:</span> <?php echo htmlspecialchars($istituto['Provincia']); ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($istituto['Regione'])): ?>
                                <p><span class="info-label">Regione:</span> <?php echo htmlspecialchars($istituto['Regione']); ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($istituto['Email'])): ?>
                                <p><span class="info-label">Email:</span> <a href="mailto:<?php echo htmlspecialchars($istituto['Email']); ?>">
                                    <?php echo htmlspecialchars($istituto['Email']); ?></a></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($istituto['Telefono'])): ?>
                                <p><span class="info-label">Tel:</span> <?php echo htmlspecialchars($istituto['Telefono']); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <a href="istituto_dettaglio.php?id=<?php echo $istituto['ID_Ente']; ?>&lang=<?php echo htmlspecialchars($lang); ?>" class="btn-detail">
                            Visualizza Dettagli →
                        </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Inizializza il dropdown del view_type con il valore corretto
        document.addEventListener('DOMContentLoaded', function() {
            const viewSelect = document.getElementById('viewType');
            const currentView = '<?php echo $view_type; ?>';
            if (viewSelect && currentView) {
                viewSelect.value = currentView;
            }
        });
    </script>
</body>
</html>
