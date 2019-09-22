-- MySQL dump 10.16  Distrib 10.2.25-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: nestulov
-- ------------------------------------------------------
-- Server version	10.2.25-MariaDB-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `answer`
--

DROP TABLE IF EXISTS `answer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `answer` (
  `answer_id` int(11) NOT NULL AUTO_INCREMENT,
  `assignment_id` int(11) NOT NULL,
  `answer` varchar(255) DEFAULT NULL,
  `answer_provided` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`answer_id`),
  KEY `fk_solution_assignment1_idx` (`assignment_id`),
  CONSTRAINT `fk_solution_assignment1` FOREIGN KEY (`assignment_id`) REFERENCES `assignment` (`assignment_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `answer`
--

LOCK TABLES `answer` WRITE;
/*!40000 ALTER TABLE `answer` DISABLE KEYS */;
INSERT INTO `answer` VALUES (10,89,'36174','2019-06-15 09:29:31'),(11,89,'36714','2019-06-15 09:29:49'),(12,90,'2ррв6','2019-06-16 13:30:57');
/*!40000 ALTER TABLE `answer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assignment`
--

DROP TABLE IF EXISTS `assignment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assignment` (
  `assignment_id` int(11) NOT NULL AUTO_INCREMENT,
  `problem_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `assignment_last_answer` varchar(255) DEFAULT NULL,
  `assigned` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `correct_answer_provided` tinyint(1) NOT NULL DEFAULT 0,
  `correct_answer_requested` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`assignment_id`),
  KEY `fk_assignment_problem1_idx` (`problem_id`),
  KEY `fk_assignment_user1_idx` (`user_id`),
  CONSTRAINT `fk_assignment_problem1` FOREIGN KEY (`problem_id`) REFERENCES `problem` (`problem_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_assignment_user1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assignment`
--

LOCK TABLES `assignment` WRITE;
/*!40000 ALTER TABLE `assignment` DISABLE KEYS */;
INSERT INTO `assignment` VALUES (89,72,53,'36714','2019-06-15 09:29:49',1,0),(90,61,53,'89cd','2019-06-16 13:31:43',0,1);
/*!40000 ALTER TABLE `assignment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam`
--

DROP TABLE IF EXISTS `exam`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam` (
  `exam_id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_name` varchar(255) NOT NULL,
  `exam_desc` int(11) NOT NULL,
  PRIMARY KEY (`exam_id`),
  UNIQUE KEY `exam_name_UNIQUE` (`exam_name`),
  KEY `fk_exam_desc_idx` (`exam_desc`),
  CONSTRAINT `fk_exam_desc` FOREIGN KEY (`exam_desc`) REFERENCES `resource_collection` (`resource_collection_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam`
--

LOCK TABLES `exam` WRITE;
/*!40000 ALTER TABLE `exam` DISABLE KEYS */;
INSERT INTO `exam` VALUES (1,'ЕГЭ',1),(2,'ОГЭ',1);
/*!40000 ALTER TABLE `exam` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam_item`
--

DROP TABLE IF EXISTS `exam_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_item` (
  `exam_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_item_number` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `exam_item_desc` int(11) NOT NULL,
  PRIMARY KEY (`exam_item_id`),
  KEY `fk_exam_idx` (`exam_id`),
  KEY `fk_exam_item_desc_idx` (`exam_item_desc`),
  CONSTRAINT `fk_exam_id` FOREIGN KEY (`exam_id`) REFERENCES `exam` (`exam_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_exam_item_desc` FOREIGN KEY (`exam_item_desc`) REFERENCES `resource_collection` (`resource_collection_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_item`
--

LOCK TABLES `exam_item` WRITE;
/*!40000 ALTER TABLE `exam_item` DISABLE KEYS */;
INSERT INTO `exam_item` VALUES (1,1,1,1),(2,2,1,1),(3,3,1,1),(4,4,1,1),(5,5,1,1),(6,6,1,1),(7,7,1,1),(8,8,1,1),(9,9,1,1),(10,10,1,1),(11,11,1,1),(12,12,1,1),(13,13,1,1),(14,14,1,1),(15,15,1,1),(16,16,1,1),(17,17,1,1),(18,18,1,1),(19,19,1,1),(20,20,1,1),(21,21,1,1),(22,22,1,1),(23,23,1,1);
/*!40000 ALTER TABLE `exam_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `input`
--

DROP TABLE IF EXISTS `input`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `input` (
  `input_id` int(11) NOT NULL AUTO_INCREMENT,
  `variant_id` int(11) NOT NULL,
  `input` varchar(255) NOT NULL,
  `answer` varchar(255) NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`input_id`),
  KEY `fk_input_variant` (`variant_id`),
  KEY `input_idx` (`input_id`),
  CONSTRAINT `fk_input_variant` FOREIGN KEY (`variant_id`) REFERENCES `variant` (`variant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `input`
--

LOCK TABLES `input` WRITE;
/*!40000 ALTER TABLE `input` DISABLE KEYS */;
INSERT INTO `input` VALUES (1,1,'О,В,Д,П,А 0,1,2,3,4 ВОДОПАД шестнадцатеричным','22162',1),(2,1,'Д,Х,Р,О,В 0,1,2,3,4 ХОРОВОД восьмеричным','36714',1),(3,1,'О,К,Г,Д,Р 0,1,2,3,4 ГОРОДОК восьмеричным','42061',1),(4,1,'Х,Е,Л,О,Д 0,1,2,3,4 ЛЕДОХОД шестнадцатеричным','999C',1),(5,1,'И,Д,Т,О,Х 0,1,2,3,4 ТИХОХОД шестнадцатеричным','89CD',1),(6,2,'А,Б,В,Г А-10,Б-11,В-110,Г-0 ВБГАГВ шестнадцатеричный','5B1A',1),(7,2,'А,Б,В,Г А-10,Б-11,В-110,Г-0 ВАГБААГВ шестнадцатеричный','D3A6',1),(8,2,'А,Б,В,Г А-0,Б-11,В-100,Г-011 ГБАВАВГ восьмеричный','151646',1),(9,2,'А,Б,В,Г А-00,Б-10,В-010,Г-101 БАБВГВ шестнадцатеричный','44AA',1),(10,3,'10101010_2-252_8+7_{16}','7',1),(11,3,'10101010_2-250_8+7_{16}','9',1),(12,3,'10101011_2-250_8+7_{16}','8',1),(13,3,'10101110_2-256_8+A_{16}','10',1),(14,3,'B9_{16}-271_8','0',1),(15,3,'253_8-AB_{16}','0',1);
/*!40000 ALTER TABLE `input` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `problem`
--

DROP TABLE IF EXISTS `problem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `problem` (
  `problem_id` int(11) NOT NULL AUTO_INCREMENT,
  `problem_statement` int(11) NOT NULL,
  `problem_answer` varchar(255) NOT NULL,
  `problem_solution` int(11) NOT NULL,
  `problem_created` timestamp NULL DEFAULT NULL,
  `problem_modified` timestamp NULL DEFAULT NULL,
  `problem_type_id` int(11) NOT NULL,
  `exam_item_id` int(11) NOT NULL,
  `problem_year` year(4) NOT NULL,
  PRIMARY KEY (`problem_id`),
  KEY `fk_problem_type_idx` (`problem_type_id`),
  KEY `fk_problem_statement_idx` (`problem_statement`),
  KEY `fk_problem_solution_idx` (`problem_solution`),
  KEY `fk_exam_item_idx` (`exam_item_id`),
  CONSTRAINT `fk_exam_item` FOREIGN KEY (`exam_item_id`) REFERENCES `exam_item` (`exam_item_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_problem_type` FOREIGN KEY (`problem_type_id`) REFERENCES `problem_type` (`problem_type_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_solution` FOREIGN KEY (`problem_solution`) REFERENCES `resource_collection` (`resource_collection_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_statement` FOREIGN KEY (`problem_statement`) REFERENCES `resource_collection` (`resource_collection_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `problem`
--

LOCK TABLES `problem` WRITE;
/*!40000 ALTER TABLE `problem` DISABLE KEYS */;
INSERT INTO `problem` VALUES (57,95,'22162',94,'2019-05-22 10:17:31','2019-05-22 10:17:31',1,5,2019),(59,97,'42061',94,'2019-05-22 10:17:39','2019-05-22 10:17:39',1,5,2019),(60,98,'999C',94,'2019-05-22 10:17:42','2019-05-22 10:17:42',1,5,2019),(61,99,'89CD',94,'2019-05-22 10:17:46','2019-05-22 10:17:46',1,5,2019),(62,101,'5B1A',100,'2019-05-22 10:17:54','2019-05-22 10:17:54',1,5,2018),(63,102,'D3A6',100,'2019-05-22 10:17:57','2019-05-22 10:17:57',1,5,2018),(64,103,'151646',100,'2019-05-22 10:18:01','2019-05-22 10:18:01',1,5,2018),(65,104,'44AA',100,'2019-05-22 10:18:05','2019-05-22 10:18:05',1,5,2018),(66,106,'7',105,'2019-05-22 10:18:12','2019-05-22 10:18:12',4,1,2016),(67,107,'9',105,'2019-05-22 10:18:17','2019-05-22 10:18:17',4,1,2016),(68,108,'8',105,'2019-05-22 10:18:21','2019-05-22 10:18:21',4,1,2016),(69,109,'10',105,'2019-05-22 10:18:25','2019-05-22 10:18:25',4,1,2016),(70,110,'0',105,'2019-05-22 10:18:29','2019-05-22 10:18:29',4,1,2016),(71,111,'0',105,'2019-05-22 10:18:33','2019-05-22 10:18:33',4,1,2016),(72,112,'36714',94,'2019-06-15 09:26:23','2019-06-15 09:26:23',1,5,2019);
/*!40000 ALTER TABLE `problem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `problem_type`
--

DROP TABLE IF EXISTS `problem_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `problem_type` (
  `problem_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `problem_type_desc_id` int(11) NOT NULL,
  `problem_type_code` varchar(255) NOT NULL,
  PRIMARY KEY (`problem_type_id`),
  KEY `fk_problem_type_desc_idx` (`problem_type_desc_id`),
  CONSTRAINT `fk_problem_type_desc` FOREIGN KEY (`problem_type_desc_id`) REFERENCES `resource_collection` (`resource_collection_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `problem_type`
--

LOCK TABLES `problem_type` WRITE;
/*!40000 ALTER TABLE `problem_type` DISABLE KEYS */;
INSERT INTO `problem_type` VALUES (1,1,'кодирование_информации'),(2,1,'робот'),(3,1,'передача_информации'),(4,1,'системы_счисления'),(5,1,'поиск_маршрута_по_расписанию');
/*!40000 ALTER TABLE `problem_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resource`
--

DROP TABLE IF EXISTS `resource`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resource` (
  `resource_id` int(11) NOT NULL AUTO_INCREMENT,
  `resource_collection_id` int(11) NOT NULL,
  `resource_name` varchar(255) DEFAULT NULL,
  `resource_content` text DEFAULT NULL,
  `resource_type_id` int(11) NOT NULL,
  PRIMARY KEY (`resource_id`),
  KEY `fk_resource_collection_idx` (`resource_collection_id`),
  KEY `fk_resource_type_idx` (`resource_type_id`),
  CONSTRAINT `fk_resource_collection` FOREIGN KEY (`resource_collection_id`) REFERENCES `resource_collection` (`resource_collection_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_resource_type` FOREIGN KEY (`resource_type_id`) REFERENCES `resource_type` (`resource_type_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=254 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resource`
--

LOCK TABLES `resource` WRITE;
/*!40000 ALTER TABLE `resource` DISABLE KEYS */;
INSERT INTO `resource` VALUES (216,94,'1.pdf','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/1.pdf',2),(217,94,'1(1).png','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/1(1).png',1),(218,95,'1-1.pdf','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/1-1.pdf',2),(219,95,'1-1(1).png','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/1-1(1).png',1),(222,97,'1-3.pdf','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/1-3.pdf',2),(223,97,'1-3(1).png','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/1-3(1).png',1),(224,98,'1-4.pdf','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/1-4.pdf',2),(225,98,'1-4(1).png','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/1-4(1).png',1),(226,99,'1-5.pdf','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/1-5.pdf',2),(227,99,'1-5(1).png','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/1-5(1).png',1),(228,100,'2.pdf','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/2.pdf',2),(229,100,'2(1).png','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/2(1).png',1),(230,101,'2-6.pdf','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/2-6.pdf',2),(231,101,'2-6(1).png','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/2-6(1).png',1),(232,102,'2-7.pdf','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/2-7.pdf',2),(233,102,'2-7(1).png','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/2-7(1).png',1),(234,103,'2-8.pdf','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/2-8.pdf',2),(235,103,'2-8(1).png','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/2-8(1).png',1),(236,104,'2-9.pdf','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/2-9.pdf',2),(237,104,'2-9(1).png','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/2-9(1).png',1),(238,105,'3.pdf','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/3.pdf',2),(239,105,'3(1).png','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/3(1).png',1),(240,106,'3-10.pdf','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/3-10.pdf',2),(241,106,'3-10(1).png','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/3-10(1).png',1),(242,107,'3-11.pdf','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/3-11.pdf',2),(243,107,'3-11(1).png','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/3-11(1).png',1),(244,108,'3-12.pdf','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/3-12.pdf',2),(245,108,'3-12(1).png','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/3-12(1).png',1),(246,109,'3-13.pdf','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/3-13.pdf',2),(247,109,'3-13(1).png','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/3-13(1).png',1),(248,110,'3-14.pdf','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/3-14.pdf',2),(249,110,'3-14(1).png','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/3-14(1).png',1),(250,111,'3-15.pdf','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/3-15.pdf',2),(251,111,'3-15(1).png','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/3-15(1).png',1),(252,112,'1-2.pdf','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/1-2.pdf',2),(253,112,'1-2(1).png','http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/resources/1-2(1).png',1);
/*!40000 ALTER TABLE `resource` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resource_collection`
--

DROP TABLE IF EXISTS `resource_collection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resource_collection` (
  `resource_collection_id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`resource_collection_id`)
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resource_collection`
--

LOCK TABLES `resource_collection` WRITE;
/*!40000 ALTER TABLE `resource_collection` DISABLE KEYS */;
INSERT INTO `resource_collection` VALUES (1),(94),(95),(96),(97),(98),(99),(100),(101),(102),(103),(104),(105),(106),(107),(108),(109),(110),(111),(112);
/*!40000 ALTER TABLE `resource_collection` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resource_type`
--

DROP TABLE IF EXISTS `resource_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resource_type` (
  `resource_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `resource_type_name` varchar(255) DEFAULT NULL,
  `resource_type_code` varchar(255) NOT NULL,
  PRIMARY KEY (`resource_type_id`),
  KEY `resource_type_code_idx` (`resource_type_code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resource_type`
--

LOCK TABLES `resource_type` WRITE;
/*!40000 ALTER TABLE `resource_type` DISABLE KEYS */;
INSERT INTO `resource_type` VALUES (1,'Ссылка на изображение, содержащее необходимую информацию','изображение'),(2,'Ссылка на pdf-файл, содержащий необходимую информацию','pdf');
/*!40000 ALTER TABLE `resource_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `solution_resource_collection`
--

DROP TABLE IF EXISTS `solution_resource_collection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `solution_resource_collection` (
  `src_id` int(11) NOT NULL AUTO_INCREMENT,
  `variant` int(11) NOT NULL,
  `resource_collection` int(11) DEFAULT NULL,
  PRIMARY KEY (`src_id`),
  KEY `fk_src_resource_collection` (`resource_collection`),
  KEY `fk_src_variant` (`variant`),
  CONSTRAINT `fk_src_resource_collection` FOREIGN KEY (`resource_collection`) REFERENCES `resource_collection` (`resource_collection_id`),
  CONSTRAINT `fk_src_variant` FOREIGN KEY (`variant`) REFERENCES `variant` (`variant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `solution_resource_collection`
--

LOCK TABLES `solution_resource_collection` WRITE;
/*!40000 ALTER TABLE `solution_resource_collection` DISABLE KEYS */;
INSERT INTO `solution_resource_collection` VALUES (6,1,94),(7,2,100),(8,3,105);
/*!40000 ALTER TABLE `solution_resource_collection` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task`
--

DROP TABLE IF EXISTS `task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task` (
  `task_id` int(11) NOT NULL AUTO_INCREMENT,
  `problem_type_id` int(11) NOT NULL,
  `exam_item_id` int(11) NOT NULL,
  PRIMARY KEY (`task_id`),
  KEY `fk_task_problem_type` (`problem_type_id`),
  KEY `fk_task_exam_item` (`exam_item_id`),
  KEY `task_idx` (`task_id`),
  CONSTRAINT `fk_task_exam_item` FOREIGN KEY (`exam_item_id`) REFERENCES `exam_item` (`exam_item_id`),
  CONSTRAINT `fk_task_problem_type` FOREIGN KEY (`problem_type_id`) REFERENCES `problem_type` (`problem_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task`
--

LOCK TABLES `task` WRITE;
/*!40000 ALTER TABLE `task` DISABLE KEYS */;
INSERT INTO `task` VALUES (1,1,5),(2,4,1);
/*!40000 ALTER TABLE `task` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `preferred_resource_type` int(11) NOT NULL,
  `user_vk_id` int(11) NOT NULL,
  `year_range` year(4) NOT NULL,
  PRIMARY KEY (`user_id`),
  KEY `fk_preferred_resource_type_idx` (`preferred_resource_type`),
  CONSTRAINT `fk_preferred_resource_type` FOREIGN KEY (`preferred_resource_type`) REFERENCES `resource_type` (`resource_type_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (48,'2019-06-12 14:55:36',1,1,2018),(52,'2019-06-13 19:48:26',1,43820622,2013),(53,'2019-06-16 13:29:22',2,19357976,2019);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `variant`
--

DROP TABLE IF EXISTS `variant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `variant` (
  `variant_id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `statement` text NOT NULL,
  `variant_year` year(4) NOT NULL,
  `solution` text NOT NULL,
  PRIMARY KEY (`variant_id`),
  KEY `fk_variant_task` (`task_id`),
  KEY `variant__idx` (`variant_id`),
  CONSTRAINT `fk_variant_task` FOREIGN KEY (`task_id`) REFERENCES `task` (`task_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `variant`
--

LOCK TABLES `variant` WRITE;
/*!40000 ALTER TABLE `variant` DISABLE KEYS */;
INSERT INTO `variant` VALUES (1,1,'http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/tex/1stmt.tex',2019,'http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/tex/1sltn.tex'),(2,1,'http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/tex/2stmt.tex',2018,'http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/tex/2sltn.tex'),(3,2,'http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/tex/3stmt.tex',2016,'http://kappa.cs.petrsu.ru/~nestulov/API/v2/files/tex/3sltn.tex');
/*!40000 ALTER TABLE `variant` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-09-22 23:53:28
