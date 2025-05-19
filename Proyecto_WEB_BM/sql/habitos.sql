-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 17-05-2025 a las 01:50:39
-- Versión del servidor: 9.1.0
-- Versión de PHP: 8.3.14
-- VERSION NO ACTUALIZADA --- AGREGAR ARCHIVO SABADO 17 :)

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `habitos`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias_habitos`
--

DROP TABLE IF EXISTS `categorias_habitos`;
CREATE TABLE IF NOT EXISTS `categorias_habitos` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text,
  `color` varchar(7) DEFAULT '#007bff',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `categorias_habitos`
--

INSERT INTO `categorias_habitos` (`id`, `nombre`, `descripcion`, `color`, `fecha_creacion`) VALUES
(1, 'Salud', 'Hábitos relacionados con la salud física y mental', '#28a745', '2025-05-15 20:52:56'),
(2, 'Productividad', 'Hábitos para mejorar la eficiencia y organización', '#007bff', '2025-05-15 20:52:56'),
(3, 'Aprendizaje', 'Hábitos de estudio y desarrollo personal', '#ffc107', '2025-05-15 20:52:56'),
(4, 'Bienestar', 'Hábitos para mejorar la calidad de vida', '#17a2b8', '2025-05-15 20:52:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_habitos`
--

