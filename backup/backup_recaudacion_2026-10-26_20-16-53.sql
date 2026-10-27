-- BACKUP DE BASE DE DATOS: reca
-- Fecha: 2026-10-26 20:16:53

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
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4;

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
INSERT INTO `asignaciones_auto` (`id`, `chofer_id`, `vehiculo_id`, `turno`, `fecha_desde`, `fecha_hasta`, `activa`, `observaciones`) VALUES ('19', '5', '4', 'DIA', '2026-10-21', NULL, '1', 'nuevo auto');

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
) ENGINE=InnoDB AUTO_INCREMENT=156 DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4;

-- Volcado de datos para la tabla `prestamos`
INSERT INTO `prestamos` (`id`, `chofer_id`, `monto`, `interes`, `monto_total`, `cuotas`, `valor_cuota`, `cuotas_pagas`, `fecha`, `descripcion`, `observaciones`, `estado`, `estado_mora`, `creado`) VALUES ('9', '1', '577000.00', '0.00', '577000.00', '1', '577000.00', '0', '2026-10-01', '', '', 'ACTIVO', 'AL DIA', '2026-10-26');
INSERT INTO `prestamos` (`id`, `chofer_id`, `monto`, `interes`, `monto_total`, `cuotas`, `valor_cuota`, `cuotas_pagas`, `fecha`, `descripcion`, `observaciones`, `estado`, `estado_mora`, `creado`) VALUES ('10', '4', '16500.00', '0.00', '16500.00', '1', '16500.00', '0', '2026-10-01', '', '', 'ACTIVO', 'AL DIA', '2026-10-26');
INSERT INTO `prestamos` (`id`, `chofer_id`, `monto`, `interes`, `monto_total`, `cuotas`, `valor_cuota`, `cuotas_pagas`, `fecha`, `descripcion`, `observaciones`, `estado`, `estado_mora`, `creado`) VALUES ('11', '6', '6750.00', '0.00', '6750.00', '1', '6750.00', '0', '2026-10-01', '', '', 'ACTIVO', 'AL DIA', '2026-10-26');
INSERT INTO `prestamos` (`id`, `chofer_id`, `monto`, `interes`, `monto_total`, `cuotas`, `valor_cuota`, `cuotas_pagas`, `fecha`, `descripcion`, `observaciones`, `estado`, `estado_mora`, `creado`) VALUES ('12', '8', '230000.00', '0.00', '230000.00', '1', '230000.00', '0', '2026-10-01', '', '', 'ACTIVO', 'AL DIA', '2026-10-26');
INSERT INTO `prestamos` (`id`, `chofer_id`, `monto`, `interes`, `monto_total`, `cuotas`, `valor_cuota`, `cuotas_pagas`, `fecha`, `descripcion`, `observaciones`, `estado`, `estado_mora`, `creado`) VALUES ('13', '10', '242000.00', '0.00', '242000.00', '1', '242000.00', '0', '2026-10-01', '', '', 'ACTIVO', 'AL DIA', '2026-10-26');
INSERT INTO `prestamos` (`id`, `chofer_id`, `monto`, `interes`, `monto_total`, `cuotas`, `valor_cuota`, `cuotas_pagas`, `fecha`, `descripcion`, `observaciones`, `estado`, `estado_mora`, `creado`) VALUES ('14', '11', '229000.00', '0.00', '229000.00', '1', '229000.00', '0', '2026-10-01', '', '', 'ACTIVO', 'AL DIA', '2026-10-26');

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
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB AUTO_INCREMENT=449 DEFAULT CHARSET=utf8mb4;

-- Volcado de datos para la tabla `recauda`
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('443', '2026-10-01', '1', '2', '72', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '0.00', '67500.00', '0.00', '0.00', '');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('444', '2026-10-02', '1', '2', '72', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '0.00', '67500.00', '0.00', '0.00', '');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('445', '2026-10-03', '1', '2', '72', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '0.00', '67500.00', '0.00', '0.00', '');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('446', '2026-10-05', '1', '2', '72', 'DIA', '0.00', '0.00', '10.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', 'FALTO');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('447', '2026-10-06', '1', '2', '72', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '50000.00', '17500.00', '0.00', '0.00', '');
INSERT INTO `recauda` (`id`, `fecha`, `chofer_id`, `vehiculo_id`, `movil`, `turno`, `abono_diario`, `paga`, `porcent`, `comision`, `voucher`, `mp`, `ft`, `total`, `debe`, `observaciones`) VALUES ('448', '2026-10-07', '1', '2', '72', 'DIA', '67500.00', '0.00', '10.00', '0.00', '0.00', '50000.00', '17500.00', '0.00', '0.00', '');

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

-- Volcado de datos para la tabla `usuarios`
INSERT INTO `usuarios` (`id`, `usuario`, `password`, `nombre`, `activo`, `creado`) VALUES ('1', 'admin', '$2y$10$WebHmyPHCLkiegvueh.ubuTVA.JEJjal9A.MvuIh0kSDKzSuLVv76', 'Administrador', '1', '2026-10-26 15:23:37');
INSERT INTO `usuarios` (`id`, `usuario`, `password`, `nombre`, `activo`, `creado`) VALUES ('2', 'silvia', '$2y$10$UlwdtU7ccs.LRebKKV1nF.Rs3ZmfkhJxF.LnCXyKbLnU49m3RzNbC', 'Silvia Svica', '1', '2026-10-26 15:35:14');
INSERT INTO `usuarios` (`id`, `usuario`, `password`, `nombre`, `activo`, `creado`) VALUES ('3', 'Fabian', '$2y$10$jBSydg9ju534DUieKw4lDuPl4r1PqOt/y7vblnE6QGEKbB3rPRzKq', 'Fabian Nogueroles', '1', '2026-10-26 15:37:02');

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

