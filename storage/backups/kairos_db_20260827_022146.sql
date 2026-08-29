-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: kairos_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

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
-- Table structure for table `administradores`
--

DROP TABLE IF EXISTS `administradores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `administradores` (
  `id_administrador` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `es_principal` tinyint(1) NOT NULL DEFAULT 0,
  `debe_cambiar_password` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_administrador`),
  UNIQUE KEY `uq_administradores_correo` (`correo`),
  KEY `idx_administradores_principal` (`es_principal`,`estado`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `administradores`
--

LOCK TABLES `administradores` WRITE;
/*!40000 ALTER TABLE `administradores` DISABLE KEYS */;
INSERT INTO `administradores` VALUES (3,'URI JOSAFAT','zacgamer1504jahen@gmail.com','$2y$10$IZ/sK1WkgvM7qp9WQuiY9eL.KTAsvMdWGkDGpbmus/b6KHAH78veS','Activo',1,1,'2026-08-23 01:22:31'),(4,'DAVID','admin@gmail.com','$2y$10$MbkqbL2Z9P1iGc83PnxR8u9szgGmsG7pZd7uELKEJWZTc3NLBH8/K','Activo',0,1,'2026-08-23 01:48:51'),(5,'hola','urijosafatmtz@gmail.com','$2y$10$xyPySXRrtIoKkw8bzMCoI.Ybah3gRvEOGKotbs5B/r7Ky6fpTq9cy','Activo',0,1,'2026-08-27 07:19:30'),(6,'hh','urijosafatmtzjahen@gmail.com','$2y$10$OIrH3mO.h3ClXY2vI3Qc5uCs6AEM2WXQo14PZhMIDibqOlTqSlEmS','Activo',0,1,'2026-08-27 07:20:07');
/*!40000 ALTER TABLE `administradores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alumno_tutores`
--

DROP TABLE IF EXISTS `alumno_tutores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alumno_tutores` (
  `id_alumno_tutor` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_alumno` int(10) unsigned NOT NULL,
  `id_tutor` int(10) unsigned NOT NULL,
  `parentesco` varchar(50) DEFAULT NULL,
  `es_principal` tinyint(1) NOT NULL DEFAULT 0,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_alumno_tutor`),
  UNIQUE KEY `uq_alumno_tutor` (`id_alumno`,`id_tutor`),
  KEY `fk_alumno_tutores_tutor` (`id_tutor`),
  CONSTRAINT `fk_alumno_tutores_alumno` FOREIGN KEY (`id_alumno`) REFERENCES `alumnos` (`id_alumno`) ON UPDATE CASCADE,
  CONSTRAINT `fk_alumno_tutores_tutor` FOREIGN KEY (`id_tutor`) REFERENCES `tutores` (`id_tutor`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alumno_tutores`
--

LOCK TABLES `alumno_tutores` WRITE;
/*!40000 ALTER TABLE `alumno_tutores` DISABLE KEYS */;
INSERT INTO `alumno_tutores` VALUES (1,1,1,NULL,1,'Activo','2026-08-24 04:16:32'),(2,5,2,NULL,1,'Activo','2026-08-27 07:01:40'),(3,2,2,NULL,1,'Activo','2026-08-27 07:32:36'),(4,4,3,NULL,1,'Activo','2026-08-27 08:06:44');
/*!40000 ALTER TABLE `alumno_tutores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alumnos`
--

DROP TABLE IF EXISTS `alumnos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alumnos` (
  `id_alumno` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellido_paterno` varchar(50) NOT NULL,
  `apellido_materno` varchar(50) DEFAULT NULL,
  `edad` tinyint(3) unsigned DEFAULT NULL,
  `id_grupo` int(10) unsigned NOT NULL,
  `estado_semaforo` enum('Verde','Amarillo','Rojo') NOT NULL DEFAULT 'Verde',
  `estado` enum('Activo','Inactivo','Trasladado','Egresado') NOT NULL DEFAULT 'Activo',
  `puntos_acumulados` int(10) unsigned NOT NULL DEFAULT 0,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_alumno`),
  KEY `idx_alumnos_grupo` (`id_grupo`),
  CONSTRAINT `fk_alumnos_grupo` FOREIGN KEY (`id_grupo`) REFERENCES `grupos` (`id_grupo`) ON UPDATE CASCADE,
  CONSTRAINT `chk_edad_valida` CHECK (`edad` is null or `edad` between 5 and 18)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alumnos`
--

LOCK TABLES `alumnos` WRITE;
/*!40000 ALTER TABLE `alumnos` DISABLE KEYS */;
INSERT INTO `alumnos` VALUES (1,'fernanda','arroyo','suarez',9,1,'Amarillo','Activo',10,'2026-08-23 02:04:52'),(2,'Uri','Martínez','Jahen',12,1,'Verde','Activo',0,'2026-08-24 05:39:09'),(3,'BLANCA','zagal','paloma',12,2,'Verde','Activo',0,'2026-08-24 05:39:56'),(4,'Pedro','juan','segundo',10,3,'Verde','Activo',0,'2026-08-24 05:41:30'),(5,'DAVID','gienra','lopez',12,1,'Verde','Activo',20,'2026-08-27 07:01:24');
/*!40000 ALTER TABLE `alumnos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bitacora_notas`
--

DROP TABLE IF EXISTS `bitacora_notas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bitacora_notas` (
  `id_nota` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_alumno` int(10) unsigned NOT NULL,
  `id_docente` int(10) unsigned NOT NULL,
  `id_sesion` int(10) unsigned DEFAULT NULL,
  `tipo_nota` enum('Observacion','Incidencia','Logro','Medalla') NOT NULL DEFAULT 'Observacion',
  `emocion` varchar(80) DEFAULT NULL,
  `nota_descripcion` text NOT NULL,
  `accion_contencion` varchar(150) DEFAULT NULL,
  `id_recompensa` int(10) unsigned DEFAULT NULL,
  `puntos_otorgados` int(10) unsigned NOT NULL DEFAULT 0,
  `notificado_al_tutor` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_nota`),
  KEY `idx_bitacora_alumno_fecha` (`id_alumno`,`fecha_hora`),
  KEY `idx_bitacora_docente_fecha` (`id_docente`,`fecha_hora`),
  KEY `fk_bitacora_sesion` (`id_sesion`),
  KEY `fk_bitacora_recompensa` (`id_recompensa`),
  CONSTRAINT `fk_bitacora_alumno` FOREIGN KEY (`id_alumno`) REFERENCES `alumnos` (`id_alumno`) ON UPDATE CASCADE,
  CONSTRAINT `fk_bitacora_docente` FOREIGN KEY (`id_docente`) REFERENCES `docentes` (`id_docente`) ON UPDATE CASCADE,
  CONSTRAINT `fk_bitacora_recompensa` FOREIGN KEY (`id_recompensa`) REFERENCES `recompensas_catalogo` (`id_recompensa`) ON UPDATE CASCADE,
  CONSTRAINT `fk_bitacora_sesion` FOREIGN KEY (`id_sesion`) REFERENCES `sesiones_temporizador` (`id_sesion`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `chk_medalla_bitacora` CHECK (`tipo_nota` = 'Medalla' and `id_recompensa` is not null and `puntos_otorgados` > 0 or `tipo_nota` <> 'Medalla' and `id_recompensa` is null and `puntos_otorgados` = 0)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bitacora_notas`
--

LOCK TABLES `bitacora_notas` WRITE;
/*!40000 ALTER TABLE `bitacora_notas` DISABLE KEYS */;
INSERT INTO `bitacora_notas` VALUES (1,1,1,NULL,'Incidencia','Irritabilidad','se emputo','Pausa Activa',NULL,0,0,'2026-08-23 22:40:42'),(2,1,1,NULL,'Incidencia','Irritabilidad','se emputo','Pausa Activa',NULL,0,0,'2026-08-23 22:56:07'),(3,1,1,NULL,'Incidencia','Irritabilidad','se emputo','Pausa Activa',NULL,0,0,'2026-08-23 23:26:16'),(4,1,1,NULL,'Incidencia','Irritabilidad','xcfghjklñ{','Lectura de Reflexión',NULL,0,0,'2026-08-23 23:29:37'),(5,1,1,NULL,'Incidencia','Irritabilidad','xcfghjklñ{','Lectura de Reflexión',NULL,0,0,'2026-08-23 23:29:50'),(6,1,1,NULL,'Incidencia','Ansiedad','se emputa','Pausa de Respiración',NULL,0,0,'2026-08-25 18:39:44'),(7,1,1,NULL,'Medalla',NULL,'todo bien',NULL,1,10,0,'2026-08-25 18:44:39'),(8,5,1,NULL,'Medalla',NULL,'va chido',NULL,1,10,1,'2026-08-27 01:03:08'),(9,5,1,NULL,'Medalla',NULL,'va chido',NULL,1,10,1,'2026-08-27 01:03:10');
/*!40000 ALTER TABLE `bitacora_notas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bitacora_respaldos`
--

DROP TABLE IF EXISTS `bitacora_respaldos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bitacora_respaldos` (
  `id_respaldo` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre_archivo` varchar(150) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `tamano_bytes` bigint(20) unsigned NOT NULL,
  `id_administrador` int(10) unsigned NOT NULL,
  `fecha_ejecucion` datetime NOT NULL DEFAULT current_timestamp(),
  `tipo_operacion` enum('Respaldo','Restauracion') NOT NULL,
  `resultado` enum('Exitoso','Fallido') NOT NULL DEFAULT 'Exitoso',
  `mensaje` text DEFAULT NULL,
  PRIMARY KEY (`id_respaldo`),
  KEY `fk_respaldos_admin` (`id_administrador`),
  CONSTRAINT `fk_respaldos_admin` FOREIGN KEY (`id_administrador`) REFERENCES `administradores` (`id_administrador`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bitacora_respaldos`
--

LOCK TABLES `bitacora_respaldos` WRITE;
/*!40000 ALTER TABLE `bitacora_respaldos` DISABLE KEYS */;
/*!40000 ALTER TABLE `bitacora_respaldos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `consejos_hogar`
--

DROP TABLE IF EXISTS `consejos_hogar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consejos_hogar` (
  `id_consejo` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(150) NOT NULL,
  `recomendacion` text NOT NULL,
  `categoria` varchar(100) NOT NULL DEFAULT 'Apoyo Emocional',
  `id_docente_autor` int(10) unsigned NOT NULL,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `fecha_publicacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_consejo`),
  KEY `fk_consejos_docente` (`id_docente_autor`),
  CONSTRAINT `fk_consejos_docente` FOREIGN KEY (`id_docente_autor`) REFERENCES `docentes` (`id_docente`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consejos_hogar`
--

LOCK TABLES `consejos_hogar` WRITE;
/*!40000 ALTER TABLE `consejos_hogar` DISABLE KEYS */;
/*!40000 ALTER TABLE `consejos_hogar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `docente_grupos`
--

DROP TABLE IF EXISTS `docente_grupos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `docente_grupos` (
  `id_docente_grupo` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_docente` int(10) unsigned NOT NULL,
  `id_grupo` int(10) unsigned NOT NULL,
  `ciclo_escolar` varchar(20) NOT NULL,
  `es_titular` tinyint(1) NOT NULL DEFAULT 0,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `fecha_asignacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_docente_grupo`),
  UNIQUE KEY `uq_docente_grupo_ciclo` (`id_docente`,`id_grupo`,`ciclo_escolar`),
  KEY `fk_docente_grupos_grupo` (`id_grupo`),
  CONSTRAINT `fk_docente_grupos_docente` FOREIGN KEY (`id_docente`) REFERENCES `docentes` (`id_docente`) ON UPDATE CASCADE,
  CONSTRAINT `fk_docente_grupos_grupo` FOREIGN KEY (`id_grupo`) REFERENCES `grupos` (`id_grupo`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `docente_grupos`
--

LOCK TABLES `docente_grupos` WRITE;
/*!40000 ALTER TABLE `docente_grupos` DISABLE KEYS */;
INSERT INTO `docente_grupos` VALUES (1,1,1,'2026-2027',1,'Activo','2026-08-24 04:24:19'),(2,1,3,'2026-2027',0,'Activo','2026-08-24 05:40:19'),(3,2,2,'2026-2027',1,'Activo','2026-08-27 08:05:40');
/*!40000 ALTER TABLE `docente_grupos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `docentes`
--

DROP TABLE IF EXISTS `docentes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `docentes` (
  `id_docente` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_docente`),
  UNIQUE KEY `uq_docentes_correo` (`correo`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `docentes`
--

LOCK TABLES `docentes` WRITE;
/*!40000 ALTER TABLE `docentes` DISABLE KEYS */;
INSERT INTO `docentes` VALUES (1,'BLANCA PAMELA','maestro@gmail.com','$2y$10$atZIX5iHO/jIcq6q263rjuyi/AS5B4zSJKFN/wCFdBG7CvG.V9pqm','845-784-8565','Activo','2026-08-23 01:47:35','2026-08-23 01:47:35'),(2,'hh','nohay1432@gmail.com','$2y$10$PoC/cm/2YKhI/.hm5tMzquhQUNngbegQVKpgUfeXF8dBcwgb2qkVS','456-481-4512','Activo','2026-08-27 07:24:26','2026-08-27 07:24:26');
/*!40000 ALTER TABLE `docentes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grupos`
--

DROP TABLE IF EXISTS `grupos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grupos` (
  `id_grupo` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `grado` tinyint(3) unsigned NOT NULL,
  `grupo` char(1) NOT NULL,
  `ciclo_escolar` varchar(20) NOT NULL,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  PRIMARY KEY (`id_grupo`),
  UNIQUE KEY `uq_grupo_ciclo` (`grado`,`grupo`,`ciclo_escolar`),
  CONSTRAINT `chk_grado_valido` CHECK (`grado` between 1 and 6)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grupos`
--

LOCK TABLES `grupos` WRITE;
/*!40000 ALTER TABLE `grupos` DISABLE KEYS */;
INSERT INTO `grupos` VALUES (1,6,'A','2026-2027','Activo'),(2,5,'B','2026-2027','Activo'),(3,6,'C','2026-2027','Activo');
/*!40000 ALTER TABLE `grupos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lecturas_cuentos`
--

DROP TABLE IF EXISTS `lecturas_cuentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lecturas_cuentos` (
  `id_lectura` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(150) NOT NULL,
  `contenido` text NOT NULL,
  `categoria_tematica` varchar(100) NOT NULL,
  `tiempo_estimado_min` smallint(5) unsigned NOT NULL,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `id_administrador` int(10) unsigned NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_lectura`),
  KEY `fk_lecturas_admin` (`id_administrador`),
  CONSTRAINT `fk_lecturas_admin` FOREIGN KEY (`id_administrador`) REFERENCES `administradores` (`id_administrador`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lecturas_cuentos`
--

LOCK TABLES `lecturas_cuentos` WRITE;
/*!40000 ALTER TABLE `lecturas_cuentos` DISABLE KEYS */;
/*!40000 ALTER TABLE `lecturas_cuentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recompensas_catalogo`
--

DROP TABLE IF EXISTS `recompensas_catalogo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recompensas_catalogo` (
  `id_recompensa` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre_insignia` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `puntos_otorgados` int(10) unsigned NOT NULL DEFAULT 10,
  `icono_url` varchar(255) DEFAULT NULL,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `id_administrador` int(10) unsigned NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_recompensa`),
  UNIQUE KEY `uq_recompensa_nombre` (`nombre_insignia`),
  KEY `fk_recompensas_admin` (`id_administrador`),
  CONSTRAINT `fk_recompensas_admin` FOREIGN KEY (`id_administrador`) REFERENCES `administradores` (`id_administrador`) ON UPDATE CASCADE,
  CONSTRAINT `chk_puntos_recompensa` CHECK (`puntos_otorgados` > 0)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recompensas_catalogo`
--

LOCK TABLES `recompensas_catalogo` WRITE;
/*!40000 ALTER TABLE `recompensas_catalogo` DISABLE KEYS */;
INSERT INTO `recompensas_catalogo` VALUES (1,'Paciencia','El alumno se toma el tiempo necesario duarante una actividad',10,'⭐','Activo',3,'2026-08-26 00:44:23');
/*!40000 ALTER TABLE `recompensas_catalogo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sesiones_temporizador`
--

DROP TABLE IF EXISTS `sesiones_temporizador`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesiones_temporizador` (
  `id_sesion` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `id_docente` int(10) unsigned NOT NULL,
  `id_grupo` int(10) unsigned NOT NULL,
  `id_video` int(10) unsigned DEFAULT NULL,
  `id_lectura` int(10) unsigned DEFAULT NULL,
  `duracion_clase_min` smallint(5) unsigned NOT NULL,
  `nivel_irritabilidad` enum('Bajo','Medio','Alto') NOT NULL,
  `estado` enum('Finalizado','En curso','Interrumpido') NOT NULL DEFAULT 'En curso',
  `fecha_inicio` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_fin` datetime DEFAULT NULL,
  PRIMARY KEY (`id_sesion`),
  KEY `idx_sesiones_docente_fecha` (`id_docente`,`fecha`),
  KEY `idx_sesiones_grupo_fecha` (`id_grupo`,`fecha`),
  KEY `fk_sesiones_video` (`id_video`),
  KEY `fk_sesiones_lectura` (`id_lectura`),
  CONSTRAINT `fk_sesiones_docente` FOREIGN KEY (`id_docente`) REFERENCES `docentes` (`id_docente`) ON UPDATE CASCADE,
  CONSTRAINT `fk_sesiones_grupo` FOREIGN KEY (`id_grupo`) REFERENCES `grupos` (`id_grupo`) ON UPDATE CASCADE,
  CONSTRAINT `fk_sesiones_lectura` FOREIGN KEY (`id_lectura`) REFERENCES `lecturas_cuentos` (`id_lectura`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_sesiones_video` FOREIGN KEY (`id_video`) REFERENCES `videos_pausas_activas` (`id_video`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sesiones_temporizador`
--

LOCK TABLES `sesiones_temporizador` WRITE;
/*!40000 ALTER TABLE `sesiones_temporizador` DISABLE KEYS */;
INSERT INTO `sesiones_temporizador` VALUES (1,'2026-08-23',1,1,NULL,NULL,1,'Bajo','Finalizado','2026-08-23 23:36:50','2026-08-23 23:37:50'),(2,'2026-08-25',1,1,NULL,NULL,1,'Medio','Interrumpido','2026-08-25 23:05:22','2026-08-25 23:05:48'),(3,'2026-08-25',1,1,NULL,NULL,1,'Medio','Interrumpido','2026-08-25 23:05:49','2026-08-25 23:07:09'),(4,'2026-08-25',1,1,NULL,NULL,40,'Medio','Interrumpido','2026-08-25 23:07:09','2026-08-25 23:07:11'),(5,'2026-08-25',1,1,NULL,NULL,1,'Medio','Interrumpido','2026-08-25 23:12:07','2026-08-25 23:12:53'),(6,'2026-08-25',1,1,NULL,NULL,1,'Medio','Interrumpido','2026-08-25 23:12:54','2026-08-25 23:13:36'),(7,'2026-08-25',1,1,NULL,NULL,1,'Medio','Finalizado','2026-08-25 23:13:37','2026-08-25 23:14:38'),(8,'2026-08-25',1,1,NULL,NULL,1,'Medio','Finalizado','2026-08-25 23:19:41','2026-08-25 23:20:41'),(9,'2026-08-25',1,1,NULL,NULL,1,'Medio','Finalizado','2026-08-25 23:23:00','2026-08-25 23:24:00'),(10,'2026-08-25',1,1,NULL,NULL,1,'Medio','Finalizado','2026-08-25 23:24:42','2026-08-25 23:25:43');
/*!40000 ALTER TABLE `sesiones_temporizador` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tokens_recuperacion`
--

DROP TABLE IF EXISTS `tokens_recuperacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tokens_recuperacion` (
  `id_token` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tipo_usuario` enum('administrador','maestro','tutor') NOT NULL,
  `id_usuario` int(10) unsigned NOT NULL,
  `token_hash` char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `expira_at` datetime NOT NULL,
  `usado_at` datetime DEFAULT NULL,
  `creado_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_token`),
  UNIQUE KEY `uq_tokens_recuperacion_hash` (`token_hash`),
  KEY `idx_tokens_recuperacion_usuario` (`tipo_usuario`,`id_usuario`),
  KEY `idx_tokens_recuperacion_expira` (`expira_at`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tokens_recuperacion`
--

LOCK TABLES `tokens_recuperacion` WRITE;
/*!40000 ALTER TABLE `tokens_recuperacion` DISABLE KEYS */;
INSERT INTO `tokens_recuperacion` VALUES (1,'tutor',2,'36ba66930a3914f06718a8062dfb96d49b5bac97485f5ed22cb9f4896d92ab09','2026-08-27 10:04:11','2026-08-27 01:04:35','2026-08-27 01:04:11'),(2,'tutor',2,'62e76bfeeed035ae538bb9d427909c96883af3dbfd6e1d37d83ca4968f4c233b','2026-08-27 10:04:54','2026-08-27 01:05:19','2026-08-27 01:04:54');
/*!40000 ALTER TABLE `tokens_recuperacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tutores`
--

DROP TABLE IF EXISTS `tutores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tutores` (
  `id_tutor` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `cargo` varchar(100) NOT NULL DEFAULT 'Padre o tutor',
  `correo` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_tutor`),
  UNIQUE KEY `uq_tutores_correo` (`correo`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tutores`
--

LOCK TABLES `tutores` WRITE;
/*!40000 ALTER TABLE `tutores` DISABLE KEYS */;
INSERT INTO `tutores` VALUES (1,'Uri','tutor','tutor@gmail.com','$2y$10$sDCVROhwIkx.pRn9VLSjn.nUoBwH0EP//wlNFPpHOB3nf3ipdH.kG','456-481-4512','Activo','2026-08-23 01:50:05'),(2,'BLANCA PAMELA','Tutor','dzbo230089@upemor.edu.mx','$2y$10$P7CAyzAd70O0653V3gnrPuHrKxkeJ3wXi26wOh0tUKsNOgJdFAVtu','845-784-8565','Activo','2026-08-27 07:01:09'),(3,'sii','Tutor','nohay1967@gmail.com','$2y$10$0WlYj1mGsCW2ay9rdyc2hukiZx0driFAZpixpJaNgISOR/0rLCMEm','845-784-8565','Activo','2026-08-27 07:22:51');
/*!40000 ALTER TABLE `tutores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `videos_pausas_activas`
--

DROP TABLE IF EXISTS `videos_pausas_activas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `videos_pausas_activas` (
  `id_video` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(150) NOT NULL,
  `url_youtube` varchar(255) NOT NULL,
  `duracion_segundos` smallint(5) unsigned NOT NULL,
  `categoria` varchar(100) NOT NULL DEFAULT 'Relajación',
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `id_administrador` int(10) unsigned NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_video`),
  UNIQUE KEY `uq_video_url` (`url_youtube`),
  KEY `fk_videos_admin` (`id_administrador`),
  CONSTRAINT `fk_videos_admin` FOREIGN KEY (`id_administrador`) REFERENCES `administradores` (`id_administrador`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `videos_pausas_activas`
--

LOCK TABLES `videos_pausas_activas` WRITE;
/*!40000 ALTER TABLE `videos_pausas_activas` DISABLE KEYS */;
INSERT INTO `videos_pausas_activas` VALUES (1,'Video de estiramiento de prueba','https://www.youtube.com/watch?v=wUxWiu5Veg8',450,'Relajación','Activo',3,'2026-08-24 06:38:15');
/*!40000 ALTER TABLE `videos_pausas_activas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `vw_bitacora_alumnos`
--

DROP TABLE IF EXISTS `vw_bitacora_alumnos`;
/*!50001 DROP VIEW IF EXISTS `vw_bitacora_alumnos`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `vw_bitacora_alumnos` AS SELECT
 1 AS `id_nota`,
  1 AS `fecha_hora`,
  1 AS `tipo_nota`,
  1 AS `id_alumno`,
  1 AS `alumno`,
  1 AS `id_docente`,
  1 AS `docente`,
  1 AS `emocion`,
  1 AS `nota_descripcion`,
  1 AS `accion_contencion`,
  1 AS `nombre_insignia`,
  1 AS `puntos_otorgados`,
  1 AS `notificado_al_tutor` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `vw_medallas_alumnos`
--

DROP TABLE IF EXISTS `vw_medallas_alumnos`;
/*!50001 DROP VIEW IF EXISTS `vw_medallas_alumnos`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `vw_medallas_alumnos` AS SELECT
 1 AS `id_medalla`,
  1 AS `id_alumno`,
  1 AS `alumno`,
  1 AS `id_recompensa`,
  1 AS `nombre_insignia`,
  1 AS `descripcion`,
  1 AS `puntos_otorgados`,
  1 AS `fecha_hora`,
  1 AS `id_docente`,
  1 AS `docente`,
  1 AS `notificado_al_tutor` */;
SET character_set_client = @saved_cs_client;

--
-- Dumping events for database 'kairos_db'
--

--
-- Dumping routines for database 'kairos_db'
--
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `crear_administrador_principal` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `crear_administrador_principal`(
  IN p_nombre VARCHAR(100),
  IN p_correo VARCHAR(150),
  IN p_password_hash VARCHAR(255)
)
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM administradores WHERE es_principal = 1
  ) THEN
    INSERT INTO administradores
      (nombre, correo, password_hash, estado, es_principal, debe_cambiar_password)
    VALUES
      (p_nombre, p_correo, p_password_hash, 'Activo', 1, 1);
  END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Final view structure for view `vw_bitacora_alumnos`
