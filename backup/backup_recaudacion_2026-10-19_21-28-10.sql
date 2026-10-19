-- BACKUP DE BASE DE DATOS: reca
-- Fecha: 2026-10-19 21:28:10

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
  PRIMARY KEY (`id`),
  KEY `idx_chofer` (`chofer_id`),
  KEY `idx_vehiculo` (`vehiculo_id`),
  KEY `idx_activa` (`activa`),
  KEY `idx_vehiculo_turno_activa` (`vehiculo_id`,`turno`,`activa`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4;

-- Volcado de datos para la tabla `asignaciones_auto`
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`) VALUES ('1', '1', '5', 'DIA', '2026-10-15', '2026-10-01', '0', '');
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`) VALUES ('2', '2', '10', 'DIA', '2026-10-15', '2026-10-19', '0', '');
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`) VALUES ('3', '3', '8', 'DIA', '2026-10-15', '2026-10-19', '0', '');
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`) VALUES ('4', '1', '2', 'DIA', '2026-10-01', NULL, '1', 'EMPEZO MES CON NUEVO AUTO');
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`) VALUES ('5', '3', '2', 'NOCHE', '2026-10-19', '2026-10-19', '0', 'EMPEZO CON NUEVO AUTO');
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`) VALUES ('6', '12', '12', 'NOCHE', '2026-10-19', NULL, '1', 'NUEVO AUTO');
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`) VALUES ('7', '10', '10', 'DIA', '2026-10-19', NULL, '1', 'NUEVO AUTO');
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`) VALUES ('8', '9', '9', 'DIA', '2026-10-19', NULL, '1', 'NUEVO AUTO');
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`) VALUES ('9', '8', '7', 'DIA', '2026-10-19', NULL, '1', 'NUEVO AUTO');
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`) VALUES ('10', '7', '6', 'DIA', '2026-10-19', NULL, '1', 'NUEVO AUTO');
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`) VALUES ('11', '6', '5', 'DIA', '2026-10-19', NULL, '1', 'NUEVO AUTO');
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`) VALUES ('12', '5', '4', 'DIA', '2026-10-19', '2026-10-19', '0', 'NUEVO AUTO');
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`) VALUES ('13', '2', '4', 'NOCHE', '2026-10-19', '2026-10-19', '0', 'NUEVO AUTO');
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`) VALUES ('14', '4', '3', 'DIA', '2026-10-19', NULL, '1', 'NUEVO AUTO');
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`) VALUES ('15', '13', '3', 'NOCHE', '2026-10-19', NULL, '1', 'NUEVO AUTO');
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`) VALUES ('16', '11', '11', 'DIA', '2026-10-19', NULL, '1', 'NUEVO AUTO');
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`) VALUES ('17', '3', '4', 'NOCHE', '2026-10-19', NULL, '1', 'CAMBIO DE UTO PORQUE EL DEL TURNO DIA LLEGO TARDE');
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`) VALUES ('18', '2', '2', 'NOCHE', '2026-10-19', NULL, '1', 'CAMBIO DE AUTO PORQUE DUARTE LLEGO TARDE');

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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4;

