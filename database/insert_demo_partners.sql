-- Insert demo partners: 5 FSL partners and 2 VR partners
USE open_house;

INSERT INTO `istituti_e_partner` 
(`Cod_REA`, `Ragione_Sociale`, `Tipologia`, `Email`, `Indirizzo`, `Comune`, `Provincia`, `Regione`, `Stato_Validazione`, `created_at`)
VALUES
('REA0001', 'Partner1 FSL', 'AZIENDA_FSL', 'partner1.fsl@example.local', 'Via Demo 1', 'Città1', 'Provincia1', 'Regione1', 1, '2026-05-21 12:00:00'),
('REA0002', 'Partner2 FSL', 'AZIENDA_FSL', 'partner2.fsl@example.local', 'Via Demo 2', 'Città2', 'Provincia2', 'Regione2', 1, '2026-05-21 12:01:00'),
('REA0003', 'Partner3 FSL', 'AZIENDA_FSL', 'partner3.fsl@example.local', 'Via Demo 3', 'Città3', 'Provincia3', 'Regione3', 1, '2026-05-21 12:02:00'),
('REA0004', 'Partner4 FSL', 'AZIENDA_FSL', 'partner4.fsl@example.local', 'Via Demo 4', 'Città4', 'Provincia4', 'Regione4', 1, '2026-05-21 12:03:00'),
('REA0005', 'Partner5 FSL', 'AZIENDA_FSL', 'partner5.fsl@example.local', 'Via Demo 5', 'Città5', 'Provincia5', 'Regione5', 1, '2026-05-21 12:04:00');

-- Two VR partners
INSERT INTO `istituti_e_partner` 
(`Ragione_Sociale`, `Tipologia`, `Email`, `Indirizzo`, `Comune`, `Provincia`, `Regione`, `Stato_Validazione`, `created_at`)
VALUES
('Partner1 VR', 'PARTNER_VR', 'partner1.vr@example.local', 'Via VR 1', 'CityVR1', 'PV1', 'RegioneVR', 1, '2026-05-21 12:05:00'),
('Partner2 VR', 'ARENA_VR', 'partner2.vr@example.local', 'Via VR 2', 'CityVR2', 'PV2', 'RegioneVR', 1, '2026-05-21 12:06:00');

-- End of demo partners
