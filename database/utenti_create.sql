-- Create utenti table and seed one user
CREATE TABLE IF NOT EXISTS `utenti` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `cognome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `tipo_utente` enum('studente','genitore','docente') NOT NULL,
  `data_nascita` date DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `utenti` (`id`, `nome`, `cognome`, `email`, `password`, `tipo_utente`, `data_nascita`, `telefono`, `created_at`, `updated_at`) VALUES
(1, 'Ghenadie', 'Grosu', 'g.grosu91@gmail.com', '$2y$10$yt/KDzIQqOjP1dGJ95pyYeroBObfsJ5sDgx87GI0J/rHJrLUuhd2K', 'studente', '1974-09-06', '3282966058', '2026-02-10 20:55:30', '2026-02-10 20:55:30');

-- Add primary key and indexes
ALTER TABLE `utenti` ADD PRIMARY KEY (`id`);
ALTER TABLE `utenti` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
