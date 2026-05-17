-- Migrazione: Rinominazione tabella istituti -> istituti_e_partner
-- Data: 2026-05-03
-- Descrizione: Aggiorna la tabella istituti con i nuovi nomi campi e aggiunge campi per partner

-- 1. Crea la nuova tabella con la nuova struttura
CREATE TABLE IF NOT EXISTS `istituti_e_partner` (
  `ID_Ente` int(11) NOT NULL AUTO_INCREMENT,
  `Ragione_Sociale` varchar(255) DEFAULT NULL,
  `Tipologia` varchar(150) DEFAULT NULL,
  `CF_PIVA` varchar(20) DEFAULT NULL,
  `Cod_Mecc` varchar(20) DEFAULT NULL,
  `Cod_REA` varchar(20) DEFAULT NULL,
  `Indirizzo` varchar(255) DEFAULT NULL,
  `Comune` varchar(150) DEFAULT NULL,
  `Provincia` varchar(10) DEFAULT NULL,
  `Regione` varchar(100) DEFAULT NULL,
  `Coordinate_GPS` varchar(100) DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `Stato_Validazione` tinyint(1) DEFAULT 0 COMMENT '0=In attesa, 1=Approvato, 2=Bloccato',
  PRIMARY KEY (`ID_Ente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Se la tabella istituti esiste, copia i dati nella nuova struttura
INSERT INTO `istituti_e_partner` 
  (`ID_Ente`, `Cod_Mecc`, `Ragione_Sociale`, `Email`, `Tipologia`, `Indirizzo`, `Comune`, `Provincia`, `created_at`, `Regione`, `Stato_Validazione`)
SELECT 
  `id`, 
  `codice_istituto`, 
  `nome`, 
  `email`, 
  `tipo_scuola`, 
  `indirizzo`, 
  `comune`, 
  `provincia`, 
  `created_at`, 
  `regione`,
  1
FROM `istituti`
ON DUPLICATE KEY UPDATE 
  `Ragione_Sociale` = VALUES(`Ragione_Sociale`),
  `Tipologia` = VALUES(`Tipologia`),
  `Email` = VALUES(`Email`),
  `Indirizzo` = VALUES(`Indirizzo`),
  `Comune` = VALUES(`Comune`),
  `Provincia` = VALUES(`Provincia`),
  `Regione` = VALUES(`Regione`);

-- 3. Rinomina la tabella vecchia come backup (opzionale, può essere rimossa successivamente)
RENAME TABLE `istituti` TO `istituti_backup_old`;

-- 4. Aggiorna eventuali foreign keys nelle altre tabelle
-- Le colonne di riferimento rimangono ID_Ente, quindi non è necessario fare alterazioni alle altre tabelle
-- se già usano il giusto nome di colonna
