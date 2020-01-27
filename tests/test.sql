SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for test_company
-- ----------------------------
DROP TABLE IF EXISTS `test_company`;
CREATE TABLE `test_company` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for test_datatable_test
-- ----------------------------
DROP TABLE IF EXISTS `test_datatable_test`;
CREATE TABLE `test_datatable_test` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_id` int(11) DEFAULT NULL,
  `checkbox` tinyint(1) NOT NULL DEFAULT 0,
  `date` datetime DEFAULT NULL,
  `multicheckbox` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `datatableselect` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `textarea` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `select` int(11) DEFAULT NULL,
  `hidden` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `autocomplete` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `wysiwyg` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for test_datatable_test_child
-- ----------------------------
DROP TABLE IF EXISTS `test_datatable_test_child`;
CREATE TABLE `test_datatable_test_child` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for test_interest
-- ----------------------------
DROP TABLE IF EXISTS `test_interest`;
CREATE TABLE `test_interest` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for test_person
-- ----------------------------
DROP TABLE IF EXISTS `test_person`;
CREATE TABLE `test_person` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `image_id` int(11) DEFAULT NULL,
  `display_order` int(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `display_order` (`display_order`),
  KEY `company_id` (`company_id`),

  CONSTRAINT `test_person_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `test_company` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `test_person_ibfk_2` FOREIGN KEY (`image_id`) REFERENCES `cms_file` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=444 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for test_person_interest
-- ----------------------------
DROP TABLE IF EXISTS `test_person_interest`;
CREATE TABLE `test_person_interest` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `person_id` int(11) DEFAULT NULL,
  `interest_id` int(11) DEFAULT NULL,
  `grade` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `person_id` (`person_id`,`interest_id`),
  KEY `interest_id` (`interest_id`),
  CONSTRAINT `test_person_interest_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `test_person` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `test_person_interest_ibfk_2` FOREIGN KEY (`interest_id`) REFERENCES `test_interest` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=971 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for test_simple_object
-- ----------------------------
DROP TABLE IF EXISTS `test_simple_object`;
CREATE TABLE `test_simple_object` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
