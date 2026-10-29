-- BACKUP DE BASE DE DATOS: reca
-- Fecha: 2026-10-29 18:05:58

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- Estructura de la tabla `asignaciones_auto`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `asignaciones_auto`;
CREATE TABLE `asignaciones_auto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chofer_id` int(11) NOT NULL,
  `vehiculo_id` int(11) NOT NULL,
  `turno` varchar(10) NOT NULL DEFAULT 'DIA',
  `fecha_desde` date NOT NULL,
  `fecha_hasta` date DEFAULT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `observaciones` varchar(255) DEFAULT '',
  `asignado_por` varchar(100) DEFAULT NULL,
  `asignado_fecha` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_chofer` (`chofer_id`),
  KEY `idx_vehiculo` (`vehiculo_id`),
  KEY `idx_activa` (`activa`),
  KEY `idx_vehiculo_turno_activa` (`vehiculo_id`,`turno`,`activa`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4;

-- Volcado de datos para la tabla `asignaciones_auto`
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`, `asignado_por`, `asignado_fecha`) VALUES ('1', '1', '5', 'DIA', '2026-10-15', '2026-10-01', '0', '', NULL, NULL);
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`, `asignado_por`, `asignado_fecha`) VALUES ('2', '2', '10', 'DIA', '2026-10-15', '2026-10-19', '0', '', NULL, NULL);
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`, `asignado_por`, `asignado_fecha`) VALUES ('3', '3', '8', 'DIA', '2026-10-15', '2026-10-19', '0', '', NULL, NULL);
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`, `asignado_por`, `asignado_fecha`) VALUES ('4', '1', '2', 'DIA', '2026-10-01', '2026-10-27', '0', 'EMPEZO MES CON NUEVO AUTO', NULL, NULL);
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`, `asignado_por`, `asignado_fecha`) VALUES ('5', '3', '2', 'NOCHE', '2026-10-19', '2026-10-19', '0', 'EMPEZO CON NUEVO AUTO', NULL, NULL);
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`, `asignado_por`, `asignado_fecha`) VALUES ('6', '12', '12', 'NOCHE', '2026-10-19', NULL, '1', 'NUEVO AUTO', NULL, NULL);
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`, `asignado_por`, `asignado_fecha`) VALUES ('7', '10', '10', 'DIA', '2026-10-19', NULL, '1', 'NUEVO AUTO', NULL, NULL);
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`, `asignado_por`, `asignado_fecha`) VALUES ('8', '9', '9', 'DIA', '2026-10-19', NULL, '1', 'NUEVO AUTO', NULL, NULL);
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`, `asignado_por`, `asignado_fecha`) VALUES ('9', '8', '7', 'DIA', '2026-10-19', NULL, '1', 'NUEVO AUTO', NULL, NULL);
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`, `asignado_por`, `asignado_fecha`) VALUES ('10', '7', '6', 'DIA', '2026-10-19', '2026-10-27', '0', 'NUEVO AUTO', NULL, NULL);
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`, `asignado_por`, `asignado_fecha`) VALUES ('11', '6', '5', 'DIA', '2026-10-19', NULL, '1', 'NUEVO AUTO', NULL, NULL);
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`, `asignado_por`, `asignado_fecha`) VALUES ('12', '5', '4', 'DIA', '2026-10-19', '2026-10-19', '0', 'NUEVO AUTO', NULL, NULL);
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`, `asignado_por`, `asignado_fecha`) VALUES ('13', '2', '4', 'NOCHE', '2026-10-19', '2026-10-19', '0', 'NUEVO AUTO', NULL, NULL);
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`, `asignado_por`, `asignado_fecha`) VALUES ('14', '4', '3', 'DIA', '2026-10-19', NULL, '1', 'NUEVO AUTO', NULL, NULL);
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`, `asignado_por`, `asignado_fecha`) VALUES ('15', '13', '3', 'NOCHE', '2026-10-19', NULL, '1', 'NUEVO AUTO', NULL, NULL);
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`, `asignado_por`, `asignado_fecha`) VALUES ('16', '11', '11', 'DIA', '2026-10-19', NULL, '1', 'NUEVO AUTO', NULL, NULL);
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`, `asignado_por`, `asignado_fecha`) VALUES ('17', '3', '4', 'NOCHE', '2026-10-19', NULL, '1', 'CAMBIO DE UTO PORQUE EL DEL TURNO DIA LLEGO TARDE', NULL, NULL);
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`, `asignado_por`, `asignado_fecha`) VALUES ('18', '2', '2', 'NOCHE', '2026-10-19', NULL, '1', 'CAMBIO DE AUTO PORQUE DUARTE LLEGO TARDE', NULL, NULL);
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`, `asignado_por`, `asignado_fecha`) VALUES ('19', '5', '4', 'DIA', '2026-10-21', NULL, '1', 'nuevo auto', NULL, NULL);
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`, `asignado_por`, `asignado_fecha`) VALUES ('20', '14', '2', 'DIA', '2026-10-27', NULL, '1', '', NULL, NULL);
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`, `asignado_por`, `asignado_fecha`) VALUES ('21', '15', '1', 'DIA', '2026-10-27', '2026-10-27', '0', '', NULL, NULL);
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`, `asignado_por`, `asignado_fecha`) VALUES ('22', '7', '6', 'NOCHE', '2026-10-27', '2026-10-27', '0', 'si', NULL, NULL);
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`, `asignado_por`, `asignado_fecha`) VALUES ('23', '7', '6', 'DIA', '2026-10-27', '2026-10-27', '0', 'si', 'Fabian Nogueroles', '2026-10-27 15:57:42');
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`, `asignado_por`, `asignado_fecha`) VALUES ('24', '7', '6', 'NOCHE', '2026-10-27', NULL, '1', 'a', 'Fabian Nogueroles', '2026-10-27 16:06:26');
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`, `asignado_por`, `asignado_fecha`) VALUES ('25', '1', '1', 'DIA', '2026-10-28', NULL, '1', 'nuevo', 'Fabian Nogueroles', '2026-10-28 14:14:38');
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`, `asignado_por`, `asignado_fecha`) VALUES ('26', '17', '28', 'DIA', '2026-10-29', '2026-10-29', '0', '', NULL, NULL);
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`, `asignado_por`, `asignado_fecha`) VALUES ('27', '17', '13', 'DIA', '2026-10-29', NULL, '1', 'nuevo chofer', 'Fabian Nogueroles', '2026-10-29 18:01:58');

