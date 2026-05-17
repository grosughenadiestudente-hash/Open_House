/**
 * Liste di Province e Regioni Italiane
 * Per l'uso nei dropdown di registrazione e profilo
 */

const REGIONI_PROVINCE = {
  'Abruzzo': ['L\'Aquila', 'Teramo', 'Pescara', 'Chieti'],
  'Basilicata': ['Potenza', 'Matera'],
  'Calabria': ['Cosenza', 'Catanzaro', 'Reggio Calabria', 'Crotone', 'Vibo Valentia'],
  'Campania': ['Napoli', 'Caserta', 'Benevento', 'Avellino', 'Salerno'],
  'Emilia-Romagna': ['Piacenza', 'Parma', 'Reggio Emilia', 'Modena', 'Bologna', 'Ferrara', 'Ravenna', 'Forlì-Cesena', 'Rimini'],
  'Friuli-Venezia Giulia': ['Udine', 'Gorizia', 'Trieste', 'Pordenone'],
  'Lazio': ['Roma', 'Frosinone', 'Latina', 'Rieti', 'Viterbo'],
  'Liguria': ['Imperia', 'Savona', 'Genova', 'La Spezia'],
  'Lombardia': ['Varese', 'Como', 'Lecco', 'Sondrio', 'Milano', 'Bergamo', 'Brescia', 'Pavia', 'Cremona', 'Mantova', 'Monza', 'Lodi'],
  'Marche': ['Pesaro', 'Ancona', 'Macerata', 'Ascoli Piceno', 'Fermo'],
  'Molise': ['Campobasso', 'Isernia'],
  'Piemonte': ['Vercelli', 'Novara', 'Cuneo', 'Asti', 'Alessandria', 'Torino', 'Biella', 'Verbano-Cusio-Ossola'],
  'Puglia': ['Foggia', 'Barletta-Andria-Trani', 'Bari', 'Taranto', 'Brindisi', 'Lecce'],
  'Sardegna': ['Sassari', 'Nuoro', 'Oristano', 'Cagliari', 'Medio Campidano', 'Olbia-Tempio', 'Carbonia-Iglesias'],
  'Sicilia': ['Palermo', 'Trapani', 'Agrigento', 'Caltanissetta', 'Enna', 'Catania', 'Ragusa', 'Siracusa', 'Messina'],
  'Toscana': ['Massa', 'Lucca', 'Pistoia', 'Firenze', 'Prato', 'Livorno', 'Pisa', 'Arezzo', 'Siena', 'Grosseto'],
  'Trentino-Alto Adige': ['Bolzano', 'Trento'],
  'Umbria': ['Perugia', 'Terni'],
  'Valle d\'Aosta': ['Aosta'],
  'Veneto': ['Belluno', 'Treviso', 'Venezia', 'Padova', 'Vicenza', 'Verona', 'Rovigo']
};

const REGIONI_ARRAY = Object.keys(REGIONI_PROVINCE).sort();

/**
 * Popola il dropdown delle regioni
 * @param {string} selectId - ID del select delle regioni
 */
function populateRegioni(selectId) {
  const select = document.getElementById(selectId);
  if (!select) return;
  
  select.innerHTML = '<option value="">-- Seleziona una Regione --</option>';
  
  REGIONI_ARRAY.forEach(regione => {
    const option = document.createElement('option');
    option.value = regione;
    option.textContent = regione;
    select.appendChild(option);
  });
}

/**
 * Popola il dropdown delle province in base alla regione selezionata
 * @param {string} regioneSelectId - ID del select delle regioni
 * @param {string} provinceSelectId - ID del select delle province
 */
function populateProvince(regioneSelectId, provinceSelectId) {
  const regioneSelect = document.getElementById(regioneSelectId);
  const provinceSelect = document.getElementById(provinceSelectId);
  
  if (!regioneSelect || !provinceSelect) return;
  
  const regione = regioneSelect.value;
  
  // Svuota il dropdown delle province
  provinceSelect.innerHTML = '<option value="">-- Seleziona una Provincia --</option>';
  
  if (regione && REGIONI_PROVINCE[regione]) {
    REGIONI_PROVINCE[regione].forEach(provincia => {
      const option = document.createElement('option');
      option.value = provincia;
      option.textContent = provincia;
      provinceSelect.appendChild(option);
    });
  }
}

/**
 * Inizializza i dropdown di regione e provincia con event listeners
 * @param {string} regioneSelectId - ID del select delle regioni
 * @param {string} provinceSelectId - ID del select delle province
 */
function initRegionProvinceSelects(regioneSelectId, provinceSelectId) {
  // Popola subito le regioni
  populateRegioni(regioneSelectId);
  
  // Aggiungi event listener al select delle regioni
  const regioneSelect = document.getElementById(regioneSelectId);
  if (regioneSelect) {
    regioneSelect.addEventListener('change', function() {
      populateProvince(regioneSelectId, provinceSelectId);
    });
  }
}

/**
 * Sincronizza il valore della regione da un campo provincia
 * (Se conosci la provincia, imposta automaticamente la regione)
 * @param {string} provinceSelectId - ID del select delle province
 * @param {string} regioneSelectId - ID del select delle regioni
 */
function syncRegionFromProvince(provinceSelectId, regioneSelectId) {
  const provinceSelect = document.getElementById(provinceSelectId);
  const regioneSelect = document.getElementById(regioneSelectId);
  
  if (!provinceSelect || !regioneSelect) return;
  
  const provincia = provinceSelect.value;
  
  if (provincia) {
    // Trova la regione corrispondente
    for (const [regione, province] of Object.entries(REGIONI_PROVINCE)) {
      if (province.includes(provincia)) {
        regioneSelect.value = regione;
        break;
      }
    }
  }
}

// Esporta per Node.js/moduli
if (typeof module !== 'undefined' && module.exports) {
  module.exports = {
    REGIONI_PROVINCE,
    REGIONI_ARRAY,
    populateRegioni,
    populateProvince,
    initRegionProvinceSelects,
    syncRegionFromProvince
  };
}
