-- MySQL dump 10.17  Distrib 10.3.12-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: nestulov
-- ------------------------------------------------------
-- Server version	10.3.12-MariaDB

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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `answer`
--

LOCK TABLES `answer` WRITE;
/*!40000 ALTER TABLE `answer` DISABLE KEYS */;
INSERT INTO `answer` VALUES (4,56,'20','2019-05-16 16:33:06'),(5,56,'21','2019-05-16 16:33:46'),(6,57,'19','2019-05-16 16:36:39'),(7,57,'19','2019-05-16 16:36:49');
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
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assignment`
--

LOCK TABLES `assignment` WRITE;
/*!40000 ALTER TABLE `assignment` DISABLE KEYS */;
INSERT INTO `assignment` VALUES (56,12,48,'19','2019-05-16 17:47:42',1,0),(57,11,48,'19','2019-05-16 17:47:44',0,0);
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `input`
--

LOCK TABLES `input` WRITE;
/*!40000 ALTER TABLE `input` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `problem`
--

LOCK TABLES `problem` WRITE;
/*!40000 ALTER TABLE `problem` DISABLE KEYS */;
INSERT INTO `problem` VALUES (1,2,'22162',6,'2018-05-09 20:05:40','2018-05-09 20:05:40',4,5,2019),(2,3,'12211',7,'2018-05-09 20:05:40','2018-05-09 20:05:40',2,14,2018),(3,4,'4',8,'2018-05-09 20:05:40','2018-05-09 20:05:40',3,9,2016),(4,5,'324',9,'2018-05-15 12:56:17','2018-05-15 12:56:17',1,10,2019),(10,10,'4',11,'2018-05-16 16:58:57','2018-05-16 16:58:57',5,2,2015),(11,12,'3',13,'2018-05-16 16:59:41','2018-05-16 16:59:41',4,1,2018),(12,14,'19',15,'2018-05-16 17:00:08','2018-05-16 17:00:08',6,8,2017);
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
INSERT INTO `problem_type` VALUES (1,1,'кодирование_информации'),(2,1,'робот'),(3,1,'передача_информации'),(4,1,'системы_счисления'),(5,1,'поиск_маршрута_по_расписанию'),(6,1,'арифметическая_прогрессия');
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
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resource`
--