-- --------------------------------------------------------
-- Estructura de la tabla `caja_cierres_mensuales`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `caja_cierres_mensuales`;
CREATE TABLE `caja_cierres_mensuales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mes_cerrado` varchar(7) NOT NULL,
  `saldo_mp` decimal(10,2) NOT NULL DEFAULT 0.00,
  `saldo_ft` decimal(10,2) NOT NULL DEFAULT 0.00,
  `deuda_total_chofers` decimal(10,2) NOT NULL DEFAULT 0.00,
  `a_favor_total_chofers` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cant_choferes_deuda` int(11) NOT NULL DEFAULT 0,
  `creado` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_mes` (`mes_cerrado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Estructura de la tabla `caja_movimientos`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `caja_movimientos`;
CREATE TABLE `caja_movimientos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `tipo` enum('INGRESO','EXTRACCION') NOT NULL,
  `medio` enum('MP','FT') NOT NULL,
  `monto` decimal(10,2) NOT NULL DEFAULT 0.00,
  `descripcion` varchar(255) DEFAULT '',
  `creado` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_fecha` (`fecha`),
  KEY `idx_medio` (`medio`),
  KEY `idx_tipo` (`tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Estructura de la tabla `choferes`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `choferes`;
CREATE TABLE `choferes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `cel` varchar(15) NOT NULL DEFAULT '',
  `email` varchar(40) NOT NULL DEFAULT '',
  `barrio` varchar(50) NOT NULL DEFAULT '',
  `dir` varchar(50) NOT NULL DEFAULT '',
  `cp` varchar(5) NOT NULL DEFAULT '',
  `foto` varchar(50) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_alta` date NOT NULL,
  `creado_por` varchar(100) DEFAULT NULL,
  `creado_fecha` datetime DEFAULT NULL,
  `eliminado` tinyint(1) NOT NULL DEFAULT 0,
  `eliminado_por` varchar(100) DEFAULT NULL,
  `eliminado_fecha` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4;

-- Volcado de datos para la tabla `choferes`
INSERT INTO `choferes` (`id`, `nombre`, `cel`, `email`, `barrio`, `dir`, `cp`, `foto`, `activo`, `fecha_alta`, `creado_por`, `creado_fecha`, `eliminado`, `eliminado_por`, `eliminado_fecha`) VALUES ('1', 'DUARTE', '1156238956', 'juan@gmail.com', 'FLORES', 'OLIDEN 258', '', 'chofer_6ad0e26374f3f_rostro-1.webp', '1', '2026-10-15', NULL, NULL, '0', NULL, NULL);
INSERT INTO `choferes` (`id`, `nombre`, `cel`, `email`, `barrio`, `dir`, `cp`, `foto`, `activo`, `fecha_alta`, `creado_por`, `creado_fecha`, `eliminado`, `eliminado_por`, `eliminado_fecha`) VALUES ('2', 'SHMIDT', '1136525896', 'ELSAPATO@GMAIL.COM', 'Belgrano', 'JURAMENTO 2589', '', 'chofer_6ad0e2a27d01f_rosto.webp', '1', '2026-10-15', NULL, NULL, '0', NULL, NULL);
INSERT INTO `choferes` (`id`, `nombre`, `cel`, `email`, `barrio`, `dir`, `cp`, `foto`, `activo`, `fecha_alta`, `creado_por`, `creado_fecha`, `eliminado`, `eliminado_por`, `eliminado_fecha`) VALUES ('3', 'JADUR', '1111111111', 'robertolopez@gmail.com', 'PAlermo Holliwood', 'Niceto Vega 1500', '1450', 'chofer_6ad0e3f590d69_CARA DE PERRO.jpg', '1', '2026-10-15', NULL, NULL, '0', NULL, NULL);
INSERT INTO `choferes` (`id`, `nombre`, `cel`, `email`, `barrio`, `dir`, `cp`, `foto`, `activo`, `fecha_alta`, `creado_por`, `creado_fecha`, `eliminado`, `eliminado_por`, `eliminado_fecha`) VALUES ('4', 'GOMEZ IVAN', '22222222', 'GOMEZ@GMAIL.COM', '', '', '', NULL, '1', '2026-10-19', NULL, NULL, '0', NULL, NULL);
INSERT INTO `choferes` (`id`, `nombre`, `cel`, `email`, `barrio`, `dir`, `cp`, `foto`, `activo`, `fecha_alta`, `creado_por`, `creado_fecha`, `eliminado`, `eliminado_por`, `eliminado_fecha`) VALUES ('5', 'CALLEGARI', '1133333333', 'CALEGARI@GMAIL.COM', '', '', '', NULL, '1', '2026-10-19', NULL, NULL, '0', NULL, NULL);
INSERT INTO `choferes` (`id`, `nombre`, `cel`, `email`, `barrio`, `dir`, `cp`, `foto`, `activo`, `fecha_alta`, `creado_por`, `creado_fecha`, `eliminado`, `eliminado_por`, `eliminado_fecha`) VALUES ('6', 'IGLESIAS', '1144444444', '', '', '', '', NULL, '1', '2026-10-19', NULL, NULL, '0', NULL, NULL);
INSERT INTO `choferes` (`id`, `nombre`, `cel`, `email`, `barrio`, `dir`, `cp`, `foto`, `activo`, `fecha_alta`, `creado_por`, `creado_fecha`, `eliminado`, `eliminado_por`, `eliminado_fecha`) VALUES ('7', 'GASTON', '1155555555', 'GASTON@GMAIL.COM', '', '', '', NULL, '1', '2026-10-19', NULL, NULL, '0', NULL, NULL);
INSERT INTO `choferes` (`id`, `nombre`, `cel`, `email`, `barrio`, `dir`, `cp`, `foto`, `activo`, `fecha_alta`, `creado_por`, `creado_fecha`, `eliminado`, `eliminado_por`, `eliminado_fecha`) VALUES ('8', 'MAGLIANO', '1166666666', 'MAGLIANO@GMAIL.COM', '', '', '', NULL, '1', '2026-10-19', NULL, NULL, '0', NULL, NULL);
INSERT INTO `choferes` (`id`, `nombre`, `cel`, `email`, `barrio`, `dir`, `cp`, `foto`, `activo`, `fecha_alta`, `creado_por`, `creado_fecha`, `eliminado`, `eliminado_por`, `eliminado_fecha`) VALUES ('9', 'ODDI', '1177777777', 'ODDI@GMAIL.COM', '', '', '', NULL, '1', '2026-10-19', NULL, NULL, '0', NULL, NULL);
INSERT INTO `choferes` (`id`, `nombre`, `cel`, `email`, `barrio`, `dir`, `cp`, `foto`, `activo`, `fecha_alta`, `creado_por`, `creado_fecha`, `eliminado`, `eliminado_por`, `eliminado_fecha`) VALUES ('10', 'BARRIONUEVO', '1188888888', 'BARRIONUEVO@GMAIL.COM', '', '', '', NULL, '1', '2026-10-19', NULL, NULL, '0', NULL, NULL);
INSERT INTO `choferes` (`id`, `nombre`, `cel`, `email`, `barrio`, `dir`, `cp`, `foto`, `activo`, `fecha_alta`, `creado_por`, `creado_fecha`, `eliminado`, `eliminado_por`, `eliminado_fecha`) VALUES ('11', 'ZURITA', '1199999999', 'ZURITA@GMAIL.COM', '', '', '', NULL, '1', '2026-10-19', NULL, NULL, '0', NULL, NULL);
INSERT INTO `choferes` (`id`, `nombre`, `cel`, `email`, `barrio`, `dir`, `cp`, `foto`, `activo`, `fecha_alta`, `creado_por`, `creado_fecha`, `eliminado`, `eliminado_por`, `eliminado_fecha`) VALUES ('12', 'MASTRANGELO', '1100000000', 'MASTRANGELO@GMAIL.COM', '', '', '', NULL, '1', '2026-10-19', NULL, NULL, '0', NULL, NULL);
INSERT INTO `choferes` (`id`, `nombre`, `cel`, `email`, `barrio`, `dir`, `cp`, `foto`, `activo`, `fecha_alta`, `creado_por`, `creado_fecha`, `eliminado`, `eliminado_por`, `eliminado_fecha`) VALUES ('13', 'OCHOA', '1100001111', '8A@GMAIL.COM', '', '', '', NULL, '1', '2026-10-19', NULL, NULL, '0', NULL, NULL);
INSERT INTO `choferes` (`id`, `nombre`, `cel`, `email`, `barrio`, `dir`, `cp`, `foto`, `activo`, `fecha_alta`, `creado_por`, `creado_fecha`, `eliminado`, `eliminado_por`, `eliminado_fecha`) VALUES ('14', 'BRAVO', '1122222222', 'BRAVO@GMAIL.COM', 'FLORES', 'LAFUENTE14000', '', NULL, '1', '2026-10-27', NULL, NULL, '0', NULL, NULL);
INSERT INTO `choferes` (`id`, `nombre`, `cel`, `email`, `barrio`, `dir`, `cp`, `foto`, `activo`, `fecha_alta`, `creado_por`, `creado_fecha`, `eliminado`, `eliminado_por`, `eliminado_fecha`) VALUES ('15', 'JUANSE', '1156234512', 'juanse@gmail.com', 'belgrano', 'Cabildo 4512', '', NULL, '0', '2026-10-27', 'Fabian Nogueroles', '2026-10-27 15:19:51', '1', 'Fabian Nogueroles', '2026-10-27 15:51:51');
INSERT INTO `choferes` (`id`, `nombre`, `cel`, `email`, `barrio`, `dir`, `cp`, `foto`, `activo`, `fecha_alta`, `creado_por`, `creado_fecha`, `eliminado`, `eliminado_por`, `eliminado_fecha`) VALUES ('16', 'PAPPO', '1122225555', 'papo@gmail.com', 'caballito', 'french 2025', '', NULL, '0', '2026-10-27', 'Fabian Nogueroles', '2026-10-27 15:23:08', '1', 'Fabian Nogueroles', '2026-10-27 15:31:14');
INSERT INTO `choferes` (`id`, `nombre`, `cel`, `email`, `barrio`, `dir`, `cp`, `foto`, `activo`, `fecha_alta`, `creado_por`, `creado_fecha`, `eliminado`, `eliminado_por`, `eliminado_fecha`) VALUES ('17', 'ANA KARENINA', '1125632541', 'ana@gmail.com', 'Belgrano', 'JURAMENTO 2589', '', 'chofer_6ae37b28a282e_nujer.webp', '1', '2026-10-29', 'Fabian Nogueroles', '2026-10-29 17:58:16', '0', NULL, NULL);

-- --------------------------------------------------------
-- Estructura de la tabla `cuenta_corriente`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `cuenta_corriente`;
CREATE TABLE `cuenta_corriente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chofer_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `tipo` varchar(20) NOT NULL DEFAULT 'DEUDA',
  `descripcion` varchar(255) DEFAULT '',
  `recauda_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_chofer` (`chofer_id`),
  KEY `idx_fecha` (`fecha`),
  KEY `idx_tipo` (`tipo`)
) ENGINE=InnoDB AUTO_INCREMENT=173 DEFAULT CHARSET=utf8mb4;

-- Volcado de datos para la tabla `cuenta_corriente`
INSERT INTO `cuenta_corriente` (`id`, `chofer_id`, `fecha`, `monto`, `tipo`, `descripcion`, `recauda_id`) VALUES ('156', '1', '2026-10-05', '-67500.00', 'DEUDA', 'Quedó debiendo del turno DIA del 2026-10-05', '446');
INSERT INTO `cuenta_corriente` (`id`, `chofer_id`, `fecha`, `monto`, `tipo`, `descripcion`, `recauda_id`) VALUES ('158', '4', '2026-10-27', '2500.00', 'A CUENTA', 'A cuenta del turno DIA del 2026-10-27', '464');
INSERT INTO `cuenta_corriente` (`id`, `chofer_id`, `fecha`, `monto`, `tipo`, `descripcion`, `recauda_id`) VALUES ('159', '4', '2026-10-27', '-2500.00', 'DEUDA', 'Pago de deuda con saldo a favor', NULL);
INSERT INTO `cuenta_corriente` (`id`, `chofer_id`, `fecha`, `monto`, `tipo`, `descripcion`, `recauda_id`) VALUES ('160', '6', '2026-10-27', '500.00', 'A CUENTA', 'A cuenta del turno DIA del 2026-10-27', '467');
INSERT INTO `cuenta_corriente` (`id`, `chofer_id`, `fecha`, `monto`, `tipo`, `descripcion`, `recauda_id`) VALUES ('161', '6', '2026-10-27', '-500.00', 'DEUDA', 'Pago de deuda con saldo a favor', NULL);
INSERT INTO `cuenta_corriente` (`id`, `chofer_id`, `fecha`, `monto`, `tipo`, `descripcion`, `recauda_id`) VALUES ('163', '8', '2026-10-27', '-20000.00', 'DEUDA', 'Pago de deuda con saldo a favor', NULL);
INSERT INTO `cuenta_corriente` (`id`, `chofer_id`, `fecha`, `monto`, `tipo`, `descripcion`, `recauda_id`) VALUES ('164', '4', '2026-10-26', '2500.00', 'A CUENTA', 'A cuenta del turno DIA del 2026-10-26', '472');
INSERT INTO `cuenta_corriente` (`id`, `chofer_id`, `fecha`, `monto`, `tipo`, `descripcion`, `recauda_id`) VALUES ('165', '4', '2026-10-27', '-2500.00', 'DEUDA', 'Pago de deuda con saldo a favor', NULL);
INSERT INTO `cuenta_corriente` (`id`, `chofer_id`, `fecha`, `monto`, `tipo`, `descripcion`, `recauda_id`) VALUES ('167', '4', '2026-10-27', '-2500.00', 'DEUDA', 'Pago de deuda con saldo a favor', NULL);
INSERT INTO `cuenta_corriente` (`id`, `chofer_id`, `fecha`, `monto`, `tipo`, `descripcion`, `recauda_id`) VALUES ('168', '8', '2026-10-26', '20000.00', 'A CUENTA', 'A cuenta del turno DIA del 2026-10-26', '476');
INSERT INTO `cuenta_corriente` (`id`, `chofer_id`, `fecha`, `monto`, `tipo`, `descripcion`, `recauda_id`) VALUES ('169', '8', '2026-10-27', '-20000.00', 'DEUDA', 'Pago de deuda con saldo a favor', NULL);

-- --------------------------------------------------------
-- Estructura de la tabla `observaciones`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `observaciones`;
CREATE TABLE `observaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chofer_id` int(11) NOT NULL,
  `fecha_creacion` date NOT NULL,
  `observacion` text NOT NULL,
  `creado` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_chofer` (`chofer_id`),
  KEY `idx_fecha` (`fecha_creacion`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Estructura de la tabla `prestamos`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `prestamos`;
CREATE TABLE `prestamos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chofer_id` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `interes` decimal(5,2) DEFAULT 0.00,
  `monto_total` decimal(10,2) NOT NULL,
  `cuotas` int(11) NOT NULL DEFAULT 1,
  `valor_cuota` decimal(10,2) NOT NULL,
  `cuotas_pagas` int(11) DEFAULT 0,
  `fecha` date NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `estado` enum('ACTIVO','PAGADO','CANCELADO','ARCHIVADO') DEFAULT 'ACTIVO',
  `estado_mora` enum('AL DIA','EN MORA') DEFAULT 'AL DIA',
  `creado` date NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_chofer` (`chofer_id`),
  KEY `idx_fecha` (`fecha`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4;

-- Volcado de datos para la tabla `prestamos`
INSERT INTO `prestamos` (`id`, `chofer_id`, `monto`, `interes`, `monto_total`, `cuotas`, `valor_cuota`, `cuotas_pagas`, `fecha`, `descripcion`, `observaciones`, `estado`, `estado_mora`, `creado`) VALUES ('9', '1', '577000.00', '0.00', '577000.00', '1', '577000.00', '1', '2026-10-01', '', '', 'ARCHIVADO', 'AL DIA', '2026-10-26');
INSERT INTO `prestamos` (`id`, `chofer_id`, `monto`, `interes`, `monto_total`, `cuotas`, `valor_cuota`, `cuotas_pagas`, `fecha`, `descripcion`, `observaciones`, `estado`, `estado_mora`, `creado`) VALUES ('10', '4', '16500.00', '0.00', '16500.00', '1', '16500.00', '0', '2026-10-01', '', '', 'ACTIVO', 'AL DIA', '2026-10-26');
INSERT INTO `prestamos` (`id`, `chofer_id`, `monto`, `interes`, `monto_total`, `cuotas`, `valor_cuota`, `cuotas_pagas`, `fecha`, `descripcion`, `observaciones`, `estado`, `estado_mora`, `creado`) VALUES ('11', '6', '6750.00', '0.00', '6750.00', '1', '6750.00', '0', '2026-10-01', '', '', 'ACTIVO', 'AL DIA', '2026-10-26');
INSERT INTO `prestamos` (`id`, `chofer_id`, `monto`, `interes`, `monto_total`, `cuotas`, `valor_cuota`, `cuotas_pagas`, `fecha`, `descripcion`, `observaciones`, `estado`, `estado_mora`, `creado`) VALUES ('12', '8', '230000.00', '0.00', '230000.00', '1', '230000.00', '0', '2026-10-01', '', '', 'ACTIVO', 'AL DIA', '2026-10-26');
INSERT INTO `prestamos` (`id`, `chofer_id`, `monto`, `interes`, `monto_total`, `cuotas`, `valor_cuota`, `cuotas_pagas`, `fecha`, `descripcion`, `observaciones`, `estado`, `estado_mora`, `creado`) VALUES ('13', '10', '242000.00', '0.00', '242000.00', '1', '242000.00', '0', '2026-10-01', '', '', 'ACTIVO', 'AL DIA', '2026-10-26');
INSERT INTO `prestamos` (`id`, `chofer_id`, `monto`, `interes`, `monto_total`, `cuotas`, `valor_cuota`, `cuotas_pagas`, `fecha`, `descripcion`, `observaciones`, `estado`, `estado_mora`, `creado`) VALUES ('14', '11', '229000.00', '0.00', '229000.00', '1', '229000.00', '0', '2026-10-01', '', '', 'ACTIVO', 'AL DIA', '2026-10-26');
INSERT INTO `prestamos` (`id`, `chofer_id`, `monto`, `interes`, `monto_total`, `cuotas`, `valor_cuota`, `cuotas_pagas`, `fecha`, `descripcion`, `observaciones`, `estado`, `estado_mora`, `creado`) VALUES ('15', '14', '100000.00', '0.00', '100000.00', '1', '100000.00', '1', '2026-10-28', '', '', 'ACTIVO', 'AL DIA', '2026-10-28');

-- --------------------------------------------------------
-- Estructura de la tabla `prestamos_pagos`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `prestamos_pagos`;
CREATE TABLE `prestamos_pagos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prestamo_id` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_pago` date NOT NULL,
  `creado` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_prestamo` (`prestamo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4;

-- Volcado de datos para la tabla `prestamos_pagos`
INSERT INTO `prestamos_pagos` (`id`, `prestamo_id`, `monto`, `fecha_pago`, `creado`) VALUES ('28', '9', '577000.00', '2026-10-27', '2026-10-27 00:23:33');
INSERT INTO `prestamos_pagos` (`id`, `prestamo_id`, `monto`, `fecha_pago`, `creado`) VALUES ('29', '10', '2500.00', '2026-10-27', '2026-10-27 05:54:38');
INSERT INTO `prestamos_pagos` (`id`, `prestamo_id`, `monto`, `fecha_pago`, `creado`) VALUES ('30', '11', '500.00', '2026-10-27', '2026-10-27 05:56:37');
INSERT INTO `prestamos_pagos` (`id`, `prestamo_id`, `monto`, `fecha_pago`, `creado`) VALUES ('31', '12', '20000.00', '2026-10-27', '2026-10-27 05:57:47');
INSERT INTO `prestamos_pagos` (`id`, `prestamo_id`, `monto`, `fecha_pago`, `creado`) VALUES ('32', '10', '2500.00', '2026-10-27', '2026-10-27 06:02:53');
INSERT INTO `prestamos_pagos` (`id`, `prestamo_id`, `monto`, `fecha_pago`, `creado`) VALUES ('33', '10', '2500.00', '2026-10-27', '2026-10-27 06:03:51');
INSERT INTO `prestamos_pagos` (`id`, `prestamo_id`, `monto`, `fecha_pago`, `creado`) VALUES ('34', '12', '20000.00', '2026-10-27', '2026-10-27 06:06:31');
INSERT INTO `prestamos_pagos` (`id`, `prestamo_id`, `monto`, `fecha_pago`, `creado`) VALUES ('35', '15', '50000.00', '2026-10-28', '2026-10-28 03:22:44');
INSERT INTO `prestamos_pagos` (`id`, `prestamo_id`, `monto`, `fecha_pago`, `creado`) VALUES ('36', '15', '10000.00', '2026-10-28', '2026-10-28 04:22:47');

-- --------------------------------------------------------
-- Estructura de la tabla `recauda`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `recauda`;
CREATE TABLE `recauda` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `chofer_id` int(11) NOT NULL,
  `vehiculo_id` int(11) DEFAULT NULL,
  `movil` int(11) NOT NULL,
  `turno` varchar(6) NOT NULL,
  `abono_diario` decimal(10,2) NOT NULL DEFAULT 0.00,
  `paga` decimal(10,2) NOT NULL DEFAULT 0.00,
  `porcent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `comision` decimal(10,2) NOT NULL DEFAULT 0.00,
  `voucher` decimal(10,2) NOT NULL DEFAULT 0.00,
  `mp` decimal(10,2) NOT NULL DEFAULT 0.00,
  `ft` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `debe` decimal(10,2) NOT NULL DEFAULT 0.00,
  `observaciones` varchar(255) DEFAULT '',
  `registrado_por` varchar(100) DEFAULT NULL,
  `registrado_fecha` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fecha_chofer` (`fecha`,`chofer_id`),
  KEY `idx_chofer` (`chofer_id`),
  KEY `idx_fecha` (`fecha`),
  KEY `idx_movil` (`movil`)
) ENGINE=InnoDB AUTO_INCREMENT=493 DEFAULT CHARSET=utf8mb4;

-- Volcado de datos para la tabla `recauda`
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`, `registrado_por`, `registrado_fecha`) VALUES ('443', '2026-10-01', '1', '2', '72', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '0.00', '67500.00', '0.00', '0.00', '', NULL, NULL);
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`, `registrado_por`, `registrado_fecha`) VALUES ('444', '2026-10-02', '1', '2', '72', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '0.00', '67500.00', '0.00', '0.00', '', NULL, NULL);
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`, `registrado_por`, `registrado_fecha`) VALUES ('445', '2026-10-03', '1', '2', '72', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '0.00', '67500.00', '0.00', '0.00', '', NULL, NULL);
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`, `registrado_por`, `registrado_fecha`) VALUES ('446', '2026-10-05', '1', '2', '72', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '0.00', '0.00', '0.00', '67500.00', 'FALTO', NULL, NULL);
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`, `registrado_por`, `registrado_fecha`) VALUES ('447', '2026-10-06', '1', '2', '72', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '50000.00', '17500.00', '0.00', '0.00', '', NULL, NULL);
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`, `registrado_por`, `registrado_fecha`) VALUES ('448', '2026-10-07', '1', '2', '72', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '50000.00', '17500.00', '0.00', '0.00', '', NULL, NULL);
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`, `registrado_por`, `registrado_fecha`) VALUES ('450', '2026-10-08', '1', '2', '72', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '0.00', '67500.00', '0.00', '0.00', '', NULL, NULL);
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`, `registrado_por`, `registrado_fecha`) VALUES ('451', '2026-10-09', '1', '2', '72', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '0.00', '67500.00', '0.00', '0.00', '', NULL, NULL);
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`, `registrado_por`, `registrado_fecha`) VALUES ('452', '2026-10-10', '1', '2', '72', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '0.00', '67500.00', '0.00', '0.00', '', NULL, NULL);
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`, `registrado_por`, `registrado_fecha`) VALUES ('453', '2026-10-11', '1', '2', '72', 'DIA', '0.00', '0.00', '10.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '', NULL, NULL);
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`, `registrado_por`, `registrado_fecha`) VALUES ('454', '2026-10-12', '1', '2', '72', 'DIA', '33750.00', '0.00', '10.00', '0.00', '0.00', '0.00', '33750.00', '0.00', '0.00', '', NULL, NULL);
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`, `registrado_por`, `registrado_fecha`) VALUES ('455', '2026-10-13', '1', '2', '72', 'DIA', '0.00', '0.00', '10.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', 'FALTO', NULL, NULL);
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`, `registrado_por`, `registrado_fecha`) VALUES ('460', '2026-10-14', '1', '2', '72', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '0.00', '67500.00', '0.00', '0.00', '', NULL, NULL);
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`, `registrado_por`, `registrado_fecha`) VALUES ('461', '2026-10-15', '1', '2', '72', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '0.00', '67500.00', '0.00', '0.00', '', NULL, NULL);
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`, `registrado_por`, `registrado_fecha`) VALUES ('462', '2026-10-16', '1', '2', '72', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '17500.00', '50000.00', '0.00', '0.00', '', NULL, NULL);
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`, `registrado_por`, `registrado_fecha`) VALUES ('463', '2026-10-27', '14', '2', '72', 'DIA', '0.00', '0.00', '10.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '', NULL, NULL);
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`, `registrado_por`, `registrado_fecha`) VALUES ('464', '2026-10-27', '4', '3', '94', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '0.00', '70000.00', '0.00', '-2500.00', '', NULL, NULL);
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`, `registrado_por`, `registrado_fecha`) VALUES ('466', '2026-10-27', '5', '4', '95', 'DIA', '60000.00', '0.00', '10.00', '0.00', '0.00', '0.00', '60000.00', '0.00', '0.00', '', NULL, NULL);
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`, `registrado_por`, `registrado_fecha`) VALUES ('467', '2026-10-27', '6', '5', '96', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '36900.00', '31100.00', '0.00', '-500.00', '', NULL, NULL);
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`, `registrado_por`, `registrado_fecha`) VALUES ('468', '2026-10-27', '9', '9', '501', 'DIA', '0.00', '0.00', '10.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', 'falto', NULL, NULL);
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`, `registrado_por`, `registrado_fecha`) VALUES ('469', '2026-10-27', '8', '7', '196', 'DIA', '0.00', '0.00', '10.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '', NULL, NULL);
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`, `registrado_por`, `registrado_fecha`) VALUES ('471', '2026-10-26', '14', '2', '72', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '47000.00', '20500.00', '0.00', '0.00', '', NULL, NULL);
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`, `registrado_por`, `registrado_fecha`) VALUES ('472', '2026-10-26', '4', '3', '94', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '0.00', '70000.00', '0.00', '-2500.00', '', NULL, NULL);
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`, `registrado_por`, `registrado_fecha`) VALUES ('473', '2026-10-26', '2', '2', '72', 'NOCHE', '0.00', '0.00', '10.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '', 'Fabian Nogueroles', '2026-10-28 14:40:07');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`, `registrado_por`, `registrado_fecha`) VALUES ('474', '2026-10-26', '5', '4', '95', 'DIA', '60000.00', '0.00', '10.00', '0.00', '0.00', '0.00', '60000.00', '0.00', '0.00', '', NULL, NULL);
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`, `registrado_por`, `registrado_fecha`) VALUES ('475', '2026-10-26', '6', '5', '96', 'DIA', '68000.00', '0.00', '10.00', '0.00', '0.00', '36900.00', '31100.00', '0.00', '0.00', '', NULL, NULL);
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`, `registrado_por`, `registrado_fecha`) VALUES ('476', '2026-10-26', '8', '7', '196', 'DIA', '40000.00', '0.00', '10.00', '0.00', '0.00', '0.00', '60000.00', '0.00', '-20000.00', '', NULL, NULL);
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`, `registrado_por`, `registrado_fecha`) VALUES ('480', '2026-10-28', '14', '2', '72', 'DIA', '0.00', '0.00', '10.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '', 'Fabian Nogueroles', '2026-10-28 14:39:45');

-- --------------------------------------------------------
-- Estructura de la tabla `usuarios`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre` varchar(100) NOT NULL DEFAULT '',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_usuario` (`usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;