-- Volcado de datos para la tabla `choferes`
INSERT INTO `choferes` (`id`, `nombre`, `cel`, `email`, `barrio`, `dir`, `cp`, `foto`, `activo`, `fecha_alta`) VALUES ('1', 'DUARTE', '1156238956', 'juan@gmail.com', 'FLORES', 'OLIDEN 258', '', 'chofer_6ad0e26374f3f_rostro-1.webp', '1', '2026-10-15');
INSERT INTO `choferes` (`id`, `nombre`, `cel`, `email`, `barrio`, `dir`, `cp`, `foto`, `activo`, `fecha_alta`) VALUES ('2', 'SHMIDT', '1136525896', 'ELSAPATO@GMAIL.COM', 'Belgrano', 'JURAMENTO 2589', '', 'chofer_6ad0e2a27d01f_rosto.webp', '1', '2026-10-15');
INSERT INTO `choferes` (`id`, `nombre`, `cel`, `email`, `barrio`, `dir`, `cp`, `foto`, `activo`, `fecha_alta`) VALUES ('3', 'JADUR', '1111111111', 'robertolopez@gmail.com', 'PAlermo Holliwood', 'Niceto Vega 1500', '1450', 'chofer_6ad0e3f590d69_CARA DE PERRO.jpg', '1', '2026-10-15');
INSERT INTO `choferes` (`id`, `nombre`, `cel`, `email`, `barrio`, `dir`, `cp`, `foto`, `activo`, `fecha_alta`) VALUES ('4', 'GOMEZ IVAN', '22222222', 'GOMEZ@GMAIL.COM', '', '', '', NULL, '1', '2026-10-19');
INSERT INTO `choferes` (`id`, `nombre`, `cel`, `email`, `barrio`, `dir`, `cp`, `foto`, `activo`, `fecha_alta`) VALUES ('5', 'CALLEGARI', '1133333333', 'CALEGARI@GMAIL.COM', '', '', '', NULL, '1', '2026-10-19');
INSERT INTO `choferes` (`id`, `nombre`, `cel`, `email`, `barrio`, `dir`, `cp`, `foto`, `activo`, `fecha_alta`) VALUES ('6', 'IGLESIAS', '1144444444', '', '', '', '', NULL, '1', '2026-10-19');
INSERT INTO `choferes` (`id`, `nombre`, `cel`, `email`, `barrio`, `dir`, `cp`, `foto`, `activo`, `fecha_alta`) VALUES ('7', 'GASTON', '1155555555', 'GASTON@GMAIL.COM', '', '', '', NULL, '1', '2026-10-19');
INSERT INTO `choferes` (`id`, `nombre`, `cel`, `email`, `barrio`, `dir`, `cp`, `foto`, `activo`, `fecha_alta`) VALUES ('8', 'MAGLIANO', '1166666666', 'MAGLIANO@GMAIL.COM', '', '', '', NULL, '1', '2026-10-19');
INSERT INTO `choferes` (`id`, `nombre`, `cel`, `email`, `barrio`, `dir`, `cp`, `foto`, `activo`, `fecha_alta`) VALUES ('9', 'ODDI', '1177777777', 'ODDI@GMAIL.COM', '', '', '', NULL, '1', '2026-10-19');
INSERT INTO `choferes` (`id`, `nombre`, `cel`, `email`, `barrio`, `dir`, `cp`, `foto`, `activo`, `fecha_alta`) VALUES ('10', 'BARRIONUEVO', '1188888888', 'BARRIONUEVO@GMAIL.COM', '', '', '', NULL, '1', '2026-10-19');
INSERT INTO `choferes` (`id`, `nombre`, `cel`, `email`, `barrio`, `dir`, `cp`, `foto`, `activo`, `fecha_alta`) VALUES ('11', 'ZURITA', '1199999999', 'ZURITA@GMAIL.COM', '', '', '', NULL, '1', '2026-10-19');
INSERT INTO `choferes` (`id`, `nombre`, `cel`, `email`, `barrio`, `dir`, `cp`, `foto`, `activo`, `fecha_alta`) VALUES ('12', 'MASTRANGELO', '1100000000', 'MASTRANGELO@GMAIL.COM', '', '', '', NULL, '1', '2026-10-19');
INSERT INTO `choferes` (`id`, `nombre`, `cel`, `email`, `barrio`, `dir`, `cp`, `foto`, `activo`, `fecha_alta`) VALUES ('13', 'OCHOA', '1100001111', '8A@GMAIL.COM', '', '', '', NULL, '1', '2026-10-19');

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
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4;

