-- MySQL dump 10.13  Distrib 5.5.62, for Win64 (AMD64)
--
-- Host: 192.168.10.10    Database: im
-- ------------------------------------------------------
-- Server version	5.7.29

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
-- Table structure for table `contacts_add_record`
--

DROP TABLE IF EXISTS `contacts_add_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contacts_add_record` (
                                       `record_id` varchar(36) NOT NULL,
                                       `send_uid` varchar(36) CHARACTER SET utf8 NOT NULL,
                                       `accept_uid` varchar(36) CHARACTER SET utf8 NOT NULL,
                                       `remarks` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
                                       `status` enum('pending','agree','refuse','ignore') CHARACTER SET utf8 DEFAULT NULL,
                                       `created_at` datetime DEFAULT NULL,
                                       `updated_at` datetime DEFAULT NULL,
                                       PRIMARY KEY (`record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='好友申请表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contacts_add_record`
--

LOCK TABLES `contacts_add_record` WRITE;
/*!40000 ALTER TABLE `contacts_add_record` DISABLE KEYS */;
INSERT INTO `contacts_add_record` VALUES ('16348879362420','289084231349448705','289063021190324225','hello','agree','2021-10-22 07:32:16','2021-10-22 07:33:08');
/*!40000 ALTER TABLE `contacts_add_record` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contacts_friend`
--

DROP TABLE IF EXISTS `contacts_friend`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contacts_friend` (
                                   `id` int(11) NOT NULL AUTO_INCREMENT,
                                   `main_uid` varchar(32) CHARACTER SET utf8 NOT NULL COMMENT '我的uid',
                                   `friend_uid` varchar(32) CHARACTER SET utf8 NOT NULL COMMENT '朋友uid',
                                   `created_at` datetime NOT NULL COMMENT '创建时间',
                                   `updated_at` datetime DEFAULT NULL,
                                   PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contacts_friend`
--

LOCK TABLES `contacts_friend` WRITE;
/*!40000 ALTER TABLE `contacts_friend` DISABLE KEYS */;
INSERT INTO `contacts_friend` VALUES (41,'289084231349448705','289063021190324225','2021-10-22 07:33:08','2021-10-22 07:33:08');
/*!40000 ALTER TABLE `contacts_friend` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contacts_recent`
--

DROP TABLE IF EXISTS `contacts_recent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contacts_recent` (
                                   `send_uid` varchar(36) CHARACTER SET utf8 NOT NULL,
                                   `accept_uid` varchar(36) CHARACTER SET utf8 DEFAULT NULL,
                                   `content_id` int(11) DEFAULT NULL,
                                   `create_at` datetime DEFAULT NULL,
                                   `update_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='当前消息列表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contacts_recent`
--

LOCK TABLES `contacts_recent` WRITE;
/*!40000 ALTER TABLE `contacts_recent` DISABLE KEYS */;
/*!40000 ALTER TABLE `contacts_recent` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `group`
--

DROP TABLE IF EXISTS `group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group` (
                         `group_number` varchar(13) COLLATE utf8mb4_bin NOT NULL COMMENT '主键',
                         `group_name` varchar(30) COLLATE utf8mb4_bin NOT NULL COMMENT '群组名称',
                         `group_head_image` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '群组头像',
                         `introduction` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '群组简介',
                         `member_num` int(11) NOT NULL DEFAULT '1' COMMENT '群组人数',
                         `extra` varchar(1024) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '附加属性',
                         `created_at` datetime NOT NULL COMMENT '创建时间',
                         `updated_at` datetime NOT NULL COMMENT '更新时间',
                         PRIMARY KEY (`group_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='群组表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group`
--

LOCK TABLES `group` WRITE;
/*!40000 ALTER TABLE `group` DISABLE KEYS */;
/*!40000 ALTER TABLE `group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `group_member`
--

DROP TABLE IF EXISTS `group_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group_member` (
                                `id` int(11) NOT NULL AUTO_INCREMENT,
                                `group_number` varchar(13) NOT NULL COMMENT '群组ID',
                                `uid` varchar(36) NOT NULL,
                                `type` enum('leader','admin','member') NOT NULL,
                                `extra` varchar(1024) DEFAULT NULL COMMENT '扩展字段',
                                `created_at` datetime NOT NULL,
                                `updated_at` datetime NOT NULL,
                                PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb4 COMMENT='群成员';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_member`
--

LOCK TABLES `group_member` WRITE;
/*!40000 ALTER TABLE `group_member` DISABLE KEYS */;
/*!40000 ALTER TABLE `group_member` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `group_member_join_record`
--

DROP TABLE IF EXISTS `group_member_join_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group_member_join_record` (
                                            `id` int(11) NOT NULL AUTO_INCREMENT,
                                            PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_member_join_record`
--

LOCK TABLES `group_member_join_record` WRITE;
/*!40000 ALTER TABLE `group_member_join_record` DISABLE KEYS */;
/*!40000 ALTER TABLE `group_member_join_record` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member`
--

DROP TABLE IF EXISTS `member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member` (
                          `uid` varchar(32) NOT NULL COMMENT 'uid',
                          `username` varchar(11) CHARACTER SET utf8 NOT NULL COMMENT '用户名',
                          `email` varchar(50) CHARACTER SET utf8 DEFAULT NULL COMMENT '邮箱',
                          `password` varchar(15) CHARACTER SET utf8 DEFAULT NULL COMMENT '密码',
                          `salt` varchar(4) CHARACTER SET utf8 DEFAULT NULL,
                          `head_image` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '头像',
                          `nikename` varchar(20) CHARACTER SET utf8 DEFAULT NULL COMMENT '昵称',
                          `autograph` varchar(50) CHARACTER SET utf8 DEFAULT NULL COMMENT '签名',
                          `created_at` datetime DEFAULT NULL COMMENT '创建时间',
                          `updated_at` datetime DEFAULT NULL COMMENT '上次登录时间',
                          PRIMARY KEY (`uid`),
                          UNIQUE KEY `member_UN` (`username`),
                          KEY `member_username_IDX` (`username`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member`
--

LOCK TABLES `member` WRITE;
/*!40000 ALTER TABLE `member` DISABLE KEYS */;
INSERT INTO `member` VALUES ('289063021190324225','jksusu',NULL,'jksusu','6125','http://cdn.jksusu.cn/xiyangyang.jpg','meet',NULL,'2021-08-25 15:53:15','2021-08-25 15:53:15'),('289084231349448705','ppx',NULL,'ppxppx','6126','http://cdn.jksusu.cn/meiyangyang.jpg','ppx',NULL,'2021-08-25 17:17:31','2021-08-25 17:17:31');
/*!40000 ALTER TABLE `member` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message`
--

DROP TABLE IF EXISTS `message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message` (
                           `msg_id` varchar(36) NOT NULL,
                           `content` varchar(500) CHARACTER SET utf8 NOT NULL,
                           `send_uid` varchar(36) CHARACTER SET utf8 NOT NULL,
                           `accept_type` enum('personal','group') NOT NULL,
                           `accept_uid` varchar(36) CHARACTER SET utf8 NOT NULL,
                           `content_type` enum('text','picture','video') CHARACTER SET utf8 NOT NULL,
                           `created_at` datetime NOT NULL,
                           `updated_at` datetime DEFAULT NULL,
                           PRIMARY KEY (`msg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='消息内容';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message`
--

LOCK TABLES `message` WRITE;
/*!40000 ALTER TABLE `message` DISABLE KEYS */;
/*!40000 ALTER TABLE `message` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message_index`
--

DROP TABLE IF EXISTS `message_index`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_index` (
                                 `msg_id` varchar(36) NOT NULL,
                                 `send_uid` varchar(36) CHARACTER SET utf8 NOT NULL,
                                 `accept_uid` varchar(36) CHARACTER SET utf8 NOT NULL,
                                 `read_state` enum('read','unread') NOT NULL,
                                 `created_at` datetime NOT NULL,
                                 `updated_at` datetime DEFAULT NULL,
                                 PRIMARY KEY (`msg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='消息索引表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_index`
--

LOCK TABLES `message_index` WRITE;
/*!40000 ALTER TABLE `message_index` DISABLE KEYS */;
/*!40000 ALTER TABLE `message_index` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message_session_list`
--

DROP TABLE IF EXISTS `message_session_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_session_list` (
                                        `session_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '会话列表id',
                                        `topping` enum('yes','no') NOT NULL DEFAULT 'no',
                                        `session_type` enum('group','personal') DEFAULT NULL COMMENT '会话类型(群聊，单聊)',
                                        `uid` varchar(32) NOT NULL COMMENT '我的uid',
                                        `accept_uid` varchar(32) NOT NULL COMMENT '接收到uid',
                                        `disturb_status` enum('yes','no') NOT NULL DEFAULT 'no' COMMENT '是否屏蔽消息，不提示。',
                                        `last_message` varchar(500) DEFAULT NULL,
                                        `last_message_type` varchar(20) DEFAULT NULL,
                                        `last_time` datetime DEFAULT NULL,
                                        `created_at` datetime NOT NULL,
                                        `updated_at` datetime NOT NULL,
                                        PRIMARY KEY (`session_id`)
) ENGINE=InnoDB AUTO_INCREMENT=162 DEFAULT CHARSET=utf8mb4 COMMENT='消息会话列表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_session_list`
--

LOCK TABLES `message_session_list` WRITE;
/*!40000 ALTER TABLE `message_session_list` DISABLE KEYS */;
/*!40000 ALTER TABLE `message_session_list` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'im'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-10-22 16:36:49
