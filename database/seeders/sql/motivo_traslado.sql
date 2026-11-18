-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 17-11-2026 a las 00:37:18
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
-- Volcado de datos para la tabla `motivo_traslado`
--

INSERT INTO `motivo_traslado` (`id_motivo_traslado`, `descripcion`, `estado`, `updated_at`, `created_at`) VALUES
(1, 'VENTA', '1', '2018-03-19 21:18:15', '2018-03-19 21:18:15'),
(2, 'DEVOLUCION', '1', '2018-04-27 07:04:25', '2018-04-27 07:04:25'),
(3, 'COMPRA', '1', '2018-04-27 07:04:35', '2018-04-27 07:04:35'),
(4, 'TRASLADO MISMA EMPRESA', '1', '2018-04-27 07:05:10', '2018-04-27 07:05:10'),
(5, 'OTRO', '1', '2018-05-17 20:12:00', '2018-04-27 07:05:18');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
