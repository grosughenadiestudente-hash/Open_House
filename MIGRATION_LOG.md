# Migrazione Database: istituti -> istituti_e_partner

## Riepilogo delle modifiche

### 1. Rinominazione tabella e campi

| Vecchio Nome | Nuovo Nome |
|---|---|
| Tabella: `istituti` | `istituti_e_partner` |
| Colonna: `id` | `ID_Ente` (PRIMARY KEY, AUTO_INCREMENT) |
| Colonna: `codice_istituto` | `Cod_Mecc` |
| Colonna: `nome` | `Ragione_Sociale` |
| Colonna: `tipo_scuola` | `Tipologia` |
| Colonna: `email` | `Email` |
| Colonna: `indirizzo` | `Indirizzo` |
| Colonna: `comune` | `Comune` |
| Colonna: `provincia` | `Provincia` |
| Colonna: `regione` | `Regione` |

### 2. Nuove colonne aggiunte

| Colonna | Tipo | Note |
|---|---|---|
| `CF_PIVA` | varchar(20) | Codice Fiscale/P. IVA |
| `Cod_REA` | varchar(20) | Codice REA/SDI (opzionale per aziende) |
| `Coordinate_GPS` | varchar(100) | Coordinate geografiche |
| `Stato_Validazione` | tinyint(1) | 0=In attesa, 1=Approvato, 2=Bloccato |

### 3. Miglioramenti strutturali

- Cambio da MyISAM a InnoDB per supportare transazioni
- Charset aggiornato a utf8mb4 per supporto completo Unicode
- Aggiunto UNIQUE KEY su `Cod_Mecc`
- Aggiunti indici su `Ragione_Sociale`, `Provincia`, `Comune`, `Tipologia`, `Stato_Validazione`

### 4. File di migrazione

- `database/migration_istituti_to_istituti_e_partner.sql` - Script di migrazione completo
- `database/migration_vr_open_house.sql` - Aggiornato per usare il nuovo nome della tabella

### 5. File PHP aggiornati ✅

- ✅ index.php - Già usa `istituti_e_partner`
- ✅ api_istituti.php - Già usa i nuovi nomi colonna con alias
- ✅ attivita_*.php - Già usa i nuovi nomi colonna
- ✅ dashboard_*.php - Già usa i nuovi nomi colonna
- ✅ istituti_elenco.php - Già usa i nuovi nomi colonna
- ✅ istituto_dettaglio.php - Già usa i nuovi nomi colonna
- ✅ login.php - Già usa i nuovi nomi colonna
- ✅ profilo_istituto.php - Già usa i nuovi nomi colonna
- ✅ chat_messaggi.php - Già usa i nuovi nomi colonna

### 6. Passi per applicare la migrazione

1. Backup del database attuale
2. Eseguire `migration_istituti_to_istituti_e_partner.sql` (createra `istituti_backup_old` come backup)
3. Verificare l'integrità dei dati
4. Eliminare la tabella di backup se confermato tutto funziona

## Compatibilità

Tutti i file PHP nel progetto stanno già usando i nuovi nomi di colonna attraverso alias SQL:
```php
SELECT i.ID_Ente as id, i.Ragione_Sociale as nome, i.Tipologia as tipo_scuola, i.Cod_Mecc as codice_istituto
```

Questo significa che il codice PHP rimarrà compatibile anche con questa migrazione.
