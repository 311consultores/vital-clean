-- MariaDB dump 10.19  Distrib 10.11.14-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: vitalclean
-- ------------------------------------------------------
-- Server version	10.11.14-MariaDB-0ubuntu0.24.04.1
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

/*!40000 ALTER TABLE `sys_usuarios` DISABLE KEYS */;
INSERT INTO `sys_usuarios` (`id_usuario`, `username`, `password_hash`, `rol`, `nombre_completo`, `email`, `activo`, `remember_token`, `created_at`, `updated_at`) VALUES (1,'admin.vitalclean','$2y$12$bXsKWemv4ASTMjVdMjX0Sefv0.NZ7k8/WrPxr/2UwPw7J6VArH8je','ADMIN','Administrador Vital Clean','admin@vitalclean.mx',1,'zvhpmjVool','2026-08-12 05:05:08','2026-08-12 05:05:08'),
(2,'vendedor.vitalclean','$2y$12$bXsKWemv4ASTMjVdMjX0Sefv0.NZ7k8/WrPxr/2UwPw7J6VArH8je','VENDEDOR','Vendedor Ruta 1','vendedor@vitalclean.mx',1,'PmZDuAZ2vF','2026-08-12 05:05:08','2026-08-12 05:05:08'),
(3,'operador.vitalclean','$2y$12$bXsKWemv4ASTMjVdMjX0Sefv0.NZ7k8/WrPxr/2UwPw7J6VArH8je','OPERADOR','Operador de Planta','operador@vitalclean.mx',1,'rGoUE2BkYC','2026-08-12 05:05:08','2026-08-12 05:05:08');
/*!40000 ALTER TABLE `sys_usuarios` ENABLE KEYS */;

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

