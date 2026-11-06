SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `prenotazione` DEFAULT CHARACTER SET utf8mb4;
USE `prenotazione`;

CREATE TABLE `appointment` (
  `id` int NOT NULL,
  `patient_id` int NOT NULL,
  `doctor_id` int NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('PENDING','CONFIRMED','REFUSED','DONE') NOT NULL DEFAULT 'PENDING',
  `cancelled_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `user` (
  `id` int NOT NULL,
  `username` varchar(40) NOT NULL,
  `password` varchar(72) NOT NULL,
  `role` enum('PAZIENTE','MEDICO') NOT NULL DEFAULT 'PAZIENTE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- demo accounts (Medico = doctor, Paziente = patient)
INSERT INTO `user` (`id`, `username`, `password`, `role`) VALUES
(1, 'Paziente', '$2y$12$R.x4LrEMD7F5KLT2vCsdr.X8earSq0C5nwx9Tg6qggj5pfojKIkIm', 'PAZIENTE'),
(2, 'Medico', '$2y$12$jQIGXydEaQWb5N2g1uJR9epk27ajLqKNofLOe8xzL/6IbFF/iycjK', 'MEDICO');

ALTER TABLE `appointment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `slot` (`doctor_id`,`appointment_date`,`appointment_time`),
  ADD KEY `fk_patient` (`patient_id`),
  ADD KEY `fk_doctor` (`doctor_id`);

ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_username` (`username`);

ALTER TABLE `appointment`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;
