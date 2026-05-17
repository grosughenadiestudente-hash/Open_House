-- Inserisci Partner FSL (con Cod_REA)
INSERT INTO `istituti_e_partner` (`Ragione_Sociale`, `Tipologia`, `Email`, `Indirizzo`, `Comune`, `Provincia`, `Regione`, `Cod_REA`, `Stato_Validazione`) VALUES
('Prova Robototecnica', 'AZIENDA', 'info@prova-robotica.it', 'Via Roma 10', 'Palermo', 'PA', 'SICILIA', 'PA123456', 1),
('Prova Medicina', 'AZIENDA', 'info@prova-medicina.it', 'Via Garibaldi 25', 'Catania', 'CT', 'SICILIA', 'CT654321', 1),
('Prova Ingegneria Meccanica', 'AZIENDA', 'info@prova-ing.it', 'Via Mazzini 15', 'Messina', 'ME', 'SICILIA', 'ME789456', 1),
('Prova Scienze Biologiche', 'AZIENDA', 'info@prova-bio.it', 'Corso Vittorio 30', 'Agrigento', 'AG', 'SICILIA', 'AG456789', 1),
('Prova Architettura Sostenibile', 'AZIENDA', 'info@prova-arch.it', 'Viale Autonomia 12', 'Trapani', 'TP', 'SICILIA', 'TP321654', 1);

-- Inserisci Partner VR (con Tipologia ARENA_VR o PARTNER_VR)
INSERT INTO `istituti_e_partner` (`Ragione_Sociale`, `Tipologia`, `Email`, `Indirizzo`, `Comune`, `Provincia`, `Regione`, `Stato_Validazione`) VALUES
('TechVision VR', 'ARENA_VR', 'info@techvision-vr.it', 'Via Innovazione 5', 'Palermo', 'PA', 'SICILIA', 1),
('ImmersiveSpace VR', 'PARTNER_VR', 'contact@immersivespace.it', 'Via Tecnologia 20', 'Catania', 'CT', 'SICILIA', 1),
('VirtualWorld Arena', 'ARENA_VR', 'hello@virtualworld.it', 'Via Realtà Virtuale 8', 'Messina', 'ME', 'SICILIA', 1),
('XR Solutions VR', 'PARTNER_VR', 'info@xr-solutions.it', 'Corso Digitale 42', 'Agrigento', 'AG', 'SICILIA', 1);