/*!40000 ALTER TABLE `cat_clientes` DISABLE KEYS */;
INSERT INTO `cat_clientes` (`id_cliente`, `nombre_comercial`, `razon_social`, `rfc`, `direccion`, `telefono`, `email_facturacion`, `estatus_credito`, `created_at`, `updated_at`) VALUES (1,'Grand Hotel de Mérida','Grand Hotel de Mérida S.A. de C.V.','GHM850101XYZ','Calle 60 #450, Centro, Mérida, Yucatán, C.P. 97000','9997808557','facturacion@grandhotelmerida.com.mx',1,'2026-08-12 05:05:08','2026-08-12 05:05:08');
/*!40000 ALTER TABLE `cat_clientes` ENABLE KEYS */;

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
) ENGINE=InnoDB AUTO_INCREMENT=132 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cat_servicios`
--

/*!40000 ALTER TABLE `cat_servicios` DISABLE KEYS */;
INSERT INTO `cat_servicios` (`id_servicio`, `descripcion`, `unidad`, `categoria`, `created_at`, `updated_at`) VALUES (1,'Sábana King Size','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:08'),
(2,'Sábana Queen','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:08'),
(3,'Toalla de Baño','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:08'),
(4,'Toalla de Manos','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:08'),
(5,'Funda de Almohada','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:08'),
(6,'Mantel','KG','Restaurante','2026-08-12 05:05:08','2026-08-12 05:05:08'),
(7,'Uniforme Personal','PZA','Uniformes','2026-08-12 05:05:08','2026-08-12 05:05:08'),
(8,'COSTURA TAPETE','PZA','Otros','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(9,'COSTURA TOALLA BAÑO','PZA','Otros','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(10,'LAVADO DE ALMOHADA','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(11,'LAVADO DE ALMOHADA DE PLUMA','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(12,'LAVADO DE BAMBALINA','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(13,'LAVADO DE BANDERA','PZA','Otros','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(14,'LAVADO DE BATA DE BAÑO','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(15,'LAVADO DE CAMINO','PZA','Restaurante','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(16,'LAVADO DE CASACAS','PZA','Uniformes','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(17,'LAVADO DE CHAMARRA','PZA','Uniformes','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(18,'LAVADO DE COBERTOR','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(19,'LAVADO DE CORTINA','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(20,'LAVADO DE CUBRECHAROLA','PZA','Restaurante','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(21,'LAVADO DE CUBRECOLCHON','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(22,'LAVADO DE CUBREMANTEL','PZA','Restaurante','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(23,'LAVADO DE CUBRESILLA','PZA','Restaurante','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(24,'LAVADO DE DUVET','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(25,'LAVADO DE EDREDON','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(26,'LAVADO DE FUNDA DE BURRO','PZA','Otros','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(27,'LAVADO DE FUNDA DE COJIN','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(28,'LAVADO DE FUNDA DECORADA','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(29,'LAVADO DE FUNDAS','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(30,'LAVADO DE FUNDAS KING','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(31,'LAVADO DE GORRO','PZA','Uniformes','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(32,'LAVADO DE INSERTO','PZA','Restaurante','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(33,'LAVADO DE LAZO','PZA','Otros','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(34,'LAVADO DE LIMPION','KG','Restaurante','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(35,'LAVADO DE MANTEL CHICO','PZA','Restaurante','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(36,'LAVADO DE MANTEL DE FELPA','PZA','Restaurante','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(37,'LAVADO DE MANTEL GRANDE','PZA','Restaurante','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(38,'LAVADO DE MANTEL MEDIANO','PZA','Restaurante','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(39,'LAVADO DE MANTEL REDONDO','PZA','Restaurante','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(40,'LAVADO MANTEL TABLON','PZA','Restaurante','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(41,'LAVADO DE PANTUFLAS','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(42,'LAVADO DE PROTECTOR DE ALMOHADA','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(43,'LAVADO DE PROTECTOR DE COLCHON','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(44,'LAVADO DE RODAPIE','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(45,'LAVADO DE SABANAS','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(46,'LAVADO SABANA KING','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(47,'LAVADO DE SERVILLETAS','PZA','Restaurante','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(48,'LAVADO DE SOBRECAMA','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(49,'LAVADO DE TAPETE DE BAÑO','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(50,'LAVADO DE TERNOS','PZA','Uniformes','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(51,'LAVADO DE TOALLA CAFE','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(52,'LAVADO DE TOALLA DE ALBERCA','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(53,'LAVADO DE TOALLA DE BAÑO','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(54,'LAVADO DE TOALLA DE BAÑO CHICA','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(55,'LAVADO DE TOALLA DE BAÑO GRANDE','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(56,'LAVADO DE TOALLA DE MANO','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(57,'LAVADO DE TOALLA FACIAL','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(58,'LAVADO DE TORTILLERAS','PZA','Restaurante','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(59,'LAVADO DE UNIFORMES','PZA','Uniformes','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(60,'LAVADO FUNDA SILLON','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(61,'LAVADO MANDIL','PZA','Uniformes','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(62,'LAVADO PROTECTOR CUNA','PZA','Hotelería','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(63,'LAVANDERIA DE BLANCOS HOSPITALARIOS','KG','Otros','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(64,'LAVADO DE PANERA','PZA','Restaurante','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(65,'SERVICIO DE DESMANCHE','PZA','Otros','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(66,'SERVICIO DE LAVADO DE MANTELERIA','KG','Restaurante','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(67,'SERVICIO DE LAVANDERIA','KG','Otros','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(68,'SERVICIO DE LAVANDERIA INTEGRAL','KG','Otros','2026-08-12 05:05:08','2026-08-12 05:05:13'),
(69,'SERVICIO DE TINTORERIA','PZA','Otros','2026-08-12 05:05:08','2026-08-12 05:05:13');
/*!40000 ALTER TABLE `cat_servicios` ENABLE KEYS */;

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

/*!40000 ALTER TABLE `rel_tarifas_cliente` DISABLE KEYS */;
INSERT INTO `rel_tarifas_cliente` (`id_tarifa`, `id_cliente`, `id_servicio`, `precio_pactado`, `created_at`, `updated_at`) VALUES (1,1,1,10.67,'2026-08-12 05:05:08','2026-08-12 05:05:08'),
(2,1,2,25.58,'2026-08-12 05:05:08','2026-08-12 05:05:08'),
(3,1,3,57.94,'2026-08-12 05:05:08','2026-08-12 05:05:08'),
(4,1,4,56.84,'2026-08-12 05:05:08','2026-08-12 05:05:08'),
(5,1,5,23.88,'2026-08-12 05:05:08','2026-08-12 05:05:08'),
(6,1,6,11.99,'2026-08-12 05:05:08','2026-08-12 05:05:08'),
(7,1,7,46.50,'2026-08-12 05:05:08','2026-08-12 05:05:08');
/*!40000 ALTER TABLE `rel_tarifas_cliente` ENABLE KEYS */;

--
-- Table structure for table `ope_notas_remision`
--

DROP TABLE IF EXISTS `ope_notas_remision`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ope_notas_remision` (
  `folio_sistema` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `folio_fisico` varchar(20) NOT NULL,
  `id_cliente` bigint(20) unsigned NOT NULL,
  `id_vendedor` bigint(20) unsigned NOT NULL,
  `fecha_recoleccion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_entrega_prog` date DEFAULT NULL,
  `estatus_orden` enum('RUTA','PLANTA_RECIBIDO','PROCESO','LISTO','ENTREGADO','CANCELADO') NOT NULL DEFAULT 'RUTA',
  `firma_cliente` mediumblob DEFAULT NULL,
  `geolocalizacion` varchar(100) DEFAULT NULL,
  `conteo_bloqueado` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`folio_sistema`),
  KEY `ope_notas_remision_id_cliente_foreign` (`id_cliente`),
  KEY `ope_notas_remision_id_vendedor_foreign` (`id_vendedor`),
  KEY `ope_notas_remision_folio_fisico_index` (`folio_fisico`),
  CONSTRAINT `ope_notas_remision_id_cliente_foreign` FOREIGN KEY (`id_cliente`) REFERENCES `cat_clientes` (`id_cliente`),
  CONSTRAINT `ope_notas_remision_id_vendedor_foreign` FOREIGN KEY (`id_vendedor`) REFERENCES `sys_usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ope_notas_remision`
--

/*!40000 ALTER TABLE `ope_notas_remision` DISABLE KEYS */;
/*!40000 ALTER TABLE `ope_notas_remision` ENABLE KEYS */;

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

/*!40000 ALTER TABLE `ope_detalle_remision` DISABLE KEYS */;
/*!40000 ALTER TABLE `ope_detalle_remision` ENABLE KEYS */;

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

/*!40000 ALTER TABLE `ope_incidencias` DISABLE KEYS */;
/*!40000 ALTER TABLE `ope_incidencias` ENABLE KEYS */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-12  5:05:20