LOCK TABLES `resource` WRITE;
/*!40000 ALTER TABLE `resource` DISABLE KEYS */;
INSERT INTO `resource` VALUES (1,2,'text1','Для кодирования букв О, В, Д, П, А решили использовать двоичное представление чисел 0, 1, 2, 3 и 4 соответственно (с сохранением одного незначащего нуля в случае одноразрядного представления). Закодируйте последовательность букв ВОДОПАД таким способом и результат запишите восьмеричным кодом.',1),(2,3,'text1','У исполнителя ДваПять две команды, которым присвоены номера:\n\n1.      отними 2\n2.      раздели на 5\n\nВыполняя первую из них, ДваПять отнимает от числа на экране 2, а выполняя вторую, делит это число на 5 (если деление нацело невозможно, ДваПять отключается)\nЗапишите порядок команд в программе, которая содержит не более 5 команд и переводит число 152 в число 2.\nВ ответе указывайте лишь номера команд, пробелы между цифрами не ставьте. Так, для программы\n\nраздели на 5\nотними 2\nотними 2\n\nнужно написать 211. Эта программа преобразует, например, число 55 в число 7.',1),(3,4,'text1','Производится четырёхканальная (квадро) звукозапись с частотой дискретизации 16 кГц и 32-битныных не производится. Размер полученного файла  60 Мбайт. Какая из приведённых ниже величин наиболее близка ко времени, в течение которого проводилась запись?\n\n1) 1 мин.\n2) 2 мин.\n3) 3 мин.\n4) 4 мин.',1),(4,5,'text1','Алексей составляет таблицу кодовых слов для передачи сообщений, каждому сообщению соответствует своё кодовое слово. В качестве кодовых слов Алексей использует 5-буквенные слова, в которых есть только буквы A, B, C, X, причём буква X может появиться на первом месте или не появиться вовсе. Сколько различных кодовых слов может использовать Алексей?',1),(5,6,'text1','1) Представь числа в условии в двоичном коде.\n2) Закодируй с их помощью слово водопад.\n3) Полученное двоичное число разбей на тройки справа налево и переведи в восьмеричную систему.',1),(6,7,'text1','Дели число на 5 всегда, когда это возможно (то есть когда число кратно 5), в оставшихся случаях отнимай 2.',1),(7,8,'text1','1) Частота дискретизации 16 кГц означает, что за секунду сигнал запоминается 16000 раз.\n2) Объем памяти возрастает прямо пропорционально числу каналов.\n3) Разрешение и размер файла нужно привести к одной единице измерения (например, битам), а время перевести в секунды.',1),(8,9,'text1','Посчитай, сколько букв может находиться на каждой позиции и перемножь.',1),(17,2,'Pimg','http://kappa.cs.petrsu.ru/~nestulov/API/files/1Pimg.png',3),(18,3,'Pimg','http://kappa.cs.petrsu.ru/~nestulov/API/files/2Pimg.png',3),(19,4,'Pimg','http://kappa.cs.petrsu.ru/~nestulov/API/files/3Pimg.png',3),(20,5,'Pimg','http://kappa.cs.petrsu.ru/~nestulov/API/files/4Pimg.png',3),(21,2,'Ppdf','http://kappa.cs.petrsu.ru/~nestulov/API/files/1Ppdf.pdf',2),(22,3,'Ppdf','http://kappa.cs.petrsu.ru/~nestulov/API/files/2Ppdf.pdf',2),(23,4,'Ppdf','http://kappa.cs.petrsu.ru/~nestulov/API/files/3Ppdf.pdf',2),(24,5,'Ppdf','http://kappa.cs.petrsu.ru/~nestulov/API/files/4Ppdf.pdf',2),(25,6,'Ppdf','http://kappa.cs.petrsu.ru/~nestulov/API/files/1Spdf.pdf',2),(26,7,'Ppdf','http://kappa.cs.petrsu.ru/~nestulov/API/files/2Spdf.pdf',2),(27,8,'Ppdf','http://kappa.cs.petrsu.ru/~nestulov/API/files/3Spdf.pdf',2),(28,9,'Ppdf','http://kappa.cs.petrsu.ru/~nestulov/API/files/4Spdf.pdf',2),(29,6,'Pimg','http://kappa.cs.petrsu.ru/~nestulov/API/files/1Simg.png',3),(30,7,'Pimg','http://kappa.cs.petrsu.ru/~nestulov/API/files/2Simg.png',3),(31,8,'Pimg','http://kappa.cs.petrsu.ru/~nestulov/API/files/3Simg.png',3),(32,9,'Pimg','http://kappa.cs.petrsu.ru/~nestulov/API/files/4Simg.png',3),(33,2,'Plink','http://kappa.cs.petrsu.ru/~nestulov/API/files/1Pimg.png',4),(34,3,'Plink','http://kappa.cs.petrsu.ru/~nestulov/API/files/2Pimg.png',4),(35,4,'Plink','http://kappa.cs.petrsu.ru/~nestulov/API/files/3Pimg.png',4),(36,5,'Plink','http://kappa.cs.petrsu.ru/~nestulov/API/files/4Pimg.png',4),(37,6,'Plink','http://kappa.cs.petrsu.ru/~nestulov/API/files/1Simg.png',4),(38,7,'Plink','http://kappa.cs.petrsu.ru/~nestulov/API/files/2Simg.png',4),(39,8,'Plink','http://kappa.cs.petrsu.ru/~nestulov/API/files/3Pimg.png',4),(40,9,'Plink','http://kappa.cs.petrsu.ru/~nestulov/API/files/4Pimg.png',4),(42,10,'text+image5','Транспортная фирма осуществляет грузоперевозки разными видами транспорта между четырьмя городами: ЧЕРЕПОВЕЦ, МОСКВА, КУРСК, ПЕРМЬ. Стоимость доставки грузов и время в пути указаны в таблице ниже.\nОпределите маршрут наиболее дешевого варианта доставки груза из ЧЕРЕПОВЦА в ПЕРМЬ. Если таких маршрутов несколько, в ответе укажите наиболее выгодный по времени вариант.\n1) ЧЕРЕПОВЕЦ  ПЕРМЬ \n2) ЧЕРЕПОВЕЦ  КУРСК  ПЕРМЬ \n3) ЧЕРЕПОВЕЦ  МОСКВА  ПЕРМЬ \n4) ЧЕРЕПОВЕЦ  МОСКВА  КУРСК  ПЕРМЬ',1),(43,10,'text+image5','http://kappa.cs.petrsu.ru/~nestulov/API/files/5PimgP.jpg',1),(44,10,'5Pimg','http://kappa.cs.petrsu.ru/~nestulov/API/files/5Pimg.png',3),(45,10,'5pdf','http://kappa.cs.petrsu.ru/~nestulov/API/files/5Ppdf.pdf',2),(46,11,'text5','1. ЧЕРЕПОВЕЦ  ПЕРМЬ: стоимость 140, время 80\n\n2. ЧЕРЕПОВЕЦ  КУРСК  ПЕРМЬ: стоимость 100 + 60 = 160, время 80 + 40 = 120\n\n3. ЧЕРЕПОВЕЦ  МОСКВА  ПЕРМЬ: стоимость 50 + 100 = 150, время 15 + 70 =85\n\n4. ЧЕРЕПОВЕЦ  МОСКВА  КУРСК  ПЕРМЬ: стоимость 50 + 30 + 60 = 140, время 15 + 10 + 40 = 65\n\nВарианты 1 и 4 имеют одинаково минимальную стоимость 140 (140 < 150 < 160), но вариант 4 более выгоден по времени 65 < 80.',1),(47,11,'5Simg','http://kappa.cs.petrsu.ru/~nestulov/API/files/5Simg.png',3),(48,11,'5Spdf','http://kappa.cs.petrsu.ru/~nestulov/API/files/5Spdf.pdf',2),(49,12,'6Pimg','http://kappa.cs.petrsu.ru/~nestulov/API/files/6Pimg.png',3),(50,12,'6Ppdf','http://kappa.cs.petrsu.ru/~nestulov/API/files/6Ppdf.pdf',2),(51,13,'6Simg','http://kappa.cs.petrsu.ru/~nestulov/API/files/6Simg.png',3),(52,13,'6Spdf','http://kappa.cs.petrsu.ru/~nestulov/API/files/6Spdf.pdf',2),(53,14,'7Pimg','http://kappa.cs.petrsu.ru/~nestulov/API/files/7Pimg.png',3),(54,14,'7Ppdf','http://kappa.cs.petrsu.ru/~nestulov/API/files/7Ppdf.pdf',2),(55,15,'7Simg','http://kappa.cs.petrsu.ru/~nestulov/API/files/7Simg.png',3),(56,15,'7Spdf','http://kappa.cs.petrsu.ru/~nestulov/API/files/7spdf.pdf',2),(57,14,'7Ptext+image','Определите, что будет напечатано в результате работы следующего фрагмента программы:',1),(58,14,'7Ptext+image','http://kappa.cs.petrsu.ru/~nestulov/API/files/7PimgP.png',1);
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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resource_collection`
--

LOCK TABLES `resource_collection` WRITE;
/*!40000 ALTER TABLE `resource_collection` DISABLE KEYS */;
INSERT INTO `resource_collection` VALUES (1),(2),(3),(4),(5),(6),(7),(8),(9),(10),(11),(12),(13),(14),(15);
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
  PRIMARY KEY (`resource_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resource_type`
--

LOCK TABLES `resource_type` WRITE;
/*!40000 ALTER TABLE `resource_type` DISABLE KEYS */;
INSERT INTO `resource_type` VALUES (1,'Текст, содержащий необходимую информацию','текст'),(2,'Ссылка на pdf-файл, содержащий необходимую информацию','pdf'),(3,'Ссылка на изображение, содержащее необходимую информацию','изображение'),(4,'Ссылка на страницу в сети Интернет, содержащую необходимую информацию','ссылка');
/*!40000 ALTER TABLE `resource_type` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task`
--

