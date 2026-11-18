-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 17-11-2026 a las 00:38:31
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
-- Volcado de datos para la tabla `sucursal`
--

INSERT INTO `sucursal` (`id_sucursal`, `id_empresa`, `id_ciudad`, `direccion`, `telefono`, `email`, `estado`, `updated_at`, `created_at`) VALUES
(1, 1, 1, 'ALGARROBOS E4-115 Y FRANCISCO AGUIRRE', '025113136', 'transportejhetro@gmail.com', '1', '2018-04-10 07:56:37', '2018-03-26 21:38:33'),
(2, 1, 3, 'BUERAN 3-56 Y NAMANGOZA', '072869529', 'transportejhetro@gmail.com', '1', '2018-04-10 07:58:02', '2018-03-27 10:00:00'),
(3, 1, 2, 'LOS CHASQUIS 21-17 Y PICHINCHA', '032842485', 'transportejhetro@gmail.com', '1', '2018-04-10 07:58:48', '2018-03-27 10:00:00'),
(4, 1, 4, 'OVIEDO 1165 Y JAIME RIVADENEIRA DIAGONAL AL HOTEL MONTECARLO', '0998582720', 'transportejhetro@gmail.com', '1', '2018-05-29 17:52:57', '2018-03-29 12:51:45'),
(5, 1, 5, 'RIO CUTUCHI 6-04 Y PASAJE RIO CHALUPA', '0992592466', 'transportejhetro@gmail.com', '1', '2018-04-10 08:01:20', '2018-03-29 13:22:31'),
(11, 1, 6, 'JOSÉ DE OROZCO 38-30 ENTRE TNTE. LATUS Y CARLOS ZAMBRANO', '0987658867', 'transportejhetro@gmail.com', '1', '2018-05-28 19:49:56', '2018-05-28 19:49:56'),
(12, 1, 7, 'C.C. TERMINAL TERRESTRE BLOQUE A LOCAL 29', '025113136', 'transportejhetro@gmail.com', '1', '2018-05-28 19:51:28', '2018-05-28 19:51:28'),
(13, 1, 61, 'ALONSO DE ANGULO CESAR CHIRIBOGA S10 48 Y JUAN FLORES JUNTO ALMA LOJANA', '5113136', 'transportejhetro@gmail.com', '1', '2018-07-23 23:54:49', '2018-07-23 23:54:49'),
(14, 1, 62, 'AV AVELINA LASSO 118 Y ALFREDO DAVILA PARQUE SAN RAFAEL EDUARDO KIMGMAN', '5113136', 'transportejhetro@gmail.com', '1', '2018-07-26 03:09:54', '2018-07-23 23:55:38'),
(15, 1, 83, 'ALONSO DE MERCADILLO Y VERSALLES HOTEL MAJESTIC', '025104006', 'danielarosalesparedes@hotmail.com', '1', '2018-10-29 21:07:34', '2018-10-29 21:06:14'),
(16, 1, 109, 'SIMON BOLIVAR FRENTE HOTEL', '0985958100', 'henryhuacav@hotmail.com', '1', '2020-06-24 03:11:56', '2019-08-03 02:11:14'),
(17, 1, 11, 'VASQUEZ CORREA ENTRE COLON Y CUENCA DETRAS CC ORQUIDEAS', '0992553984', 'adrianaguachun@gmail.com', '1', '2019-09-24 17:59:37', '2019-09-24 17:59:37'),
(18, 1, 102, 'LUQUE', '0984975861', 'transportejhetro@gmail.com', '1', '2019-10-02 01:17:09', '2019-10-02 01:15:50'),
(19, 1, 103, 'QUITUMBE', '025113136', 'transportejhetro@gmail.com', '1', '2019-10-19 02:56:36', '2019-10-19 02:56:03'),
(20, 1, 110, 'SANTA MARTHA BUCAY', '0992300357', 'pedrosantiago013@gmail.com', '1', '2020-07-16 17:11:00', '2020-07-16 17:11:00'),
(21, 1, 111, 'LA TRONCAL', '0999588758', 'transportejhetro@gmail.com', '1', '2020-07-25 00:09:04', '2020-07-25 00:09:04'),
(22, 1, 112, 'CUMANDA STA MARTHA', '0984975861', 'transportejhetro@gmail.com', '1', '2020-07-28 21:34:10', '2020-07-28 21:34:10'),
(23, 1, 13, 'AV 31 DE OCTUBRE Y QUITO ESQ', '0982205735', 'jorgevilla5@hotmail.com', '1', '2020-09-07 23:43:15', '2020-09-07 23:43:15'),
(24, 1, 49, 'AV. INTEROCEÁNICA OE4-70 Y RODRIGO NUÑEZ', '2378704', 'lorsarai@hotmail.com', '1', '2020-09-14 22:31:03', '2020-09-14 22:31:03'),
(25, 1, 19, 'ABRAHAM CALASACON', '0981788370', 'transportesjhetro@gmail.com', '1', '2021-04-22 02:10:53', '2021-04-22 02:10:53'),
(26, 1, 130, 'COLON Y REINA VICTORIA', '0992574397', 'transportesjhetro@hotmail.com', '1', '2021-07-30 02:38:21', '2021-07-30 02:38:21'),
(27, 1, 133, 'CALIXTO MUSO Y PANAMERICANA NORTE', '0962826118', 'tefyr.888@gmail.com', '1', '2022-01-28 01:14:22', '2022-01-28 01:14:22'),
(28, 1, 43, 'GANGOTENA Y SHYRIS DETRAS DE LOS BOMBEROS', '3808680', 'judyrojass@hotmail.com', '1', '2022-03-09 23:50:53', '2022-03-09 23:50:53'),
(29, 1, 137, 'TAMBILLO', '0980222672', 'crismilu583@gmail.com', '1', '2022-05-03 16:58:23', '2022-05-03 16:58:23'),
(30, 1, 141, '---', '000', 'danielarosalesparedes@hotail.com', '1', '2023-02-03 00:35:57', '2023-02-03 00:35:57'),
(31, 1, 143, 'AMBATO', '0987746396', 'kserranog@hotmail.com', '1', '2024-08-06 21:57:17', '2024-08-06 21:54:29'),
(32, 1, 115, 'JAIME ROLDOS AGUILERA ENTRE VICTOR EMILIO ESTRADA Y CARLOS JULIO AROSEMENA', '0967173362', 'eliroso15@gmail.com', '1', '2024-09-02 17:12:52', '2024-09-02 17:12:52'),
(33, 1, 54, 'AV PABLO GUARDERAS ENTRE ALISOS Y NOGALES', '0984975861', 'daniela.rosales@transportejhetro.com', '1', '2024-10-02 17:59:46', '2024-10-02 17:59:46'),
(34, 1, 55, 'ALOAG', '0993604597', 'lizbeth.quimba1466@utc.edu.ec', '1', '2024-10-08 21:50:06', '2024-10-08 21:50:06'),
(35, 1, 54, 'PABLO GUARDERAS ENTRE ALISOS Y NOGALES', '0997417530', 'jenifer.pilaguano.cris@gmail.com', '1', '2024-10-08 21:54:16', '2024-10-08 21:54:16'),
(36, 1, 144, 'AV. HNO MIGUEL', '0986675243', 'nathyparra20@gmail.com', '1', '2024-11-08 18:10:55', '2024-11-08 18:10:55'),
(37, 1, 145, 'CENTRO RIOBAMBA', '0962683042', 'luisfabian10@hotmail.com', '1', '2024-11-18 23:38:25', '2024-11-18 23:38:25'),
(38, 1, 147, 'LA PRENSA', '0997417530', 'henrryhuaca@hotmail.com', '1', '2025-03-26 01:04:23', '2025-03-26 01:04:23'),
(39, 1, 150, 'GUAMANI CASA ANGEL CHISAG', '025555555', 'daniela.rosales@jhetro.com', '1', '2026-01-07 15:52:10', '2026-01-07 15:50:31');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
