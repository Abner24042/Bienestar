-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-04-2026 a las 00:26:03
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_usuarios`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_conversaciones`
--

CREATE TABLE `chat_conversaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `profesional_id` int(11) NOT NULL,
  `ultimo_mensaje_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_mensajes`
--

CREATE TABLE `chat_mensajes` (
  `id` int(11) NOT NULL,
  `conversacion_id` int(11) NOT NULL,
  `remitente_id` int(11) NOT NULL,
  `contenido` text NOT NULL,
  `leido` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `tipo` varchar(10) NOT NULL DEFAULT 'texto',
  `archivo_url` varchar(600) DEFAULT NULL,
  `archivo_nombre` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

CREATE TABLE `citas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `doctor_nombre` varchar(100) NOT NULL,
  `notas` text DEFAULT NULL,
  `google_event_id` varchar(255) DEFAULT NULL,
  `estado` enum('pendiente','confirmada','cancelada','completada') DEFAULT 'pendiente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas_bieniestar`
--

CREATE TABLE `citas_bieniestar` (
  `id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `correo` varchar(100) NOT NULL,
  `profesional_correo` varchar(100) DEFAULT NULL,
  `estado` enum('pendiente','confirmada','cancelada','completada') DEFAULT 'pendiente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `es_solicitud` tinyint(1) NOT NULL DEFAULT 0,
  `sol_profesional` varchar(255) DEFAULT NULL,
  `sol_tipo` varchar(50) DEFAULT NULL,
  `sol_estado` varchar(20) NOT NULL DEFAULT 'pendiente',
  `sol_motivo` text DEFAULT NULL,
  `sol_reasignado` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `citas_bieniestar`
--

INSERT INTO `citas_bieniestar` (`id`, `fecha`, `hora`, `titulo`, `descripcion`, `correo`, `profesional_correo`, `estado`, `created_at`, `updated_at`, `es_solicitud`, `sol_profesional`, `sol_tipo`, `sol_estado`, `sol_motivo`, `sol_reasignado`) VALUES
(1, '1969-12-31', '10:00:00', 'Nutricionista - Dra. María González', NULL, 'abner.borrego@iest.edu.mx', NULL, 'pendiente', '2026-01-29 21:34:02', '2026-01-29 21:34:02', 0, NULL, NULL, 'pendiente', NULL, NULL),
(2, '2026-01-31', '13:00:00', 'Nutricionista - Especialista', NULL, 'abner.borrego@iest.edu.mx', NULL, 'pendiente', '2026-01-30 22:28:56', '2026-01-30 22:28:56', 0, NULL, NULL, 'pendiente', NULL, NULL),
(5, '2026-03-18', '14:00:00', '-,m m m', NULL, 'abner.borrego@iest.edu.mx', NULL, 'pendiente', '2026-01-31 00:27:30', '2026-01-31 00:27:30', 0, NULL, NULL, 'pendiente', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ejercicios`
--

CREATE TABLE `ejercicios` (
  `id` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `duracion` int(11) DEFAULT NULL COMMENT 'en minutos',
  `nivel` enum('principiante','intermedio','avanzado') DEFAULT 'principiante',
  `tipo` enum('cardio','fuerza','flexibilidad','equilibrio') DEFAULT 'cardio',
  `calorias_quemadas` int(11) DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `instrucciones` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ejercicio_api_id` varchar(100) DEFAULT NULL,
  `musculo_objetivo` varchar(100) DEFAULT NULL,
  `equipamiento` varchar(100) DEFAULT NULL,
  `musculos_secundarios` text DEFAULT NULL,
  `auto_generado` tinyint(1) NOT NULL DEFAULT 0,
  `aprobado` tinyint(1) NOT NULL DEFAULT 0,
  `solo_asignado` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ejercicios`
--

INSERT INTO `ejercicios` (`id`, `titulo`, `descripcion`, `duracion`, `nivel`, `tipo`, `calorias_quemadas`, `video_url`, `imagen`, `instrucciones`, `activo`, `created_at`, `ejercicio_api_id`, `musculo_objetivo`, `equipamiento`, `musculos_secundarios`, `auto_generado`, `aprobado`, `solo_asignado`) VALUES
(1, 'Cardio de 7 Minutos', 'Rutina rápida de cardio para quemar calorías', 7, 'principiante', 'cardio', 80, NULL, NULL, 'Realizar cada ejercicio durante 30 segundos con 10 segundos de descanso', 1, '2026-01-22 17:47:06', NULL, NULL, NULL, NULL, 0, 0, 0),
(2, 'Rutina de Fuerza Completa', 'Entrenamiento de cuerpo completo con peso corporal', 30, 'intermedio', 'fuerza', 250, NULL, NULL, 'Realizar 3 series de cada ejercicio con 1 minuto de descanso', 1, '2026-01-22 17:47:06', NULL, NULL, NULL, NULL, 0, 0, 0),
(3, 'Yoga para Principiantes', 'Sesión de yoga suave para flexibilidad', 20, 'principiante', 'flexibilidad', 100, NULL, NULL, 'Seguir las posturas manteniendo respiración profunda', 1, '2026-01-22 17:47:06', NULL, NULL, NULL, NULL, 0, 0, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `favoritos`
--

CREATE TABLE `favoritos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo` varchar(10) NOT NULL,
  `referencia_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `snapshot_data` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `favoritos`
--

INSERT INTO `favoritos` (`id`, `usuario_id`, `tipo`, `referencia_id`, `created_at`, `snapshot_data`) VALUES
(2, 3, 'receta', 1, '2026-03-20 23:22:20', NULL),
(3, 3, 'ejercicio', 2, '2026-03-20 23:22:29', NULL),
(4, 3, 'ejercicio', 1, '2026-03-20 23:22:31', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_planes`
--

CREATE TABLE `historial_planes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `item_titulo` varchar(255) DEFAULT NULL,
  `accion` varchar(20) NOT NULL,
  `profesional_email` varchar(255) DEFAULT NULL,
  `profesional_nombre` varchar(255) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_salud`
--

CREATE TABLE `historial_salud` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `profesional_email` varchar(255) DEFAULT NULL,
  `peso` decimal(5,2) DEFAULT NULL,
  `altura` decimal(4,2) DEFAULT NULL,
  `imc` decimal(4,1) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes_contacto`
--

CREATE TABLE `mensajes_contacto` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `asunto` varchar(200) DEFAULT NULL,
  `mensaje` text DEFAULT NULL,
  `leido` tinyint(1) DEFAULT 0,
  `fecha_envio` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `noticias`
--

CREATE TABLE `noticias` (
  `id` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `contenido` text NOT NULL,
  `resumen` varchar(500) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `categoria` enum('alimentacion','ejercicio','salud-mental','general') DEFAULT 'general',
  `autor` varchar(100) DEFAULT NULL,
  `publicado` tinyint(1) DEFAULT 0,
  `fecha_publicacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `destacado` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `noticias`
--

INSERT INTO `noticias` (`id`, `titulo`, `contenido`, `resumen`, `imagen`, `categoria`, `autor`, `publicado`, `fecha_publicacion`, `created_at`, `destacado`) VALUES
(1, '5 Beneficios de una Alimentación Balanceada', 'Una alimentación balanceada no solo mejora tu salud física...', 'Descubre cómo una dieta equilibrada transforma tu vida', NULL, 'alimentacion', 'Dr. Juan Pérez', 1, '2026-01-22 17:47:06', '2026-01-22 17:47:06', 0),
(2, 'Ejercicio Matutino: Clave para un Día Productivo', 'Hacer ejercicio por la mañana tiene múltiples beneficios...', 'El ejercicio matutino mejora tu energía y productividad', NULL, 'ejercicio', 'Lic. María González', 1, '2026-01-22 17:47:06', '2026-01-22 17:47:06', 0),
(3, 'Técnicas de Mindfulness para Reducir el Estrés', 'El mindfulness es una práctica que ayuda a manejar el estrés...', 'Aprende técnicas de mindfulness para tu bienestar mental', NULL, 'salud-mental', 'Psic. Carlos Ramírez', 1, '2026-01-22 17:47:06', '2026-01-22 17:47:06', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `planes_alimenticios`
--

CREATE TABLE `planes_alimenticios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `objetivo` varchar(200) DEFAULT NULL,
  `duracion_semanas` int(11) DEFAULT 1,
  `nutriologo_correo` varchar(200) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plan_alimenticio_recetas`
--

CREATE TABLE `plan_alimenticio_recetas` (
  `id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `receta_id` int(11) NOT NULL,
  `dia_semana` tinyint(4) DEFAULT 1 COMMENT '1=Lunes 7=Domingo',
  `tiempo_comida` varchar(50) DEFAULT 'comida' COMMENT 'desayuno|almuerzo|merienda|cena',
  `porciones` decimal(4,1) DEFAULT 1.0,
  `notas` text DEFAULT NULL,
  `orden` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plan_ejercicios`
--

CREATE TABLE `plan_ejercicios` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `ejercicio_id` int(11) NOT NULL,
  `asignado_por` varchar(255) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `fecha_asignacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plan_recetas`
--

CREATE TABLE `plan_recetas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `receta_id` int(11) NOT NULL,
  `asignado_por` varchar(255) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `fecha_asignacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `dia_semana` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rate_limit`
--

CREATE TABLE `rate_limit` (
  `ip_hash` varchar(64) NOT NULL,
  `route_type` varchar(10) NOT NULL,
  `requests` int(11) NOT NULL DEFAULT 1,
  `window_start` int(11) NOT NULL,
  `blocked_until` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recetas`
--

CREATE TABLE `recetas` (
  `id` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `ingredientes` text NOT NULL,
  `instrucciones` text NOT NULL,
  `tiempo_preparacion` int(11) DEFAULT NULL COMMENT 'en minutos',
  `porciones` int(11) DEFAULT 1,
  `calorias` int(11) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `categoria` varchar(50) NOT NULL DEFAULT 'comida',
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `recetas`
--

INSERT INTO `recetas` (`id`, `titulo`, `descripcion`, `ingredientes`, `instrucciones`, `tiempo_preparacion`, `porciones`, `calorias`, `imagen`, `categoria`, `activo`, `created_at`) VALUES
(1, 'Ensalada César Saludable', 'Una versión ligera de la clásica ensalada César', 'Lechuga romana, Pechuga de pollo, Queso parmesano, Aderezo César light', 'Picar la lechuga, Cocinar el pollo, Mezclar ingredientes', 15, 2, 350, NULL, 'comida', 1, '2026-01-22 17:47:06'),
(2, 'Smoothie Verde Energético', 'Smoothie lleno de nutrientes para comenzar el día', 'Espinaca, Plátano, Manzana verde, Yogurt natural, Miel', 'Licuar todos los ingredientes hasta obtener consistencia suave', 5, 1, 180, NULL, 'desayuno', 1, '2026-01-22 17:47:06'),
(3, 'Tacos de Pescado Light', 'Tacos saludables con pescado a la parrilla', 'Filete de pescado, Tortillas de maíz, Repollo, Aguacate, Limón', 'Asar el pescado, Preparar vegetales, Armar tacos', 20, 3, 400, NULL, 'cena', 1, '2026-01-22 17:47:06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recomendaciones`
--

CREATE TABLE `recomendaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `profesional_id` varchar(255) DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `contenido` text DEFAULT NULL,
  `tipo` varchar(50) DEFAULT 'general',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `test_resultados`
--

CREATE TABLE `test_resultados` (
  `id` int(11) NOT NULL,
  `correo` varchar(255) NOT NULL,
  `puntaje` int(11) NOT NULL,
  `nivel` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `test_resultados`
--

INSERT INTO `test_resultados` (`id`, `correo`, `puntaje`, `nivel`, `created_at`) VALUES
(1, 'admin@bieniestar.com', 16, 'Preocupante', '2026-02-26 22:26:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `contrasena` varchar(255) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `rol` varchar(50) DEFAULT 'usuario',
  `area` varchar(50) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `activo` tinyint(1) DEFAULT 1,
  `peso` decimal(5,2) DEFAULT NULL,
  `altura` decimal(4,2) DEFAULT NULL,
  `login_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contrasena`, `foto`, `rol`, `area`, `fecha`, `activo`, `peso`, `altura`, `login_count`) VALUES
(3, 'Administrador', 'admin@bieniestar.com', '$2y$10$IAfWnv50Tr40n3pKsJNHA.aJ6x1i51nEnwdHFyb5TM3TIqTiKlT62', NULL, 'Administrador', 'Sistemas', '2026-04-23 23:04:36', 1, NULL, NULL, 2),
(4, 'Usuario Prueba', 'usuario@test.com', '$2y$10$WmnZmAQ/jssrshU04oB81.Hp8HygERLlOQG8l4hexV142qSGlwMKG', NULL, 'nutriologo', 'Estudiante', '2026-04-21 23:58:04', 1, NULL, NULL, 2),
(5, 'Abner Borrego Vargas', 'abner.borrego@iest.edu.mx', '$2y$10$I52Jph3.0gAx3R5hQC9II.4Vs187svSEKufjIQTKrQ1hpCHJLFsLW', 'https://lh3.googleusercontent.com/a/ACg8ocJHkRefQusen3Ivfs5MysyYXVaDTpWJtVSlN3h4i6T1l16RQwm1=s1024-c', 'usuario', NULL, '2026-02-03 17:57:29', 1, NULL, NULL, 0),
(6, 'Prueba 2', 'prueba2@test.com', '$2y$10$oAoBr9XPq0fIbyl/OTSrQeAcVbgWc0AbSzrlA3eQWC.Zkh51WJWBK', NULL, 'usuario', 'Prueba', '2026-04-22 00:26:33', 1, NULL, NULL, 3),
(7, 'qwe', 'qwe@qwe.com', '$2y$10$gc/FakVXHb/7C5JPlznffOz8Zk1iRi0LbdBnIyYgzIeg0jMoYRLSq', NULL, 'usuario', 'qwe', '2026-04-21 23:52:34', 1, NULL, NULL, 2);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `chat_conversaciones`
--
ALTER TABLE `chat_conversaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_conv` (`usuario_id`,`profesional_id`);

--
-- Indices de la tabla `chat_mensajes`
--
ALTER TABLE `chat_mensajes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_conv_created` (`conversacion_id`,`created_at`),
  ADD KEY `idx_leido` (`conversacion_id`,`leido`);

--
-- Indices de la tabla `citas`
--
ALTER TABLE `citas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `fecha` (`fecha`);

--
-- Indices de la tabla `citas_bieniestar`
--
ALTER TABLE `citas_bieniestar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fecha` (`fecha`),
  ADD KEY `idx_correo` (`correo`),
  ADD KEY `idx_profesional_correo` (`profesional_correo`);

--
-- Indices de la tabla `ejercicios`
--
ALTER TABLE `ejercicios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_nivel` (`nivel`),
  ADD KEY `idx_tipo` (`tipo`);

--
-- Indices de la tabla `favoritos`
--
ALTER TABLE `favoritos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_fav` (`usuario_id`,`tipo`,`referencia_id`),
  ADD KEY `idx_usuario_tipo` (`usuario_id`,`tipo`);

--
-- Indices de la tabla `historial_planes`
--
ALTER TABLE `historial_planes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario` (`usuario_id`);

--
-- Indices de la tabla `historial_salud`
--
ALTER TABLE `historial_salud`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario` (`usuario_id`);

--
-- Indices de la tabla `mensajes_contacto`
--
ALTER TABLE `mensajes_contacto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_leido` (`leido`),
  ADD KEY `idx_fecha` (`fecha_envio`);

--
-- Indices de la tabla `noticias`
--
ALTER TABLE `noticias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_categoria` (`categoria`),
  ADD KEY `idx_publicado` (`publicado`);

--
-- Indices de la tabla `planes_alimenticios`
--
ALTER TABLE `planes_alimenticios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `plan_alimenticio_recetas`
--
ALTER TABLE `plan_alimenticio_recetas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plan_id` (`plan_id`);

--
-- Indices de la tabla `plan_ejercicios`
--
ALTER TABLE `plan_ejercicios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_asig_ej` (`usuario_id`,`ejercicio_id`);

--
-- Indices de la tabla `plan_recetas`
--
ALTER TABLE `plan_recetas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_asig_rec` (`usuario_id`,`receta_id`,`dia_semana`);

--
-- Indices de la tabla `rate_limit`
--
ALTER TABLE `rate_limit`
  ADD PRIMARY KEY (`ip_hash`,`route_type`);

--
-- Indices de la tabla `recetas`
--
ALTER TABLE `recetas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_categoria` (`categoria`);

--
-- Indices de la tabla `recomendaciones`
--
ALTER TABLE `recomendaciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `test_resultados`
--
ALTER TABLE `test_resultados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_correo` (`correo`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `idx_correo` (`correo`),
  ADD KEY `idx_rol` (`rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `chat_conversaciones`
--
ALTER TABLE `chat_conversaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chat_mensajes`
--
ALTER TABLE `chat_mensajes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `citas_bieniestar`
--
ALTER TABLE `citas_bieniestar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `ejercicios`
--
ALTER TABLE `ejercicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `favoritos`
--
ALTER TABLE `favoritos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `historial_planes`
--
ALTER TABLE `historial_planes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `historial_salud`
--
ALTER TABLE `historial_salud`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mensajes_contacto`
--
ALTER TABLE `mensajes_contacto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `noticias`
--
ALTER TABLE `noticias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `planes_alimenticios`
--
ALTER TABLE `planes_alimenticios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `plan_alimenticio_recetas`
--
ALTER TABLE `plan_alimenticio_recetas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `plan_ejercicios`
--
ALTER TABLE `plan_ejercicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `plan_recetas`
--
ALTER TABLE `plan_recetas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `recetas`
--
ALTER TABLE `recetas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `recomendaciones`
--
ALTER TABLE `recomendaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `test_resultados`
--
ALTER TABLE `test_resultados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `citas`
--
ALTER TABLE `citas`
  ADD CONSTRAINT `citas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `citas_bieniestar`
--
ALTER TABLE `citas_bieniestar`
  ADD CONSTRAINT `citas_bieniestar_ibfk_1` FOREIGN KEY (`correo`) REFERENCES `usuarios` (`correo`) ON DELETE CASCADE;

--
-- Filtros para la tabla `plan_alimenticio_recetas`
--
ALTER TABLE `plan_alimenticio_recetas`
  ADD CONSTRAINT `plan_alimenticio_recetas_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `planes_alimenticios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
