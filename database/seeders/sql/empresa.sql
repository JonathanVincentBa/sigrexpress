-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 17-11-2026 a las 00:35:32
-- Versión del servidor: 10.11.19-MariaDB-ubu2204
-- Versión de PHP: 8.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `jhetro-admin_jhetro`
--

--
-- Volcado de datos para la tabla `empresa`
--

INSERT INTO `empresa` (`id_empresa`, `razon_social`, `ruc`, `direccion`, `telefono`, `email`, `representante`, `porcentaje_impuesto`, `logo`, `estado`, `updated_at`, `created_at`) VALUES
(1, 'COMPAÑÍA DE TRANSPORTE DE CARGA PESADA Y LIVIANA JHETRO S.A.', '1715881056001', 'ALGARROBOS E4-115 Y FRANCISCO AGUIRRE', '025113136', 'transportejhetro@gmail.com', 'PEDRO CHAVEZ', 0.00, 'logo_jhetro.png', '1', '2018-05-29 14:22:47', '2018-03-26 10:00:00');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