--

/*!50001 DROP VIEW IF EXISTS `vw_bitacora_alumnos`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_bitacora_alumnos` AS select `b`.`id_nota` AS `id_nota`,`b`.`fecha_hora` AS `fecha_hora`,`b`.`tipo_nota` AS `tipo_nota`,`b`.`id_alumno` AS `id_alumno`,concat(`a`.`nombre`,' ',`a`.`apellido_paterno`,' ',coalesce(`a`.`apellido_materno`,'')) AS `alumno`,`b`.`id_docente` AS `id_docente`,`d`.`nombre` AS `docente`,`b`.`emocion` AS `emocion`,`b`.`nota_descripcion` AS `nota_descripcion`,`b`.`accion_contencion` AS `accion_contencion`,`r`.`nombre_insignia` AS `nombre_insignia`,`b`.`puntos_otorgados` AS `puntos_otorgados`,`b`.`notificado_al_tutor` AS `notificado_al_tutor` from (((`bitacora_notas` `b` join `alumnos` `a` on(`a`.`id_alumno` = `b`.`id_alumno`)) join `docentes` `d` on(`d`.`id_docente` = `b`.`id_docente`)) left join `recompensas_catalogo` `r` on(`r`.`id_recompensa` = `b`.`id_recompensa`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `vw_medallas_alumnos`
--

/*!50001 DROP VIEW IF EXISTS `vw_medallas_alumnos`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_medallas_alumnos` AS select `b`.`id_nota` AS `id_medalla`,`b`.`id_alumno` AS `id_alumno`,concat(`a`.`nombre`,' ',`a`.`apellido_paterno`,' ',coalesce(`a`.`apellido_materno`,'')) AS `alumno`,`r`.`id_recompensa` AS `id_recompensa`,`r`.`nombre_insignia` AS `nombre_insignia`,`r`.`descripcion` AS `descripcion`,`b`.`puntos_otorgados` AS `puntos_otorgados`,`b`.`fecha_hora` AS `fecha_hora`,`b`.`id_docente` AS `id_docente`,`d`.`nombre` AS `docente`,`b`.`notificado_al_tutor` AS `notificado_al_tutor` from (((`bitacora_notas` `b` join `alumnos` `a` on(`a`.`id_alumno` = `b`.`id_alumno`)) join `recompensas_catalogo` `r` on(`r`.`id_recompensa` = `b`.`id_recompensa`)) join `docentes` `d` on(`d`.`id_docente` = `b`.`id_docente`)) where `b`.`tipo_nota` = 'Medalla' */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-27  2:21:46
