-- MySQL dump 10.13  Distrib 8.0.34, for Win64 (x86_64)
--
-- Host: localhost    Database: inventario
-- ------------------------------------------------------
-- Server version	8.0.34

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `categorias`
--

DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias` (
  `idCategoria` int NOT NULL,
  `nombreCategoria` varchar(45) NOT NULL,
  `descripcionCategoria` varchar(120) NOT NULL,
  PRIMARY KEY (`idCategoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias`
--

LOCK TABLES `categorias` WRITE;
/*!40000 ALTER TABLE `categorias` DISABLE KEYS */;
INSERT INTO `categorias` VALUES (1,'Papeleria','Productos de papeleria general, papeles, copias, impresiones'),(2,'Electronica','Productos electricos o relacionados'),(3,'Jueguetería y deportes','pelotas, balones, juegos de mesa, juegos en general'),(4,'Alimentos y Bebidas','Alimentacion, dulceria, bebidas, paquetes'),(5,'salud y Belleza','Alimentacion, dulceria, bebidas, paquetes'),(6,'Ropa','Vestimenta, ropa, interiores, sombreros, ');
/*!40000 ALTER TABLE `categorias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_venta`
--

DROP TABLE IF EXISTS `detalle_venta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_venta` (
  `idDetalle` int NOT NULL AUTO_INCREMENT,
  `ventaId` int NOT NULL,
  `productoId` int NOT NULL,
  `cantidad` int NOT NULL,
  `precioUnitario` decimal(10,2) NOT NULL,
  PRIMARY KEY (`idDetalle`),
  KEY `fk_venta_detalle` (`ventaId`),
  KEY `fk_producto_detalle` (`productoId`),
  CONSTRAINT `fk_producto_detalle` FOREIGN KEY (`productoId`) REFERENCES `productos` (`idProducto`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_venta_detalle` FOREIGN KEY (`ventaId`) REFERENCES `ventas` (`idVenta`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_venta`
--

LOCK TABLES `detalle_venta` WRITE;
/*!40000 ALTER TABLE `detalle_venta` DISABLE KEYS */;
INSERT INTO `detalle_venta` VALUES (1,47,7,4,2500.00),(2,48,6,1,5000.00),(3,48,11,2,300.00),(4,49,10,1,2500.00),(6,53,4,1,2800.00),(7,53,12,2,800.00),(8,53,15,2,400.00);
/*!40000 ALTER TABLE `detalle_venta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `entradaproductos`
--

DROP TABLE IF EXISTS `entradaproductos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entradaproductos` (
  `idEntrada` int NOT NULL AUTO_INCREMENT,
  `fechaEntrada` date NOT NULL,
  `productoId` int NOT NULL,
  `cantidadComprada` int NOT NULL,
  `precioCompraUnidad` decimal(10,2) NOT NULL,
  `proveedorId` int NOT NULL,
  PRIMARY KEY (`idEntrada`),
  KEY `productoId_idx` (`productoId`),
  KEY `proveedorId_idx` (`proveedorId`),
  CONSTRAINT `producto_Id` FOREIGN KEY (`productoId`) REFERENCES `productos` (`idProducto`),
  CONSTRAINT `proveedorId` FOREIGN KEY (`proveedorId`) REFERENCES `proveedores` (`idProveedor`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entradaproductos`
--

LOCK TABLES `entradaproductos` WRITE;
/*!40000 ALTER TABLE `entradaproductos` DISABLE KEYS */;
INSERT INTO `entradaproductos` VALUES (1,'2024-05-22',6,45,4500.00,2),(2,'2024-05-22',6,40,4500.00,2),(3,'2024-05-22',6,20,2500.00,2),(4,'2024-05-21',6,20,2500.00,2),(5,'2024-05-21',9,25,1900.00,1),(6,'2024-05-19',10,25,2100.00,1),(8,'2024-11-21',1,32,700.00,2);
/*!40000 ALTER TABLE `entradaproductos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos`
--

DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `productos` (
  `idProducto` int NOT NULL AUTO_INCREMENT,
  `nombreProducto` varchar(45) NOT NULL,
  `descripcionProducto` varchar(120) NOT NULL,
  `cantidad` int NOT NULL DEFAULT '0',
  `precioVenta` decimal(10,2) NOT NULL DEFAULT '0.00',
  `precioCompra` decimal(10,2) NOT NULL DEFAULT '0.00',
  `proveedorId` int NOT NULL,
  `CategoriaId` int NOT NULL,
  PRIMARY KEY (`idProducto`),
  KEY `proveedorId_idx` (`proveedorId`),
  KEY `categoriaId_idx` (`CategoriaId`),
  CONSTRAINT `categoriaId` FOREIGN KEY (`CategoriaId`) REFERENCES `categorias` (`idCategoria`) ON UPDATE CASCADE,
  CONSTRAINT `proveedor_Id` FOREIGN KEY (`proveedorId`) REFERENCES `proveedores` (`idProveedor`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES (1,'Lapiz','Lapiz',131,1000.00,650.00,1,1),(2,'borrador','borrador Nata',53,1400.00,850.00,1,1),(4,'marcador','marcador bonito',49,2800.00,2100.00,2,1),(6,'Block Carta','block 100 hojas cuadriculado',147,5000.00,4200.00,1,1),(7,'Palo de balso ','2,5 * 2,5',72,2500.00,3400.00,3,1),(8,'Marcador Permanente','offi-esco',14,3800.00,3100.00,1,1),(9,'Mani Yogurt','Mani con sabor a yogurt',36,3900.00,3000.00,2,1),(10,'Chocomani','mani con chocolate',38,2500.00,2000.00,1,4),(11,'Cartulina','Cartulina de colores pastel',53,300.00,180.00,2,1),(12,'carton paja','carton blanco',20,800.00,550.00,2,1),(13,'Mani salado','Mani con uvas',21,1800.00,1300.00,3,4),(15,'cartulina linda','cartulina linda linda',8,400.00,300.00,6,1);
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proveedores`
--

DROP TABLE IF EXISTS `proveedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `proveedores` (
  `idProveedor` int NOT NULL AUTO_INCREMENT,
  `nombreProveedor` varchar(45) NOT NULL,
  `descripcionProveedor` varchar(120) NOT NULL,
  `direccionProveedor` varchar(45) NOT NULL,
  `telefono` varchar(25) NOT NULL,
  `Correo` varchar(45) NOT NULL,
  `infoAdicional` varchar(225) DEFAULT NULL,
  PRIMARY KEY (`idProveedor`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedores`
--

LOCK TABLES `proveedores` WRITE;
/*!40000 ALTER TABLE `proveedores` DISABLE KEYS */;
INSERT INTO `proveedores` VALUES (1,'Jumbo','Chocolates y galletas','Bogotá, Tangamandapio','3133333331','ejemplo@ejemplo.ejemplo','chocolates de todo tamaños'),(2,'SurtiPapeleria ','surtipapeleria','Cogua, sincelejo, carrera 10','3100023457','surti@papeleria.com','surtidora'),(3,'Surtilider','Chocolates y paquetes','Bogota, Cundinamarca','3133133332','surtilider@surti.com','chocolates y paquetes'),(4,'Claro','Simcards','Bogota','3100100120','claro.simcard@gmail.com','sim cards y demás'),(5,'FomisArt','Figuras en fomi','zipaquirá, cogua','3112423367','fomis@gmail.com.org','Foamies muchos'),(6,'cartulinaslindas','cartulinaslindas muy lindas','cogua','311111111','cartulinaslindas@lindas','cartulinaslindas'),(7,'DistriCogua','Productos coguanos','Cogua con 19','3133347568','districoguanos@cogua.com','coguaaa');
/*!40000 ALTER TABLE `proveedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipousuario`
--

DROP TABLE IF EXISTS `tipousuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipousuario` (
  `idUsuario` int NOT NULL,
  `tipoUsuario` int NOT NULL,
  `descripcionUsuario` varchar(120) NOT NULL,
  PRIMARY KEY (`idUsuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipousuario`
--

LOCK TABLES `tipousuario` WRITE;
/*!40000 ALTER TABLE `tipousuario` DISABLE KEYS */;
INSERT INTO `tipousuario` VALUES (1,0,'Administrador del servicio'),(2,1,'Empleado');
/*!40000 ALTER TABLE `tipousuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario` (
  `idUsuario` int NOT NULL,
  `nombreCompleto` varchar(45) NOT NULL,
  `tipoUsuario` int NOT NULL,
  `nombreUsuario` varchar(25) NOT NULL,
  `contrasenia` varchar(255) DEFAULT NULL,
  `correo` varchar(45) NOT NULL,
  `telefono` varchar(45) NOT NULL,
  `algoritmo_hash` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`idUsuario`),
  KEY `TipoUsuario_idx` (`tipoUsuario`),
  CONSTRAINT `TipoUsuario` FOREIGN KEY (`tipoUsuario`) REFERENCES `tipousuario` (`idUsuario`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (1,'Cristhian Poveda',1,'root','$2y$10$MdEXjR8BkxSDMNC3wDo3ue8tA5G6hEJZUmTcpjFwI.eTFuFBnPV5W','cristhianandreypoveda@gmail.com','3112529978','bcrypt'),(2,'Ronal Salazar',2,'rfsc','$2y$10$PNU3oqyQQiBV3w7A6G1C4eSlpchthx7jZrqfE1EN8zwTl0fypVqT6','ronal.salazarcasas@gmail.com','3175693223','bcrypt');
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ventas`
--

DROP TABLE IF EXISTS `ventas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ventas` (
  `idVenta` int NOT NULL AUTO_INCREMENT,
  `fechaVenta` date NOT NULL,
  `totalVenta` decimal(10,2) NOT NULL,
  `vendedorId` int NOT NULL,
  PRIMARY KEY (`idVenta`),
  KEY `vendedorId_idx` (`vendedorId`),
  CONSTRAINT `vendedorId` FOREIGN KEY (`vendedorId`) REFERENCES `usuario` (`idUsuario`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ventas`
--

LOCK TABLES `ventas` WRITE;
/*!40000 ALTER TABLE `ventas` DISABLE KEYS */;
INSERT INTO `ventas` VALUES (1,'2024-05-23',14000.00,1),(3,'2024-05-28',50000.00,2),(4,'2024-05-25',12000.00,1),(5,'2024-11-18',3600.00,1),(6,'2024-11-18',21600.00,2),(7,'2024-11-16',25000.00,1),(8,'2024-11-16',25000.00,1),(9,'2024-11-20',14000.00,1),(10,'2024-11-21',2000.00,1),(11,'2024-11-21',14000.00,2),(12,'2024-11-22',4200.00,1),(13,'2024-11-22',30800.00,1),(14,'2024-11-22',2000.00,1),(15,'2024-11-22',5600.00,1),(19,'2024-11-22',9600.00,1),(20,'2024-11-22',3600.00,1),(22,'2024-11-22',10000.00,1),(23,'2024-11-22',900.00,1),(24,'2025-05-04',800.00,1),(25,'2025-05-11',25000.00,1),(26,'2025-05-11',10000.00,1),(27,'2025-05-11',11700.00,1),(28,'2025-05-14',7600.00,1),(29,'2025-05-14',20000.00,1),(31,'2025-05-15',50000.00,1),(32,'2025-05-15',30000.00,2),(33,'2025-05-15',7000.00,1),(34,'2025-05-15',1500.00,1),(35,'2025-05-15',2000.00,1),(36,'2025-05-15',3800.00,1),(37,'2025-05-19',1500.00,1),(38,'2025-05-20',3000.00,1),(39,'2025-05-21',9000.00,1),(40,'2025-05-18',5000.00,1),(41,'2025-05-22',1000.00,2),(42,'2025-05-22',7500.00,2),(43,'2025-05-22',15200.00,2),(44,'2025-05-22',3800.00,2),(45,'2025-10-18',11400.00,1),(46,'2025-10-18',3900.00,1),(47,'2025-10-18',10000.00,2),(48,'2025-10-18',5600.00,1),(49,'2025-10-18',2500.00,1),(53,'2025-10-20',5200.00,1);
/*!40000 ALTER TABLE `ventas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'inventario'
--

--
-- Dumping routines for database 'inventario'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-10-20 11:59:27
