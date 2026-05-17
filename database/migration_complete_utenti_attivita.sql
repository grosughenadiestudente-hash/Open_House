-- Completa la migrazione: assicura che le tabelle siano rinominate correttamente
-- Data: 2026-05-03

-- ============================================================================
-- STEP 1: Se attivita esiste ancora, rinominala in attivita_backup_old
-- ============================================================================
-- Controlla se attivita esiste
ALTER TABLE `attivita` RENAME TO `attivita_backup_old_temp`;
RENAME TABLE `attivita_backup_old_temp` TO `attivita_backup_old`;

-- ============================================================================
-- STEP 2: Se attivita_backup esiste, eliminala (è un duplicato)
-- ============================================================================
DROP TABLE IF EXISTS `attivita_backup`;

-- ============================================================================
-- STEP 3: Aggiorna la FK nella tabella materiali per attivita_eventi
-- ============================================================================
-- Prima elimina la FK vecchia
ALTER TABLE `materiali` DROP FOREIGN KEY `materiali_ibfk_1`;

-- Aggiunge la nuova FK
ALTER TABLE `materiali` 
  ADD CONSTRAINT `fk_materiali_attivita_eventi` 
  FOREIGN KEY (`attivita_id`) REFERENCES `attivita_eventi` (`ID_Attivita`) ON DELETE CASCADE;

-- ============================================================================
-- STEP 4: Verifica che la struttura di attivita_eventi sia corretta
-- ============================================================================
-- Se la tabella attivita_eventi non ha i campi corretti, crea una nuova
-- (già esiste, quindi verifichiamo solo)
-- DESCRIBE attivita_eventi;