DROP TABLE IF EXISTS `estados_habitos`;
CREATE TABLE IF NOT EXISTS `estados_habitos` (
  `id` tinyint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(20) NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `color` varchar(7) DEFAULT '#000000',
  `orden` tinyint DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `estados_habitos`
--

INSERT INTO `estados_habitos` (`id`, `nombre`, `descripcion`, `color`, `orden`) VALUES
(1, 'pendiente', 'Hábito pendiente de completar', '#ffc107', 2),
(2, 'atrasado', 'Hábito con fecha de vencimiento pasada', '#dc3545', 3),
(3, 'completado', 'Hábito completado exitosamente', '#28a745', 1),
(4, 'activo', 'Hábito activo y pendiente de completar', '#007bff', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estatus`
--

DROP TABLE IF EXISTS `estatus`;
CREATE TABLE IF NOT EXISTS `estatus` (
  `id` tinyint UNSIGNED NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `estatus`
--

INSERT INTO `estatus` (`id`, `descripcion`) VALUES
(1, 'Activo'),
(2, 'Inactivo'),
(3, 'Suspendido');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `habitos`
--

DROP TABLE IF EXISTS `habitos`;
CREATE TABLE IF NOT EXISTS `habitos` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario_id` int UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `frecuencia` varchar(20) DEFAULT NULL,
  `meta` int DEFAULT '1',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `estado_id` tinyint UNSIGNED DEFAULT '1',
  `recordatorio` time DEFAULT NULL,
  `dias_semana` varchar(20) DEFAULT NULL,
  `categoria_id` int UNSIGNED DEFAULT NULL,
  `fecha_registro` date DEFAULT NULL,
  `hora_registro` time DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_habitos_usuario` (`usuario_id`),
  KEY `idx_habitos_categoria` (`categoria_id`),
  KEY `idx_habitos_estado` (`estado_id`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `habitos`
--

INSERT INTO `habitos` (`id`, `usuario_id`, `nombre`, `descripcion`, `frecuencia`, `meta`, `fecha_creacion`, `fecha_actualizacion`, `estado_id`, `recordatorio`, `dias_semana`, `categoria_id`, `fecha_registro`, `hora_registro`) VALUES
(20, 2, 'Churrumais', 'asdasd', 'diaria', 0, '2025-05-17 00:39:43', '2025-05-16 18:39:43', 1, NULL, NULL, 3, '2025-05-16', '18:44:00'),
(19, 2, 'Zarten', 'asdasd', 'diaria', 0, '2025-05-17 00:14:51', '2025-05-16 18:14:51', 1, NULL, NULL, 2, '2025-05-16', '23:14:00'),
(16, 2, 'prueba2', 'asdasd', 'diaria', 0, '2025-05-16 22:23:11', '2025-05-16 16:23:11', 1, NULL, NULL, 3, '2025-05-16', '19:23:00'),
(17, 2, 'prueba 3', 'sdsd', 'diaria', 0, '2025-05-16 22:32:38', '2025-05-16 16:32:38', 1, NULL, NULL, 3, '2025-05-17', '16:34:00'),
(21, 3, 'Hábito de Prueba', 'Hábito para probar el gráfico', 'diaria', 1, '2025-05-16 19:14:59', '2025-05-16 19:14:59', 1, NULL, NULL, NULL, '2025-05-16', '19:14:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro_habitos`
--

DROP TABLE IF EXISTS `registro_habitos`;
CREATE TABLE IF NOT EXISTS `registro_habitos` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `habito_id` int UNSIGNED NOT NULL,
  `fecha` date NOT NULL,
  `completado` tinyint(1) DEFAULT '0',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `estado_id` tinyint UNSIGNED DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `habito_id` (`habito_id`),
  KEY `idx_registro_fecha` (`fecha`),
  KEY `estado_id` (`estado_id`)
) ENGINE=MyISAM AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `registro_habitos`
--

INSERT INTO `registro_habitos` (`id`, `habito_id`, `fecha`, `completado`, `fecha_creacion`, `estado_id`) VALUES
(19, 21, '2025-04-19', 1, '2025-05-16 19:15:24', 1),
(18, 21, '2025-04-18', 0, '2025-05-16 19:15:24', 1),
(17, 21, '2025-04-17', 0, '2025-05-16 19:15:24', 1),
(16, 20, '2025-05-16', 1, '2025-05-16 18:48:17', 1),
(15, 19, '2025-05-16', 0, '2025-05-16 18:33:07', 1),
(14, 18, '2025-05-16', 1, '2025-05-16 18:33:06', 1),
(13, 16, '2025-05-16', 1, '2025-05-16 18:33:06', 1),
(8, 16, '2025-05-17', 0, '2025-05-16 18:03:33', 2),
(9, 18, '2025-05-17', 1, '2025-05-16 18:03:45', 3),
(20, 21, '2025-04-20', 1, '2025-05-16 19:15:24', 1),
(21, 21, '2025-04-21', 1, '2025-05-16 19:15:24', 1),
(22, 21, '2025-04-22', 0, '2025-05-16 19:15:24', 1),
(23, 21, '2025-04-23', 0, '2025-05-16 19:15:24', 1),
(24, 21, '2025-04-24', 0, '2025-05-16 19:15:24', 1),
(25, 21, '2025-04-25', 0, '2025-05-16 19:15:24', 1),
(26, 21, '2025-04-26', 1, '2025-05-16 19:15:24', 1),
(27, 21, '2025-04-27', 0, '2025-05-16 19:15:24', 1),
(28, 21, '2025-04-28', 1, '2025-05-16 19:15:24', 1),
(29, 21, '2025-04-29', 0, '2025-05-16 19:15:24', 1),
(30, 21, '2025-04-30', 1, '2025-05-16 19:15:24', 1),
(31, 21, '2025-05-01', 1, '2025-05-16 19:15:24', 1),
(32, 21, '2025-05-02', 0, '2025-05-16 19:15:24', 1),
(33, 21, '2025-05-03', 0, '2025-05-16 19:15:24', 1),
(34, 21, '2025-05-04', 1, '2025-05-16 19:15:24', 1),
(35, 21, '2025-05-05', 0, '2025-05-16 19:15:24', 1),
(36, 21, '2025-05-06', 0, '2025-05-16 19:15:24', 1),
(37, 21, '2025-05-07', 1, '2025-05-16 19:15:24', 1),
(38, 21, '2025-05-08', 1, '2025-05-16 19:15:24', 1),
(39, 21, '2025-05-09', 1, '2025-05-16 19:15:24', 1),
(40, 21, '2025-05-10', 1, '2025-05-16 19:15:24', 1),
(41, 21, '2025-05-11', 1, '2025-05-16 19:15:24', 1),
(42, 21, '2025-05-12', 1, '2025-05-16 19:15:24', 1),
(43, 21, '2025-05-13', 1, '2025-05-16 19:15:24', 1),
(44, 21, '2025-05-14', 1, '2025-05-16 19:15:24', 1),
(45, 21, '2025-05-15', 1, '2025-05-16 19:15:24', 1),
(46, 21, '2025-05-16', 1, '2025-05-16 19:15:24', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` tinyint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`) VALUES
(1, 'Administrador'),
(2, 'Usuario');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguimiento_sueno`
--

DROP TABLE IF EXISTS `seguimiento_sueno`;
CREATE TABLE IF NOT EXISTS `seguimiento_sueno` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario_id` int UNSIGNED NOT NULL,
  `fecha` date NOT NULL,
  `hora_dormir` time NOT NULL,
  `hora_despertar` time NOT NULL,
  `duracion` decimal(4,2) GENERATED ALWAYS AS ((timestampdiff(MINUTE,`hora_dormir`,`hora_despertar`) / 60)) STORED,
  `calidad` enum('Buena','Regular','Mala') DEFAULT NULL,
  `comentarios` text,
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `seguimiento_sueno`
--

INSERT INTO `seguimiento_sueno` (`id`, `usuario_id`, `fecha`, `hora_dormir`, `hora_despertar`, `calidad`, `comentarios`, `fecha_creacion`) VALUES
(9, 2, '2025-05-16', '02:36:00', '08:38:00', 'Regular', 'dfgdfgf', '2025-05-16 02:38:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol_id` tinyint UNSIGNED NOT NULL DEFAULT '2',
  `estatus_id` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `correo` (`correo`),
  KEY `rol_id` (`rol_id`),
  KEY `estatus_id` (`estatus_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `password`, `rol_id`, `estatus_id`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'Admin', 'admin@test.com', '$2y$10$Zj33cnqjnVQ4Ndi7PTQL.uUn7OthGy3BsrAlcOf8AOLA7kIDZLHrO', 1, 1, '2025-05-15 20:52:37', '2025-05-15 20:56:14'),
(2, 'AlanT', 'usuario@test.com', '$2y$10$1iest9XKS2W9agcv7IAg.uqPbQxc9OZiDwhlvhfkTh4n.RaZ6O.tG', 2, 1, '2025-05-15 20:52:43', '2025-05-15 20:56:57'),
(3, 'UsuarioPrueba', 'prueba@habito.com', '$2y$10$yrb4EMRX1AzGkhsciDfy8euefrqAJAHr5b96MDX9WPmCC2dbRsmGu', 2, 1, '2025-05-16 19:08:42', '2025-05-16 19:08:42');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;