-- Volcado de datos para la tabla `usuarios`
INSERT INTO `usuarios` (`id`, `usuario`, `password`, `nombre`, `activo`, `creado`) VALUES ('2', 'silvia', '$2y$10$UlwdtU7ccs.LRebKKV1nF.Rs3ZmfkhJxF.LnCXyKbLnU49m3RzNbC', 'Silvia Svica', '1', '2026-10-26 03:35:14');
INSERT INTO `usuarios` (`id`, `usuario`, `password`, `nombre`, `activo`, `creado`) VALUES ('3', 'Fabian', '$2y$10$jBSydg9ju534DUieKw4lDuPl4r1PqOt/y7vblnE6QGEKbB3rPRzKq', 'Fabian Nogueroles', '1', '2026-10-26 03:37:02');
INSERT INTO `usuarios` (`id`, `usuario`, `password`, `nombre`, `activo`, `creado`) VALUES ('4', 'Gaston', '$2y$10$pij2lqZMVHaa1P37jE6PyOZreH7Vhp4qALnxMJwjBmTW2K0BhtbhO', 'Gaston', '1', '2026-10-27 04:52:41');
INSERT INTO `usuarios` (`id`, `usuario`, `password`, `nombre`, `activo`, `creado`) VALUES ('5', 'Javier', '$2y$10$nzNE3u3.34MZgeAnq9k5OO2l66wuIHCkxCkJRriWvH0FRTLjFiPBu', 'Javier', '1', '2026-10-28 04:49:57');
INSERT INTO `usuarios` (`id`, `usuario`, `password`, `nombre`, `activo`, `creado`) VALUES ('6', 'Diego', '$2y$10$QnDlE9881ap8WI4fz/B0CeaWP0czKpZOlSMTVAHEAniYxRON9PCOq', 'Diego Torres', '1', '2026-10-29 13:56:19');

