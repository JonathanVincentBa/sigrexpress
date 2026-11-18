-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 17-11-2026 a las 00:39:25
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
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `id_rol`, `id_persona`, `name`, `email`, `password`, `remember_token`, `created_at`, `updated_at`, `estado`) VALUES
(1, 1, 1, 'JONATHAN VINCENT', 'jonathanvincent@outlook.com', '$2y$12$e755YRT./M.uFBN7zCDYmenBxp/bR7W0/szxA1.zWmNNa8lL4q9XS', 'qee1oLg2G84OYcNnAZP6pl5nmFIA8Ja5MS4IyYffDHvKhq9jcyfNVsA7Wmem', '2018-04-27 03:58:55', '2023-03-31 20:23:45', '1'),
(2, 1, 2, 'DIANA BLACIO', 'dblacio07@hotmail.com', '$2y$10$MpqJ/5ffqUVr2fniT4LDTOtDXHF0RP9R.vpctMlC9LM17HLdcNsPG', 'kGzs1FFKNOyxpvPDAWa46SdonMbMc725Q41I9RL0LrK9SFRAQ63ZibPVmOTG', '2018-05-20 18:09:06', '2021-09-30 02:46:03', '0'),
(3, 2, 134, 'XIMENA FERNANDEZ', 'xime.fernandez.750@hotmail.com', '$2y$10$GRyS79L/M9GIhoStAAUasORMZxItYvjCjIBclH52l/NxnvBXsFkv6', 'bLQjTyG0EcmA1YGH3JkbGMO2wM2Waf23AE8w4hJUSgiCY3eV23TTALgBtQ9n', '2018-05-28 19:56:15', '2019-02-26 20:15:00', '0'),
(4, 2, 136, 'KARINA MERCEDES SERRANO GUAITARA', 'kserranog@hotmail.com', '$2y$10$ctX7FTcyCd98rpwuOAcVre.LNsxkINfM/jgKLyNTZVgleW0KPb/tG', 'lSUlq8wpdXeppUnx3ydWEhTBRjMhw5B11ySwoqABHzxPP182Scwb5zULZMwx', '2018-05-28 22:11:43', '2020-06-13 16:39:50', '1'),
(5, 1, 150, 'DANIELA ROSALES', 'danielarosalesparedes@hotmail.com', '$2y$10$tRtdnvCRAw02NsdsbW0qauQmFxedw5oEuD6O7BlRZmY3xkD0qqmQi', 'yq9uBb9lAoJO4VFFmnAM7VUvkDXuYbxx4sjRe8ftZ3cz3aBrlfAGtnSFVtD1', '2018-05-29 02:11:02', '2025-07-03 16:09:01', '1'),
(6, 1, 556, 'PEDRO  CHAVEZ', 'transportejhetro@gmail.com', '$2y$10$Icgs4sFrPxOYfZeXbTzAee9OfdtEaVoOar0SFvpvHF/oUaxfAF0vS', 'dv8aNbN6tkS4l3oQozHSLg31qDGuLCQt2MqlWaVZE1dlaL5W3dsBcizUm9Py', '2018-07-23 23:57:26', '2022-10-26 15:24:21', '1'),
(7, 2, 1055, 'KARINA NOEMI CORTEZ', 'noemy11keicy@outlook.com', '$2y$10$EFpxCNg1HgOH24h1H2WSZ.fhE4U2faMdt9.vQIG46Ya.i9AnNgkea', 'VLNIEXV8Ktno63O4fSwn9DTDBhEA9kkqEvlFzwT7uhq7Tu462IAXoQ4YD21J', '2018-09-25 19:07:05', '2019-02-27 22:31:11', '0'),
(8, 2, 1231, 'MARIA JOSE CEVALLOS CHILUIZA', 'majiithoc@gmail.com', '$2y$10$oyuCgm2hdh9nndYc.eWDRunhQgdW4h1XxiO5W8hmoWkoU1d.ptZs2', 'PF2LiffA60wgMfeL3ZPaIKI6eWQQTk9p2zudwCAZM1qX3qyCgt3cN3H24ERP', '2018-10-22 20:13:36', '2020-07-10 19:48:40', '0'),
(9, 2, 1342, 'IRIS CAROLINA ALIENDRES VILLANUEVA', 'iris.c.aliendres@gmail.com', '$2y$10$uo3LyBppRHj/hSdKPOdTn.BhgowHZ0dh7V5CBFq6TtCIG.ztjfSBa', 'mqkQRJ1l1hmQA5zEfJx2mojQKbwUUKDLGRRwcrrY1P8AQUSjd7DbIKKRx0Fi', '2018-11-09 15:15:20', '2022-08-22 21:41:19', '1'),
(10, 2, 1744, 'MARCO SOTO', 'elizabra1407@hotmail.com', '$2y$10$627btisedOXaH2YRARCEpe8ogj5pkU/UXfFeoMy6EkioE3Vta8nci', 'MGEvISdqfIGfcc4leZ3Un6zIZSccvEcFVTlJ7s8K7WwUN8tOVn3m10rORBjl', '2019-01-26 23:09:47', '2019-02-06 02:45:58', '1'),
(11, 2, 1882, 'GUSTAVO VIDAL', 'gustavo1587@gmail.com', '$2y$10$6VSWA2H/Ekk9bsvHWc0V8ez..Ohc02Q79QhX2M30.gTHa3iZ0rvjS', 'b6MN8IFZr9od6Avvv6oKFbQzTp6QdZjYV2a8WFtreetPq9xt87ydeDQhd6Ex', '2019-02-19 19:04:49', '2021-10-25 23:28:58', '1'),
(12, 2, 2127, 'JESUS VELASCO', 'javo2412@hotmail.com', '$2y$10$YIaBUp8GdY6IvoZ8QCfrZOgZ8A8UcDzeIF/n97xWYreH/Ord/PcYy', 'L9QKeC6rWBhxI0jSUaZ0tN3100d1PksTvca8foXpgdCXt3onitZy0vnasHzE', '2019-04-09 00:54:08', '2022-08-22 21:42:03', '1'),
(13, 2, 2719, 'HENRY HUACA', 'henryhuacav@hotmail.com', '$2y$10$AqzxDYT3FT4WFl/dSUFiM.XAtBKr43vKGd/UHoAmwkJJ0OieJtWQC', NULL, '2019-08-03 02:13:44', '2019-08-03 02:13:44', ''),
(14, 2, 3085, 'ADRIANA GUACHUN', 'adrianaguachun@gmail.com', '$2y$10$7DpD/dVIZC2S9e88lNqqVelG4a8tMMz1bdrO2zgEIaL8r8raSkuFu', NULL, '2019-09-24 18:03:35', '2019-09-24 18:03:35', ''),
(15, 2, 3695, 'NICOL  GARCIA', 'gabiprins19@gmail.com', '$2y$10$ZwH7bcmLUFLIwSuHWzTmMO3Kd0H91D5EGQ5jWtUitNDoSSu.LuQSy', 'NkeWNXsqtUgQETKKBzQ5nIM2eNd7f5BWBHtp5Ew9T5FNMRYH57dyx9iKVmbF', '2020-01-27 02:48:36', '2020-07-10 19:48:26', '0'),
(16, 2, 3725, 'EDWIN CASTILLO', 'edwinmcc38@gmail.com', '$2y$10$EW72GgGwGgU2zIRrUkol.eg9k9K3/BwF.cE05GrmoyOrynE7I1g6K', 'tq5Y8EPX7XbYyglzFi2VXChiwb3ckJOwYwhVeiN6QZ5B7qsaYqAtKktgVgHK', '2020-01-31 15:48:28', '2022-11-15 16:03:03', ''),
(17, 2, 3857, 'DAVID IBARRA IBARRA', 'transportesjhetro@hotmail.com', '$2y$10$9pQF6axT31jGy1yEWEIaGOx0iipHZdtkPVqjT75wpwtGdvXHHeyQ.', 'Yn91GriaL9vmlv2B6QI4WEQEjx5sOQ0DZb0F4OonYwmTyfEv51HCx98gLeHR', '2020-03-03 15:47:07', '2023-11-29 23:33:55', ''),
(18, 2, 1744, 'MARCO SOTO', 'soto.marks@yahoo.com', '$2y$10$R.6hzxF4fn.kWZIQ1SUeveRfuAS/vxwkytsUI7LL22KwGVMfS.cya', 'FNgGK0vp4mGvApFMJBR5417lzQ2qXUWkFrTNzvNetl70W0sNLasOZlhJclCh', '2020-03-03 16:22:14', '2021-10-06 14:45:16', ''),
(19, 2, 3890, 'SONIA  PAREDES', 'arichavezr2002@gmail.com', '$2y$10$aaC5uiLFcUCBZVyJZqUxteHZnIsjaJlkdmlBFXyOKB5O/2PvKjrfK', 'gkooX6DeWEmu6BI3X2lBWKsvRU0Qq25Y2dK4WdHTyYPdw1SADYzZ4Zq3GdRw', '2020-03-09 16:36:13', '2024-05-31 20:03:59', ''),
(20, 2, 4077, 'ELIEL CHAVEZ', 'elielchavez823@gmail.com', '$2y$10$Kxhx9P9AJkzUKj6MZrkELuxWZIzDugbojfG2pT0jkGEexzbAZyHS6', 'CD8tZILpnRu4amGQG3wpdYHllghkzUT14roOm7gvg1guGcxuGdYn8nM0I8Sq', '2020-05-09 17:28:52', '2026-05-06 23:34:55', ''),
(21, 2, 5714, 'GEOVANNY VILLARROEL', 'jorgevilla5@hotmail.com', '$2y$10$kZIzI1bE9t0XXTewhtDXZOEVICAyuOcUgVJ9xh84B9uOK.Ab1a7Vu', NULL, '2020-09-07 23:55:21', '2020-11-27 16:37:24', ''),
(22, 2, 6104, 'SHIRLEY GUERRERO', 'shirguerrero26@gmail.com', '$2y$10$Vjv1qvyOLPZMiLjpvrNZvePHU06JFUp3C3PXchWel4IrkI4WgAuXW', NULL, '2020-10-17 19:49:21', '2020-11-27 16:36:58', ''),
(23, 2, 6616, 'LORENA ROSALES', 'lorsarai@hotmail.com', '$2y$10$Trfc34waVBKXQ2JCAuGEKOZilk.Dj5.MmoFJsWskFkCPngNH7.BF2', NULL, '2021-01-09 01:19:35', '2021-10-06 19:08:23', ''),
(24, 2, 7433, 'HILDA ROJAS', 'hildarojas94@hotmail.com', '$2y$10$8lAco8YUn8n277F768/1uO.M4svLj7Q3U5yBMcQFBCqDHnPPlOJqu', NULL, '2021-06-21 21:18:34', '2021-06-21 21:18:34', ''),
(25, 2, 7434, 'KHATY ALLO', 'kattayo@outlook.es', '$2y$10$WcDO/Dl5hgTex/7A4YwDTO7umSs.fYdDN78/UElwF2H1wb1gDqi1u', NULL, '2021-06-21 21:28:47', '2021-10-07 21:22:22', ''),
(26, 2, 8308, 'MAYKOL MELENDREZ', 'maicolmelendrez732@gmail.com', '$2y$10$JxOl7UeSVO1ro8k4f08XHuBNQlByM/rTpBAbMenAwqmIcd2gJjaH6', NULL, '2022-01-07 21:10:23', '2022-01-07 23:33:26', ''),
(27, 2, 8433, 'ESTEFANIA ROSALES', 'tefyr888@gmail.com', '$2y$10$Ta9dlxqyQN3/sE9L.PxgkOqZA8LqiZpg9iciAjMBdEIfB7s4I59f.', NULL, '2022-02-15 18:09:00', '2022-02-15 18:09:00', ''),
(28, 2, 8509, 'JUDITH ROJAS', 'judyrojass@hotail.com', '$2y$10$bmTWf1BaKPbC5hYN6gdm4O6RPx0UitSaM8whOAQsXdulh5KkNoUCy', NULL, '2022-03-09 23:56:07', '2022-03-10 00:04:43', ''),
(29, 2, 8681, 'CRISTINA GARCIA', 'crismilu583@gmail.com', '$2y$10$rRBMXzNn1nrY9wXgGHrTlO2U0I07CukPnEb3Eur4HErYsQjIdXbIO', NULL, '2022-05-03 16:59:15', '2022-05-03 16:59:15', ''),
(30, 2, 9053, 'SUSANA AGUIRRE', 'asusy2616@hotmail.com', '$2y$10$bIP.ZE4t7f0otDEsgntCiuJbLN4MtI7X1vqMoQmrsnWUwQj8xaffW', NULL, '2022-08-22 21:38:05', '2022-08-22 21:45:52', ''),
(31, 2, 9273, 'IRIS AVILES', 'iirisaviles@gmail.com', '$2y$10$UHTzGogY/UU0L10I4f1.mODF85ERaF2aiqCY7puZJ8v/DY/fLUlR6', NULL, '2022-10-29 18:40:40', '2022-10-29 18:40:40', ''),
(32, 2, 9848, 'NELSON ROSALES', 'nrosales-v@hotmail.com', '$2y$10$sSeKy55AxmWzzzO4hK6v2e/RzU3kuezUkW32rUlEXVmaw93fmGCQ6', NULL, '2023-06-15 15:58:04', '2023-06-15 15:58:04', ''),
(33, 2, 9999, 'EVELYN ROSADO', 'rosadoevelin70@gmail.com', '$2y$10$oj8IjbTuIQO6t5gGp9dAAOfbdoXzikuqHwvC6anYx6yFeJStwG5tO', NULL, '2023-08-08 15:50:29', '2023-08-08 15:50:29', ''),
(34, 2, 10396, 'EDUARDA ROSALES', 'eduardarosales29@gmail.com', '$2y$10$aTR/80rXQ5SRs/nEmbGGAee03X9ILTEStLH8lxWYY6gyrg2OG8T2G', NULL, '2023-11-29 21:26:23', '2023-11-29 21:26:23', ''),
(35, 1, 10932, 'ERNESTO BOLAÑOS', 'ernesb@gmail.com', '$2y$10$K1PknjNHVkAhqJgUC38TaOX3s.z8KdYwme3ODIgPoVYVy83QhvJ0i', NULL, '2024-05-31 15:44:12', '2024-05-31 15:44:12', ''),
(36, 2, 11057, 'ADRIANA NARVAEZ', 'adriana_narvaez@hotmail.es', '$2y$10$NBhAVu8jPhqpuWGJKGcQaunm27a.reA4yZRRKD5u13xGZQmPZ2BGm', NULL, '2024-07-15 14:59:25', '2024-07-15 14:59:25', ''),
(37, 2, 504, 'JOSE LUIS OCAÑA', 'joslouistour77@hotmail.com', '$2y$10$2u2v7AMV7k/Iz9UFcln.a.rQAhqONwicRWgs.KdNAXR5i12bKokDC', NULL, '2024-08-01 21:40:37', '2024-08-01 21:40:37', ''),
(38, 2, 11275, 'SILVANA ELIZABETH ROSO BARRETO', 'eliroso15@gmail.com', '$2y$10$7G.c.i/Pwea0uREpxDrgSOrl99kOCAYyOdWPoLWVjKM6SKiETxL2q', NULL, '2024-09-02 16:59:46', '2024-09-02 16:59:46', ''),
(39, 2, 11319, 'JOHANA MERA', 'lucianaduran1803@gmail.com', '$2y$10$jj3rzpuPesko20O10TEZWePgj27ataWq98OvktJxyUFbHekQUZjt2', NULL, '2024-09-10 22:11:31', '2024-09-10 22:11:31', ''),
(40, 2, 11494, 'LIZBETH QUIMBA', 'lizbeth.quimba1466@utc.edu.ec', '$2y$10$ribbaIaGYSJAMnXJivU0f.ul31al/TxfGLNX.I4uVNvW1ziaUib/O', NULL, '2024-10-08 21:52:02', '2026-05-29 02:25:16', ''),
(41, 2, 11495, 'JENIFER PILAGUANO', 'jenifer.pilaguano.cris@gmail.com', '$2y$10$0bb4C3u8nNHpmHL6raYHMueH5h57PmXcCXiYX7cF7dBDbrplgJAWS', NULL, '2024-10-08 21:56:11', '2025-10-27 18:44:15', ''),
(42, 2, 11612, 'NATHALY PARRA', 'nathyparra20@gmail.com', '$2y$10$yQ0MCcDL07jPoBgx82973eK8TlL86491AmmY2VWJUNeXKDisoD1X6', NULL, '2024-11-08 18:12:36', '2024-11-08 18:12:36', ''),
(43, 0, 0, 'adminxps', 'budaklzcrew@gmail.com', '$2y$10$2X7FgfBHEp17UBmIkYqER.pbaQ9yQbcVKH0os1O/rI22OKVYnXOFm', NULL, '2025-02-20 15:34:35', '2025-02-20 15:34:35', ''),
(44, 0, 0, 'adminxp', 'adminxp@gmail.com', '$2y$10$aE0UiUEZVb23KnBsOSgJeO/z0d9oR/dCq8ETwSnmlGhsnMPW66qcG', NULL, '2025-09-17 05:27:00', '2025-09-17 05:27:00', ''),
(45, 0, 0, 'adminexp', 'adminexp@gmail.com', '$2y$10$4fzgjy.55jI7qfiDY.v2J.9gMp5dnu1Qqs0ln.cUdzGy955FBlV52', NULL, '2025-10-03 10:05:30', '2025-10-03 10:05:30', ''),
(46, 0, 0, 'adminxx', 'adminxx@gmail.com', '$2y$10$zsTSGNJuP18HTYM0dzBU0e2xW2maUrHP7ysBtiO/TSJvN7/6tcbkm', NULL, '2025-10-15 14:45:17', '2025-10-15 14:45:17', ''),
(47, 2, 12772, 'MELISA GARAVITO', 'melisagaravitovelasquez@gmail.com', '$2y$10$kwP8V8J51PEjDbjXW4gn0uCoAGsGR0wJxjmFfGXwjPQTQvsJE9Wja', NULL, '2025-11-06 00:35:25', '2025-11-06 00:35:25', ''),
(48, 1, 13793, 'YOMAYRA ELISABETH PAEZ RUIZ', 'yomipaez_2021@hotmail.com', '$2y$10$ya/87Kc/YKkLhknKKaZOpO6afR/YDyFFNhe3ubMWGICrryDIQMS8m', NULL, '2026-10-28 15:16:20', '2026-10-28 15:16:20', ''),
(49, 0, 0, 'adminxps', 'budaklzcrew@proton.me', '$2y$10$NolxChwZ67FBJqlJZViICuUQs81cZ43qJCckFFaPLehvOs7449zg2', NULL, '2026-11-10 11:40:33', '2026-11-10 11:40:33', '');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
