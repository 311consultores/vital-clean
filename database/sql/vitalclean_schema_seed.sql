-- MariaDB dump 10.19  Distrib 10.11.14-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: vitalclean
-- ------------------------------------------------------
-- Server version	10.11.14-MariaDB-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `sys_usuarios`
--

DROP TABLE IF EXISTS `sys_usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sys_usuarios` (
  `id_usuario` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol` enum('VENDEDOR','OPERADOR','ADMIN') NOT NULL,
  `nombre_completo` varchar(150) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `sys_usuarios_username_unique` (`username`),
  UNIQUE KEY `sys_usuarios_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_usuarios`
--

LOCK TABLES `sys_usuarios` WRITE;
/*!40000 ALTER TABLE `sys_usuarios` DISABLE KEYS */;
INSERT INTO `sys_usuarios` VALUES
(1,'admin.vitalclean','$2y$12$uX9j/G.OC/au1f4v2YiE0ee8iDPNyTJm8esEVdFgTZT734ATveXcu','ADMIN','Administrador Vital Clean','admin@vitalclean.mx',1,'kanZqTWyZs','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(2,'vendedor.vitalclean','$2y$12$uX9j/G.OC/au1f4v2YiE0ee8iDPNyTJm8esEVdFgTZT734ATveXcu','VENDEDOR','Vendedor Ruta 1','vendedor@vitalclean.mx',1,'UzEMn4ZcvG','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(3,'operador.vitalclean','$2y$12$uX9j/G.OC/au1f4v2YiE0ee8iDPNyTJm8esEVdFgTZT734ATveXcu','OPERADOR','Operador de Planta','operador@vitalclean.mx',1,'pCAQdPt1Ts','2026-09-15 02:16:53','2026-09-15 02:16:53');
/*!40000 ALTER TABLE `sys_usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cat_clientes`
--

DROP TABLE IF EXISTS `cat_clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cat_clientes` (
  `id_cliente` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre_comercial` varchar(150) NOT NULL,
  `razon_social` varchar(150) DEFAULT NULL,
  `rfc` varchar(13) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `email_facturacion` varchar(100) DEFAULT NULL,
  `estatus_credito` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_cliente`),
  UNIQUE KEY `cat_clientes_rfc_unique` (`rfc`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cat_clientes`
--

LOCK TABLES `cat_clientes` WRITE;
/*!40000 ALTER TABLE `cat_clientes` DISABLE KEYS */;
INSERT INTO `cat_clientes` VALUES
(1,'Grand Hotel de Mérida','Grand Hotel de Mérida S.A. de C.V.','GHM850101XYZ','Calle 60 #450, Centro, Mérida, Yucatán, C.P. 97000','9997808557','facturacion@grandhotelmerida.com.mx',1,'2026-09-15 02:16:53','2026-09-15 02:16:53');
/*!40000 ALTER TABLE `cat_clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cat_servicios`
--

DROP TABLE IF EXISTS `cat_servicios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cat_servicios` (
  `id_servicio` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(100) NOT NULL,
  `unidad` enum('PZA','KG') NOT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_servicio`),
  UNIQUE KEY `cat_servicios_descripcion_unique` (`descripcion`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cat_servicios`
--

LOCK TABLES `cat_servicios` WRITE;
/*!40000 ALTER TABLE `cat_servicios` DISABLE KEYS */;
INSERT INTO `cat_servicios` VALUES
(1,'Sábana King Size','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(2,'Sábana Queen','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(3,'Toalla de Baño','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(4,'Toalla de Manos','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(5,'Funda de Almohada','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(6,'Mantel','KG','Restaurante','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(7,'Uniforme Personal','PZA','Uniformes','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(8,'COSTURA TAPETE','PZA','Otros','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(9,'COSTURA TOALLA BAÑO','PZA','Otros','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(10,'LAVADO DE ALMOHADA','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(11,'LAVADO DE ALMOHADA DE PLUMA','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(12,'LAVADO DE BAMBALINA','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(13,'LAVADO DE BANDERA','PZA','Otros','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(14,'LAVADO DE BATA DE BAÑO','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(15,'LAVADO DE CAMINO','PZA','Restaurante','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(16,'LAVADO DE CASACAS','PZA','Uniformes','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(17,'LAVADO DE CHAMARRA','PZA','Uniformes','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(18,'LAVADO DE COBERTOR','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(19,'LAVADO DE CORTINA','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(20,'LAVADO DE CUBRECHAROLA','PZA','Restaurante','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(21,'LAVADO DE CUBRECOLCHON','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(22,'LAVADO DE CUBREMANTEL','PZA','Restaurante','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(23,'LAVADO DE CUBRESILLA','PZA','Restaurante','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(24,'LAVADO DE DUVET','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(25,'LAVADO DE EDREDON','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(26,'LAVADO DE FUNDA DE BURRO','PZA','Otros','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(27,'LAVADO DE FUNDA DE COJIN','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(28,'LAVADO DE FUNDA DECORADA','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(29,'LAVADO DE FUNDAS','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(30,'LAVADO DE FUNDAS KING','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(31,'LAVADO DE GORRO','PZA','Uniformes','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(32,'LAVADO DE INSERTO','PZA','Restaurante','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(33,'LAVADO DE LAZO','PZA','Otros','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(34,'LAVADO DE LIMPION','KG','Restaurante','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(35,'LAVADO DE MANTEL CHICO','PZA','Restaurante','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(36,'LAVADO DE MANTEL DE FELPA','PZA','Restaurante','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(37,'LAVADO DE MANTEL GRANDE','PZA','Restaurante','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(38,'LAVADO DE MANTEL MEDIANO','PZA','Restaurante','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(39,'LAVADO DE MANTEL REDONDO','PZA','Restaurante','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(40,'LAVADO MANTEL TABLON','PZA','Restaurante','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(41,'LAVADO DE PANTUFLAS','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(42,'LAVADO DE PROTECTOR DE ALMOHADA','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(43,'LAVADO DE PROTECTOR DE COLCHON','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(44,'LAVADO DE RODAPIE','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(45,'LAVADO DE SABANAS','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(46,'LAVADO SABANA KING','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(47,'LAVADO DE SERVILLETAS','PZA','Restaurante','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(48,'LAVADO DE SOBRECAMA','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(49,'LAVADO DE TAPETE DE BAÑO','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(50,'LAVADO DE TERNOS','PZA','Uniformes','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(51,'LAVADO DE TOALLA CAFE','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(52,'LAVADO DE TOALLA DE ALBERCA','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(53,'LAVADO DE TOALLA DE BAÑO','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(54,'LAVADO DE TOALLA DE BAÑO CHICA','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(55,'LAVADO DE TOALLA DE BAÑO GRANDE','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(56,'LAVADO DE TOALLA DE MANO','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(57,'LAVADO DE TOALLA FACIAL','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(58,'LAVADO DE TORTILLERAS','PZA','Restaurante','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(59,'LAVADO DE UNIFORMES','PZA','Uniformes','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(60,'LAVADO FUNDA SILLON','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(61,'LAVADO MANDIL','PZA','Uniformes','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(62,'LAVADO PROTECTOR CUNA','PZA','Hotelería','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(63,'LAVANDERIA DE BLANCOS HOSPITALARIOS','KG','Otros','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(64,'LAVADO DE PANERA','PZA','Restaurante','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(65,'SERVICIO DE DESMANCHE','PZA','Otros','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(66,'SERVICIO DE LAVADO DE MANTELERIA','KG','Restaurante','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(67,'SERVICIO DE LAVANDERIA','KG','Otros','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(68,'SERVICIO DE LAVANDERIA INTEGRAL','KG','Otros','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(69,'SERVICIO DE TINTORERIA','PZA','Otros','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(70,'LAVADO','PZA','Otros','2026-09-15 02:16:53','2026-09-15 02:16:53'),
(71,'DESMANCHE','PZA','Otros','2026-09-15 02:16:53','2026-09-15 02:16:53');
/*!40000 ALTER TABLE `cat_servicios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rel_tarifas_cliente`
--

DROP TABLE IF EXISTS `rel_tarifas_cliente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rel_tarifas_cliente` (
  `id_tarifa` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_cliente` bigint(20) unsigned NOT NULL,
  `id_servicio` bigint(20) unsigned NOT NULL,
  `precio_pactado` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_tarifa`),
  UNIQUE KEY `rel_tarifas_cliente_id_cliente_id_servicio_unique` (`id_cliente`,`id_servicio`),
  KEY `rel_tarifas_cliente_id_servicio_foreign` (`id_servicio`),
  CONSTRAINT `rel_tarifas_cliente_id_cliente_foreign` FOREIGN KEY (`id_cliente`) REFERENCES `cat_clientes` (`id_cliente`),
  CONSTRAINT `rel_tarifas_cliente_id_servicio_foreign` FOREIGN KEY (`id_servicio`) REFERENCES `cat_servicios` (`id_servicio`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rel_tarifas_cliente`
--

LOCK TABLES `rel_tarifas_cliente` WRITE;
/*!40000 ALTER TABLE `rel_tarifas_cliente` DISABLE KEYS */;
INSERT INTO `rel_tarifas_cliente` VALUES
(1,1,1,54.51,'2026-09-15 02:16:53','2026-09-15 02:16:53'),
(2,1,2,59.61,'2026-09-15 02:16:53','2026-09-15 02:16:53'),
(3,1,3,16.10,'2026-09-15 02:16:53','2026-09-15 02:16:53'),
(4,1,4,50.20,'2026-09-15 02:16:53','2026-09-15 02:16:53'),
(5,1,5,18.55,'2026-09-15 02:16:53','2026-09-15 02:16:53'),
(6,1,6,44.07,'2026-09-15 02:16:53','2026-09-15 02:16:53'),
(7,1,7,53.03,'2026-09-15 02:16:53','2026-09-15 02:16:53');
/*!40000 ALTER TABLE `rel_tarifas_cliente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ope_notas_remision`
--

DROP TABLE IF EXISTS `ope_notas_remision`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ope_notas_remision` (
  `folio_sistema` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `folio_fisico` varchar(20) NOT NULL,
  `folio_padre` bigint(20) unsigned DEFAULT NULL,
  `secuencia_subnota` tinyint(3) unsigned DEFAULT NULL,
  `id_cliente` bigint(20) unsigned NOT NULL,
  `id_vendedor` bigint(20) unsigned NOT NULL,
  `fecha_recoleccion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_entrega_prog` date DEFAULT NULL,
  `estatus_orden` enum('RUTA','PLANTA_RECIBIDO','PROCESO','LISTO','ENTREGADO','CANCELADO') NOT NULL DEFAULT 'RUTA',
  `firma_cliente` mediumblob DEFAULT NULL,
  `firma_entrega` mediumblob DEFAULT NULL,
  `geolocalizacion` varchar(100) DEFAULT NULL,
  `conteo_bloqueado` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`folio_sistema`),
  KEY `ope_notas_remision_id_cliente_foreign` (`id_cliente`),
  KEY `ope_notas_remision_id_vendedor_foreign` (`id_vendedor`),
  KEY `ope_notas_remision_folio_fisico_index` (`folio_fisico`),
  KEY `ope_notas_remision_folio_padre_foreign` (`folio_padre`),
  CONSTRAINT `ope_notas_remision_folio_padre_foreign` FOREIGN KEY (`folio_padre`) REFERENCES `ope_notas_remision` (`folio_sistema`) ON DELETE SET NULL,
  CONSTRAINT `ope_notas_remision_id_cliente_foreign` FOREIGN KEY (`id_cliente`) REFERENCES `cat_clientes` (`id_cliente`),
  CONSTRAINT `ope_notas_remision_id_vendedor_foreign` FOREIGN KEY (`id_vendedor`) REFERENCES `sys_usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ope_notas_remision`
--

LOCK TABLES `ope_notas_remision` WRITE;
/*!40000 ALTER TABLE `ope_notas_remision` DISABLE KEYS */;
/*!40000 ALTER TABLE `ope_notas_remision` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ope_detalle_remision`
--

DROP TABLE IF EXISTS `ope_detalle_remision`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ope_detalle_remision` (
  `id_detalle` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `folio_sistema` bigint(20) unsigned NOT NULL,
  `id_servicio` bigint(20) unsigned NOT NULL,
  `cantidad_entrada` int(11) NOT NULL DEFAULT 0,
  `cantidad_salida` int(11) DEFAULT NULL,
  `precio_aplicado` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `observacion_prenda` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_detalle`),
  KEY `ope_detalle_remision_folio_sistema_foreign` (`folio_sistema`),
  KEY `ope_detalle_remision_id_servicio_foreign` (`id_servicio`),
  CONSTRAINT `ope_detalle_remision_folio_sistema_foreign` FOREIGN KEY (`folio_sistema`) REFERENCES `ope_notas_remision` (`folio_sistema`),
  CONSTRAINT `ope_detalle_remision_id_servicio_foreign` FOREIGN KEY (`id_servicio`) REFERENCES `cat_servicios` (`id_servicio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ope_detalle_remision`
--

LOCK TABLES `ope_detalle_remision` WRITE;
/*!40000 ALTER TABLE `ope_detalle_remision` DISABLE KEYS */;
/*!40000 ALTER TABLE `ope_detalle_remision` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ope_incidencias`
--

DROP TABLE IF EXISTS `ope_incidencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ope_incidencias` (
  `id_incidencia` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_detalle` bigint(20) unsigned NOT NULL,
  `foto_evidencia` varchar(255) DEFAULT NULL,
  `comentario` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_incidencia`),
  KEY `ope_incidencias_id_detalle_foreign` (`id_detalle`),
  CONSTRAINT `ope_incidencias_id_detalle_foreign` FOREIGN KEY (`id_detalle`) REFERENCES `ope_detalle_remision` (`id_detalle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ope_incidencias`
--

LOCK TABLES `ope_incidencias` WRITE;
/*!40000 ALTER TABLE `ope_incidencias` DISABLE KEYS */;
/*!40000 ALTER TABLE `ope_incidencias` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-15  2:17:26
