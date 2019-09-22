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
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-09-22 23:55:31