LOCK TABLES `task` WRITE;
/*!40000 ALTER TABLE `task` DISABLE KEYS */;
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
  `user_created` timestamp NULL DEFAULT NULL,
  `preferred_resource_type` int(11) NOT NULL,
  `user_vk_id` int(11) NOT NULL,
  `year_range` year(4) NOT NULL,
  PRIMARY KEY (`user_id`),
  KEY `fk_preferred_resource_type_idx` (`preferred_resource_type`),
  CONSTRAINT `fk_preferred_resource_type` FOREIGN KEY (`preferred_resource_type`) REFERENCES `resource_type` (`resource_type_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (48,'2019-05-15 14:55:53',2,1,2017),(51,'2019-05-15 16:05:04',4,2,2019);
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
  `template` text NOT NULL,
  `task_year` year(4) NOT NULL,
  `solution` text NOT NULL,
  PRIMARY KEY (`variant_id`),
  KEY `fk_variant_task` (`task_id`),
  KEY `variant__idx` (`variant_id`),
  CONSTRAINT `fk_variant_task` FOREIGN KEY (`task_id`) REFERENCES `task` (`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `variant`
--

LOCK TABLES `variant` WRITE;
/*!40000 ALTER TABLE `variant` DISABLE KEYS */;
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

-- Dump completed on 2019-05-17 16:15:36
