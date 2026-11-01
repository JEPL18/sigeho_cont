CREATE TABLE `materia_prelacion` (
  `materia_id` int(11) NOT NULL,
  `prelacion_id` int(11) NOT NULL,
  PRIMARY KEY (`materia_id`, `prelacion_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
