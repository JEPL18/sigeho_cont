-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-09-2026 a las 01:12:21
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sigeho_cont_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aulas`
--

CREATE TABLE `aulas` (
  `id` int(11) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `capacidad` int(11) NOT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'OPERATIVA'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `aulas`
--

INSERT INTO `aulas` (`id`, `codigo`, `capacidad`, `estado`) VALUES
(7, 'AULA12', 31, 'OPERATIVA'),
(8, 'M-114', 29, 'OPERATIVA'),
(9, 'AULA29', 40, 'OPERATIVA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacora`
--

CREATE TABLE `bitacora` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `accion` varchar(255) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `bitacora`
--

INSERT INTO `bitacora` (`id`, `usuario_id`, `accion`, `fecha`) VALUES
(16, 8, 'Inicio de sesión exitoso', '2026-08-22 02:41:34'),
(17, 8, 'Cierre de sesión manual', '2026-08-22 02:56:41'),
(18, 8, 'Inicio de sesión exitoso', '2026-08-22 03:03:18'),
(19, 8, 'Inicio de sesión exitoso', '2026-09-15 20:37:46'),
(20, 8, 'Cierre de sesión manual', '2026-09-15 20:38:09'),
(21, 8, 'Inicio de sesión exitoso', '2026-09-15 22:41:35'),
(22, 8, 'Cierre de sesión manual', '2026-09-15 23:08:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` int(11) NOT NULL,
  `parametro` varchar(100) NOT NULL,
  `valor` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `parametro`, `valor`) VALUES