-- --------------------------------------------------------
-- Estructura de la tabla `vehiculos`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `vehiculos`;
CREATE TABLE `vehiculos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `movil` int(11) NOT NULL,
  `marca` varchar(20) NOT NULL,
  `modelo` varchar(20) NOT NULL,
  `color` varchar(20) DEFAULT NULL,
  `dominio` varchar(10) NOT NULL,
  `tipo` varchar(20) NOT NULL DEFAULT 'TAXI',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `km_guardados` int(11) NOT NULL DEFAULT 0,
  `km_actuales` int(11) NOT NULL DEFAULT 0,
  `km_aceite` int(11) DEFAULT NULL,
  `km_bujias` int(11) DEFAULT NULL,
  `km_correa` int(11) DEFAULT NULL,
  `km_grasa` int(11) DEFAULT NULL,
  `km_editado_por` varchar(100) DEFAULT NULL,
  `km_editado_fecha` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `movil_unico` (`movil`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4;

-- Volcado de datos para la tabla `vehiculos`
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('1', '71', 'CHEVROLET', 'SPIN', 'NEGRO', 'AA669GD', 'TAXI', '1', '0', '0', NULL, NULL, NULL, NULL, 'Javier', '2026-10-28 12:18:51');
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('2', '72', 'CHEVROLET', 'SPIN', NULL, 'AA940SR', 'TAXI', '1', '0', '0', NULL, NULL, NULL, NULL, 'Fabian Nogueroles', '2026-10-29 11:53:52');
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('3', '94', 'FIAT', 'CRONOS', NULL, 'AF045JW', 'TAXI', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('4', '95', 'CHEVROLET', 'SPIN', NULL, 'MIS427', 'TAXI', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('5', '96', 'CHEVROLET', 'SPIN', NULL, 'MNM803', 'TAXI', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('6', '171', 'VOLKSWAGEN', 'VOYAGE', NULL, 'AA028MN', 'TAXI', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('7', '196', 'VOLKSWAGEN', 'VOYAGE', NULL, 'PQT059', 'TAXI', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('8', '500', 'VOLKSWAGEN', 'VOYAGE', NULL, 'LMK622', 'TAXI', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('9', '501', 'FIAT', 'CRONOS', NULL, 'AG866ID', 'TAXI', '1', '77120', '88120', NULL, NULL, NULL, NULL, 'Javier', '2026-10-28 12:24:03');
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('10', '799', 'FIAT', 'CRONOS', NULL, 'AG866IF', 'TAXI', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('11', '800', 'FIAT', 'CRONOS', NULL, 'AG866II', 'TAXI', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('12', '1504', 'CHEVROLET', 'SPIN', NULL, 'PNU638', 'TAXI', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('13', '1', 'VOLKSWAGEN', 'SURAN', 'NEGRO', 'NZW952/255', 'ALQUILER', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('14', '2', 'VOLKSWAGEN', 'VOYAGE', 'NEGRO', 'AB283SH/36', 'ALQUILER', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('15', '4', 'FIAT', 'SIENA', 'NEGRO', 'PCJ/429', 'BASE', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('16', '3', 'VOLKSWAGEN', 'SURAN', 'NEGRO', 'AA374SK/72', 'ALQUILER', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('17', '5', 'TOYOTA', 'COROLLA', 'CELESTE', 'AG802XZ', 'REMIS', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('18', '6', 'TOYOTA', 'COROLLA', 'BLANCO HIB', 'AG921JQ', 'REMIS', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('19', '7', 'FIAT', 'CRONOS', 'BLANCO', 'AE926IP/20', 'REMIS', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('20', '8', 'VOLKSWAGEN', 'SURAN', 'NEGRO', 'ILX072', 'BASE', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('21', '9', 'TOYOTA', 'COROLLA', 'BLANCO', 'AG357UU/93', 'REMIS', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('22', '10', 'VOLKSWAGEN', 'VOYAGE', 'NEGRO', 'NNU469', 'ALQUILER', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('23', '11', 'PEUGEOT', '408', 'AZUL', 'NGZ763', 'REMIS', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('24', '12', 'TOYOTA', 'COROLLA', 'BLANCO', 'AA426MZ', 'REMIS', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('25', '13', 'CHEVROLET', 'SPIN', 'VERDE', 'AA338KG/92', 'REMIS', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('26', '14', 'TOYOTA', 'COROLLA', 'GRIS OSCURO', 'AH060PK/52', 'REMIS', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('27', '15', 'SEAT', 'LEON', 'MARRON', 'JQS325', 'REMIS', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('28', '16', 'FIAT', 'CRONOS', 'GRIS PLATA', 'AF795AS/93', 'REMIS', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('29', '17', 'TOYOTA', 'COROLLA', 'GRIS', 'AG795IQ/52', 'REMIS', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('30', '18', 'CHEVROLET', 'SPIN', 'NEGRA', 'AI153ID', 'REMIS', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('31', '19', 'VOLKSWAGEN', 'VOYAGE', 'NEGRO', 'AB283SH/36', 'ALQUILER', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('32', '20', 'FIAT', 'CRONOS', 'GRIS', 'AG025YP/46', 'REMIS', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('33', '21', 'CHEVROLET', 'SPIN', 'BLANCO', 'NDC833', 'REMIS', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('34', '22', 'TOYOTA', 'COROLLA', 'BLANCO HIB', 'AG802WX', 'REMIS', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('35', '23', 'VOLKSWAGEN', 'SURAN', 'NEGRO', 'NXX573', 'ALQUILER', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('36', '24', 'CHEVROLET', 'SPIN', 'NEGRO', 'AA279LE', 'REMIS', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('37', '25', 'TOYOTA', 'COROLLA', 'GRIS PLATA', 'AG204TZ', 'REMIS', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('38', '26', 'TOYOTA', 'COROLLA', 'NEGRO', 'AH233LH/KE', 'REMIS', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('39', '27', 'FIAT', 'CRONOS', 'GRIS PLATA', 'AF795AV/92', 'REMIS', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('40', '28', 'FIAT', 'LINEA', 'NEGRO', 'JZH560/KEV', 'REMIS', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('41', '29', 'TOYOTA', 'COROLLA', 'GRIS PLATA', 'AG381TG', 'REMIS', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('42', '30', 'MERCEDES', 'SPRINTER', 'PAMPA', 'ETS432', 'BASE', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('43', '31', 'VOLKSWAGEN', 'SURAN', 'NEGRO', 'PBX153', 'ALQUILER', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('44', '32', 'TOYOTA', 'COROLLA', 'NEGRO', 'OUA717', 'REMIS', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('45', '33', 'TOYOTA', 'COROLLA', 'GRIS OSCURO', 'AG802YB/96', 'REMIS', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('46', '34', 'CHEVROLET', 'SPIN', 'GRIS PLATA', 'OYH672', 'REMIS', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('47', '35', 'FIAT', 'CRONOS', 'BLANCO', 'AF828AS/93', 'REMIS', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('48', '36', 'CITROEN', 'ELISEE', 'GRIS', 'AC333NV', 'REMIS', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('49', '37', 'CHEVROLET', 'SPIN', 'NEGRO', 'AV714ZM/52', 'REMIS', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `color`, `dominio`, `tipo`, `activo`, `km_guardados`, `km_actuales`, `km_aceite`, `km_bujias`, `km_correa`, `km_grasa`, `km_editado_por`, `km_editado_fecha`) VALUES ('50', '38', 'TOYOTA', 'COROLLA', 'GRIS OSCURO', 'AG802YA', 'REMIS', '1', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL);

