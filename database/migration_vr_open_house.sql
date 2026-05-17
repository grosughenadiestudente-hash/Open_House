-- Migrazione VR Open House (ruoli estesi + booking multimodale)

ALTER TABLE utenti
  MODIFY tipo_utente ENUM('studente','genitore','docente','partner_vr','mentor','admin') NOT NULL;

ALTER TABLE istituti_e_partner
  ADD COLUMN IF NOT EXISTS password VARCHAR(255) NULL AFTER Email,
  ADD COLUMN IF NOT EXISTS telefono VARCHAR(50) NULL AFTER Provincia,
  ADD COLUMN IF NOT EXISTS descrizione TEXT NULL AFTER telefono;

CREATE TABLE IF NOT EXISTS partner_profili (
  id INT(11) NOT NULL AUTO_INCREMENT,
  utente_id INT(11) NOT NULL,
  ragione_sociale VARCHAR(255) NOT NULL,
  tipo_partner ENUM('arena_vr','arena_mobile','azienda_fsl','ente_pubblico') NOT NULL DEFAULT 'arena_vr',
  descrizione TEXT NULL,
  indirizzo VARCHAR(255) NULL,
  citta VARCHAR(120) NULL,
  provincia VARCHAR(10) NULL,
  regione VARCHAR(100) NULL,
  latitudine DECIMAL(10,7) NULL,
  longitudine DECIMAL(10,7) NULL,
  stato_validazione ENUM('in_attesa','approvato','bloccato') NOT NULL DEFAULT 'in_attesa',
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_partner_utente (utente_id),
  KEY idx_partner_tipo (tipo_partner),
  KEY idx_partner_stato (stato_validazione),
  CONSTRAINT fk_partner_utente FOREIGN KEY (utente_id) REFERENCES utenti(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE prenotazioni
  MODIFY utente_id INT(11) NULL,
  ADD COLUMN IF NOT EXISTS modalita_fruizione ENUM('casa','arena_fisica','arena_mobile') NOT NULL DEFAULT 'casa' AFTER attivita_id,
  ADD COLUMN IF NOT EXISTS partner_vr_id INT(11) NULL AFTER modalita_fruizione,
  ADD COLUMN IF NOT EXISTS istituto_prenotante_id INT(11) NULL AFTER partner_vr_id,
  ADD COLUMN IF NOT EXISTS numero_partecipanti INT(11) NOT NULL DEFAULT 1 AFTER istituto_prenotante_id,
  ADD COLUMN IF NOT EXISTS qr_code VARCHAR(120) NULL AFTER numero_partecipanti,
  ADD COLUMN IF NOT EXISTS fsl_ore DECIMAL(4,2) NULL AFTER qr_code;

-- Nota: istituti e una tabella MyISAM nel dump legacy, quindi qui evitiamo FK dirette.
-- Il vincolo applicativo viene gestito lato PHP.