(1, 'fecha_inicio_lapso', '2026-08-21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios`
--

CREATE TABLE `horarios` (
  `id` int(11) NOT NULL,
  `seccion_id` int(11) NOT NULL,
  `materia_id` int(11) NOT NULL,
  `profesor_id` int(11) NOT NULL,
  `aula_id` int(11) NOT NULL,
  `dia_semana` enum('Lunes','Martes','Miercoles','Jueves','Viernes','Sabado') NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `horarios`
--

INSERT INTO `horarios` (`id`, `seccion_id`, `materia_id`, `profesor_id`, `aula_id`, `dia_semana`, `hora_inicio`, `hora_fin`) VALUES
(18, 6, 7, 10, 7, 'Martes', '07:00:00', '08:35:00'),
(19, 6, 8, 12, 8, 'Lunes', '08:35:00', '10:30:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `log_choques`
--

CREATE TABLE `log_choques` (
  `id` int(11) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `usuario_id` int(11) DEFAULT NULL,
  `intento_asignacion` text DEFAULT NULL,
  `detalle_conflicto` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materias`
--

CREATE TABLE `materias` (
  `id` int(11) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `trayecto` int(11) NOT NULL,
  `horas_semanales` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `materias`
--

INSERT INTO `materias` (`id`, `codigo`, `nombre`, `trayecto`, `horas_semanales`) VALUES
(6, 'CON-122', 'Matematica Inicial', 0, 3),
(7, 'CON-200', 'Proyecto Socio Tecnologico Para La Contaduria', 1, 4),
(8, 'CON-230', 'Matematica Aplicada', 3, 5),
(9, 'CON-300', 'Matematica Ii', 3, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profesores`
--

CREATE TABLE `profesores` (
  `id` int(11) NOT NULL,
  `cedula` varchar(15) NOT NULL,
  `titulo` varchar(20) DEFAULT 'Prof.',
  `nombre` varchar(100) NOT NULL,
  `max_horas` int(11) DEFAULT 5,
  `estado` varchar(20) NOT NULL DEFAULT 'ACTIVO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `profesores`
--

INSERT INTO `profesores` (`id`, `cedula`, `titulo`, `nombre`, `max_horas`, `estado`) VALUES
(10, '1241244', 'Dr.', 'Alexis', 5, 'ACTIVO'),
(11, '44012942', 'Mgs.', 'Paul', 5, 'ACTIVO'),
(12, '5401294', 'Prof.', 'Maoi', 5, 'ACTIVO'),
(13, '8591204', 'Ing.', 'Clara', 5, 'ACTIVO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `secciones`
--

CREATE TABLE `secciones` (
  `id` int(11) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `trayecto` int(11) NOT NULL,
  `trimestre` int(11) NOT NULL DEFAULT 1,
  `cantidad_alumnos` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `secciones`
--

INSERT INTO `secciones` (`id`, `codigo`, `trayecto`, `trimestre`, `cantidad_alumnos`) VALUES
(6, 'SECC-32', 1, 1, 27),
(7, 'SECC-02', 3, 2, 17),
(8, 'SECC-04', 2, 2, 21),
(9, 'SECC-05', 3, 3, 12);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `2fa_secret` varchar(255) DEFAULT NULL,
  `2fa_activo` tinyint(1) DEFAULT 0,
  `rol` enum('administrador','coordinador','profesor') NOT NULL,
  `pregunta_seguridad_1` varchar(255) NOT NULL DEFAULT '¿Fecha de cumpleaños?',
  `respuesta_seguridad_1` varchar(255) NOT NULL DEFAULT 'uptag',
  `pregunta_seguridad_2` varchar(255) NOT NULL DEFAULT '¿Nombre del colegio donde estudiaste?',
  `respuesta_seguridad_2` varchar(255) NOT NULL DEFAULT 'uptag',
  `pregunta_seguridad_3` varchar(255) NOT NULL DEFAULT '¿Ciudad donde naciste?',
  `respuesta_seguridad_3` varchar(255) NOT NULL DEFAULT 'uptag',
  `intentos` int(11) DEFAULT 0,
  `bloqueado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `password`, `2fa_secret`, `2fa_activo`, `rol`, `pregunta_seguridad_1`, `respuesta_seguridad_1`, `pregunta_seguridad_2`, `respuesta_seguridad_2`, `pregunta_seguridad_3`, `respuesta_seguridad_3`, `intentos`, `bloqueado`) VALUES
(8, 'Director Contaduria', 'admin@uptag.edu.ve', '$2y$10$2pd2t1YjFfp1mbnUw1yrV.UGDrz/QetXFWmDhZzmvO3u0RQ.gZ5.u', NULL, 0, 'administrador', '¿Fecha de cumpleaños?', '$2y$10$4zoEGh6yRB8Pg.s8aH5alOWRlMYQL8o8okBXJW/JB6kk5y.B0Gd3G', '¿Nombre del colegio donde estudiaste?', '$2y$10$ATKWNhbO1AXuCb4mkr44h.I8aZ5/SRWATS1Ww3finLruoRTGeGPx.', '¿Ciudad donde naciste?', '$2y$10$wOzSQDr4qyTlb1M2EkYCj.UzhpQKet5PyhxKfgwc4.wW/mN..lS6.', 0, 0);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `aulas`
--
ALTER TABLE `aulas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `horarios`
--
ALTER TABLE `horarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seccion_id` (`seccion_id`),
  ADD KEY `materia_id` (`materia_id`),
  ADD KEY `profesor_id` (`profesor_id`),
  ADD KEY `aula_id` (`aula_id`);

--
-- Indices de la tabla `log_choques`
--
ALTER TABLE `log_choques`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `materias`
--
ALTER TABLE `materias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `profesores`
--
ALTER TABLE `profesores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cedula` (`cedula`);

--
-- Indices de la tabla `secciones`
--
ALTER TABLE `secciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `aulas`
--
ALTER TABLE `aulas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `horarios`
--
ALTER TABLE `horarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `log_choques`
--
ALTER TABLE `log_choques`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `materias`
--
ALTER TABLE `materias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `profesores`
--
ALTER TABLE `profesores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `secciones`
--
ALTER TABLE `secciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD CONSTRAINT `bitacora_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `horarios`
--
ALTER TABLE `horarios`
  ADD CONSTRAINT `horarios_ibfk_1` FOREIGN KEY (`seccion_id`) REFERENCES `secciones` (`id`),
  ADD CONSTRAINT `horarios_ibfk_2` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`),
  ADD CONSTRAINT `horarios_ibfk_3` FOREIGN KEY (`profesor_id`) REFERENCES `profesores` (`id`),
  ADD CONSTRAINT `horarios_ibfk_4` FOREIGN KEY (`aula_id`) REFERENCES `aulas` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