-- Volcado de datos para la tabla `cuenta_corriente`
INSERT INTO `cuenta_corriente` (`id`, `chofer_id`, `fecha`, `monto`, `tipo`, `descripcion`, `recauda_id`) VALUES ('59', '1', '2026-09-30', '-577000.00', 'DEUDA', 'Quedó debiendo del turno DIA del 2026-09-30', '78');
INSERT INTO `cuenta_corriente` (`id`, `chofer_id`, `fecha`, `monto`, `tipo`, `descripcion`, `recauda_id`) VALUES ('60', '1', '2026-10-08', '-1.00', 'DEUDA', 'Quedó debiendo del turno DIA del 2026-10-08', '77');
INSERT INTO `cuenta_corriente` (`id`, `chofer_id`, `fecha`, `monto`, `tipo`, `descripcion`, `recauda_id`) VALUES ('62', '3', '2026-10-06', '-4500.00', 'DEUDA', 'Quedó debiendo del turno NOCHE del 2026-10-06', '92');
INSERT INTO `cuenta_corriente` (`id`, `chofer_id`, `fecha`, `monto`, `tipo`, `descripcion`, `recauda_id`) VALUES ('63', '3', '2026-10-07', '4300.00', 'A CUENTA', 'A cuenta del turno NOCHE del 2026-10-07', '94');

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
  `estado` enum('ACTIVO','PAGADO','CANCELADO','ARCHIVADO') DEFAULT 'ACTIVO',
  `estado_mora` enum('AL DIA','EN MORA') DEFAULT 'AL DIA',
  `creado` date NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_chofer` (`chofer_id`),
  KEY `idx_fecha` (`fecha`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4;

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `fecha_chofer` (`fecha`,`chofer_id`),
  KEY `idx_chofer` (`chofer_id`),
  KEY `idx_fecha` (`fecha`),
  KEY `idx_movil` (`movil`)
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4;

-- Volcado de datos para la tabla `recauda`
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('67', '2026-10-01', '1', '2', '72', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '0.00', '67500.00', '0.00', '0.00', '');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('68', '2026-10-02', '1', '2', '72', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '0.00', '67500.00', '0.00', '0.00', '');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('69', '2026-10-03', '1', '2', '72', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '0.00', '67500.00', '0.00', '0.00', '');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('70', '2026-10-04', '1', '2', '72', 'DIA', '0.00', '0.00', '10.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', 'SALIO NO PAGO');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('73', '2026-10-05', '1', '2', '72', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '50000.00', '17500.00', '0.00', '0.00', 'LUNES 5-10 FALTO');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('75', '2026-10-06', '1', '2', '72', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '50000.00', '17500.00', '0.00', '0.00', '');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('76', '2026-10-07', '1', '2', '72', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '50000.00', '17500.00', '0.00', '0.00', '');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('77', '2026-10-08', '1', '2', '72', 'DIA', '67501.00', '0.00', '10.00', '0.00', '0.00', '0.00', '67500.00', '0.00', '1.00', '');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('78', '2026-09-30', '1', '2', '72', 'DIA', '577000.00', '0.00', '10.00', '0.00', '0.00', '0.00', '0.00', '0.00', '577000.00', '');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('80', '2026-10-09', '1', '2', '72', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '0.00', '67500.00', '0.00', '0.00', '');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('81', '2026-10-10', '1', '2', '72', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '0.00', '67500.00', '0.00', '0.00', '');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('83', '2026-10-11', '1', '2', '72', 'DIA', '0.00', '0.00', '10.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', 'NO TRABAJO');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('84', '2026-10-12', '1', '2', '72', 'DIA', '33750.00', '0.00', '10.00', '0.00', '0.00', '0.00', '33750.00', '0.00', '0.00', '');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('85', '2026-10-13', '1', '2', '72', 'DIA', '0.00', '0.00', '10.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', 'FALTO');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('86', '2026-10-14', '1', '2', '72', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '0.00', '67500.00', '0.00', '0.00', '');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('87', '2026-10-15', '1', '2', '72', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '0.00', '67500.00', '0.00', '0.00', '');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('88', '2026-10-16', '1', '2', '72', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '17500.00', '50000.00', '0.00', '0.00', '');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('89', '2026-10-17', '1', '2', '72', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '0.00', '67500.00', '0.00', '0.00', '');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('90', '2026-10-18', '1', '2', '72', 'DIA', '0.00', '0.00', '10.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', 'SALIO NO PAGO');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('91', '2026-10-05', '3', '2', '72', 'NOCHE', '65000.00', '0.00', '10.00', '0.00', '0.00', '0.00', '65000.00', '0.00', '0.00', '');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('92', '2026-10-06', '3', '2', '72', 'NOCHE', '58500.00', '0.00', '10.00', '0.00', '0.00', '0.00', '54000.00', '0.00', '4500.00', 'paga 54000 se le descontaron $4500- para gas que el compañero tiene que pagar');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('94', '2026-10-07', '3', '2', '72', 'NOCHE', '65000.00', '0.00', '10.00', '0.00', '0.00', '0.00', '69300.00', '0.00', '-4300.00', '');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('95', '2026-10-08', '3', '2', '72', 'NOCHE', '65000.00', '0.00', '10.00', '0.00', '0.00', '0.00', '65000.00', '0.00', '0.00', '');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('98', '2026-10-09', '2', '2', '72', 'NOCHE', '60000.00', '0.00', '10.00', '0.00', '0.00', '47000.00', '13000.00', '0.00', '0.00', '');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('99', '2026-10-10', '2', '2', '72', 'NOCHE', '60000.00', '0.00', '10.00', '0.00', '0.00', '48000.00', '12000.00', '0.00', '0.00', '');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('100', '2026-10-11', '2', '2', '72', 'NOCHE', '60000.00', '0.00', '10.00', '0.00', '0.00', '60000.00', '0.00', '0.00', '0.00', '');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('101', '2026-10-12', '2', '2', '72', 'NOCHE', '30000.00', '0.00', '10.00', '0.00', '0.00', '10000.00', '20000.00', '0.00', '0.00', '');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('102', '2026-10-13', '2', '2', '72', 'NOCHE', '60000.00', '0.00', '10.00', '0.00', '0.00', '3000.00', '57000.00', '0.00', '0.00', '');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('103', '2026-10-14', '2', '2', '72', 'NOCHE', '0.00', '0.00', '10.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', 'FRANCO');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('104', '2026-10-15', '2', '2', '72', 'NOCHE', '60000.00', '0.00', '10.00', '0.00', '0.00', '60000.00', '0.00', '0.00', '0.00', '');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('105', '2026-10-16', '2', '2', '72', 'NOCHE', '60000.00', '0.00', '10.00', '0.00', '0.00', '60000.00', '0.00', '0.00', '0.00', '');

-- --------------------------------------------------------
-- Estructura de la tabla `vehiculos`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `vehiculos`;
CREATE TABLE `vehiculos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `movil` int(11) NOT NULL,
  `marca` varchar(20) NOT NULL,
  `modelo` varchar(20) NOT NULL,
  `dominio` varchar(10) NOT NULL,
  `tipo` varchar(10) NOT NULL DEFAULT 'TAXI',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `movil_unico` (`movil`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4;

-- Volcado de datos para la tabla `vehiculos`
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `dominio`, `tipo`, `activo`) VALUES ('1', '71', 'CHEVROLET', 'SPIN', 'AA669GD', 'TAXI', '1');
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `dominio`, `tipo`, `activo`) VALUES ('2', '72', 'CHEVROLET', 'SPIN', 'AA940SR', 'TAXI', '1');
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `dominio`, `tipo`, `activo`) VALUES ('3', '94', 'FIAT', 'CRONOS', 'AF045JW', 'TAXI', '1');
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `dominio`, `tipo`, `activo`) VALUES ('4', '95', 'CHEVROLET', 'SPIN', 'MIS427', 'TAXI', '1');
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `dominio`, `tipo`, `activo`) VALUES ('5', '96', 'CHEVROLET', 'SPIN', 'MNM803', 'TAXI', '1');
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `dominio`, `tipo`, `activo`) VALUES ('6', '171', 'VOLKSWAGEN', 'VOYAGE', 'AA028MN', 'TAXI', '1');
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `dominio`, `tipo`, `activo`) VALUES ('7', '196', 'VOLKSWAGEN', 'VOYAGE', 'PQT059', 'TAXI', '1');
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `dominio`, `tipo`, `activo`) VALUES ('8', '500', 'VOLKSWAGEN', 'VOYAGE', 'LMK622', 'TAXI', '1');
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `dominio`, `tipo`, `activo`) VALUES ('9', '501', 'FIAT', 'CRONOS', 'AG866ID', 'TAXI', '1');
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `dominio`, `tipo`, `activo`) VALUES ('10', '799', 'FIAT', 'CRONOS', 'AG866IF', 'TAXI', '1');
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `dominio`, `tipo`, `activo`) VALUES ('11', '800', 'FIAT', 'CRONOS', 'AG866II', 'TAXI', '1');
INSERT INTO `vehiculos` (`id`, `movil`, `marca`, `modelo`, `dominio`, `tipo`, `activo`) VALUES ('12', '1504', 'CHEVROLET', 'SPIN', 'PNU638', 'TAXI', '1');

