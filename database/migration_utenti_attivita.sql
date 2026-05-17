-- Migrazione: Rinominazione e aggiornamento tabelle
-- utenti → utenti_finali
-- attivita → attivita_eventi (con nuova struttura)
-- Data: 2026-05-03

-- ============================================================================
-- STEP 1: Crea backup della tabella attivita
-- ============================================================================
CREATE TABLE IF NOT EXISTS `attivita_backup` AS SELECT * FROM `attivita`;

-- ============================================================================
-- STEP 2: Crea backup della tabella utenti
-- ============================================================================
CREATE TABLE IF NOT EXISTS `utenti_backup` AS SELECT * FROM `utenti`;

-- ============================================================================
-- STEP 3: Rinomina tabella utenti → utenti_finali
-- ============================================================================
RENAME TABLE `utenti` TO `utenti_finali`;

-- ============================================================================
-- STEP 4: Crea la nuova tabella attivita_eventi con la nuova struttura
-- ============================================================================
CREATE TABLE IF NOT EXISTS `attivita_eventi` (
  `ID_Attivita` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificativo univoco attività',
  `FK_Ente_Organizzatore` int(11) NOT NULL COMMENT 'FK a istituti_e_partner.ID_Ente',
  `Titolo` varchar(255) NOT NULL COMMENT 'Denominazione evento',
  `Descrizione` text COMMENT 'Testo esplicativo contenuti e obiettivi',
  `Link_WebXR` varchar(500) DEFAULT NULL COMMENT 'URL accesso Hub Immersivo (A-Frame/WebXR)',
  `Data_Ora` datetime NOT NULL COMMENT 'Cronoprogramma evento',
  `Max_Posti` int(11) DEFAULT 50 COMMENT 'Limite massimo partecipanti',
  `Flag_FSL` tinyint(1) DEFAULT 0 COMMENT 'Valida per certificazione ore',
  `Tipo_Attivita` enum('presentazione','laboratorio','tour_virtuale','workshop','altro') DEFAULT 'presentazione' COMMENT 'Tipo attività',
  `Durata_Minuti` int(11) DEFAULT 60,
  `Supporta_VR` tinyint(1) DEFAULT 1,
  `Materiali_URL` text,
  `Stato` enum('bozza','pubblicata','in_corso','completata','cancellata') DEFAULT 'bozza',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`ID_Attivita`),
  KEY `idx_ente_organizzatore` (`FK_Ente_Organizzatore`),
  KEY `idx_data_ora` (`Data_Ora`),
  KEY `idx_stato` (`Stato`),
  KEY `idx_fsl` (`Flag_FSL`),
  CONSTRAINT `fk_attivita_eventi_ente` FOREIGN KEY (`FK_Ente_Organizzatore`) REFERENCES `istituti_e_partner` (`ID_Ente`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- STEP 5: Migra i dati da attivita a attivita_eventi
-- ============================================================================
INSERT INTO `attivita_eventi` 
  (`ID_Attivita`, `FK_Ente_Organizzatore`, `Titolo`, `Descrizione`, `Link_WebXR`, `Data_Ora`, 
   `Max_Posti`, `Tipo_Attivita`, `Durata_Minuti`, `Supporta_VR`, `Materiali_URL`, `Stato`, `created_at`, `updated_at`)
SELECT 
  `id`, 
  `istituto_id`, 
  `titolo`, 
  `descrizione`, 
  `url_vr`,
  `data_ora`, 
  `max_partecipanti`, 
  `tipo_attivita`,
  `durata_minuti`,
  `supporta_vr`,
  `materiali_url`,
  `stato`,
  `created_at`,
  `updated_at`
FROM `attivita`;

-- ============================================================================
-- STEP 6: Aggiorna la FK nella tabella materiali
-- ============================================================================
ALTER TABLE `materiali` DROP FOREIGN KEY `materiali_ibfk_1`;
ALTER TABLE `materiali` 
  ADD CONSTRAINT `fk_materiali_attivita_eventi` 
  FOREIGN KEY (`attivita_id`) REFERENCES `attivita_eventi` (`ID_Attivita`) ON DELETE CASCADE;

-- ============================================================================
-- STEP 7: Rinomina la vecchia tabella attivita come backup
-- ============================================================================
RENAME TABLE `attivita` TO `attivita_backup_old`;

-- ============================================================================
-- STEP 8: Demo tecnica WebXR per ITIS "PIETRO PALEOCAPA" Bergamo
-- ============================================================================
INSERT INTO `attivita_eventi`
  (`FK_Ente_Organizzatore`, `Titolo`, `Descrizione`, `Link_WebXR`, `Data_Ora`,
   `Max_Posti`, `Flag_FSL`, `Tipo_Attivita`, `Durata_Minuti`, `Supporta_VR`, `Materiali_URL`, `Stato`)
SELECT
  i.`ID_Ente`,
  'Simulazione WebXR - Braccio robotico 3D',
  'Laboratorio di robototecnica con simulazione tecnica WebXR del braccio robotico in 3D, pensato per utenti registrati del portale e per l\'orientamento tecnologico.',
  'https://Novia-RDI-XR-Robotics.github.io/a-frame-xr-tutorial/',
  '2026-05-15 10:00:00',
  30,
  1,
  'laboratorio',
  45,
  1,
  NULL,
  'pubblicata'
FROM `istituti_e_partner` i
WHERE i.`Cod_Mecc` = 'BGTF010003'
  AND NOT EXISTS (
    SELECT 1
    FROM `attivita_eventi` a
    WHERE a.`FK_Ente_Organizzatore` = i.`ID_Ente`
      AND a.`Titolo` = 'Simulazione WebXR - Braccio robotico 3D'
  );
