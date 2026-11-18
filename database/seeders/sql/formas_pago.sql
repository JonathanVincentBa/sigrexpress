-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 17-11-2026 a las 00:36:54
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
-- Volcado de datos para la tabla `formas_pago`
--

INSERT INTO `formas_pago` (`id_formas_pago`, `descripcion`, `estado`, `updated_at`, `created_at`) VALUES
(1, 'PAGADO/EFECTIVO', '1', '2021-03-22 22:28:11', '2018-03-19 21:02:58'),
(2, 'AL COBRO', '1', '2018-04-27 07:03:02', '2018-04-27 07:03:02'),
(3, 'FACTURAR', '1', '2018-05-17 20:08:37', '2018-04-27 07:03:53'),
(4, 'TRANSFERENCIA SOLO CON DPTO', '1', '2021-10-30 03:10:16', '2021-05-05 21:15:56'),
(5, 'TRANSFERENCIA PENDIENTE', '1', '2021-10-30 03:10:40', '2021-10-30 03:10:40');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
