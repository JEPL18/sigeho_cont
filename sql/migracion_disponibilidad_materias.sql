CREATE TABLE `profesor_disponibilidad` (
  `profesor_id` int(11) NOT NULL,
  `dia_semana` varchar(20) NOT NULL,
  `turno` varchar(20) NOT NULL,
  PRIMARY KEY (`profesor_id`, `dia_semana`, `turno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `profesor_materia` (
  `profesor_id` int(11) NOT NULL,
  `materia_id` int(11) NOT NULL,
  PRIMARY KEY (`profesor_id`, `materia_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
