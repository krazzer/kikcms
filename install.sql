SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `cms_language`
-- ----------------------------
DROP TABLE IF EXISTS `cms_language`;
CREATE TABLE `cms_language` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
--  Records of `cms_language`
-- ----------------------------
BEGIN;
INSERT INTO `cms_language` VALUES ('1', 'nl', 'Nederlands', '1');
COMMIT;

-- ----------------------------
--  Table structure for `cms_page`
-- ----------------------------
DROP TABLE IF EXISTS `cms_page`;
CREATE TABLE `cms_page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `alias` int(11) DEFAULT NULL,
  `template` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `display_order` int(11) DEFAULT NULL,
  `key` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` enum('page','menu','link','alias') CHARACTER SET utf8 NOT NULL DEFAULT 'page',
  `level` int(11) DEFAULT NULL,
  `lft` int(11) DEFAULT NULL,
  `rgt` int(11) DEFAULT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `menu_max_level` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `display_order` (`display_order`,`parent_id`) USING BTREE,
  UNIQUE KEY `key` (`key`) USING BTREE,
  KEY `parent_id` (`parent_id`),
  KEY `template_id` (`template`),
  KEY `alias` (`alias`),
  CONSTRAINT `cms_page_ibfk_1` FOREIGN KEY (`alias`) REFERENCES `cms_page` (`id`),
  CONSTRAINT `cms_page_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `cms_page` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
--  Table structure for `cms_page_content`
-- ----------------------------
DROP TABLE IF EXISTS `cms_page_content`;
CREATE TABLE `cms_page_content` (
  `page_id` int(11) NOT NULL,
  `field` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `value` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`page_id`,`field`),
  KEY `field` (`field`) USING BTREE,
  CONSTRAINT `cms_page_content_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `cms_page` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
--  Table structure for `cms_page_language`
-- ----------------------------
DROP TABLE IF EXISTS `cms_page_language`;
CREATE TABLE `cms_page_language` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(11) NOT NULL,
  `language_code` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) DEFAULT '1',
  `name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `seo_title` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `seo_description` text CHARACTER SET utf8,
  `seo_keywords` text CHARACTER SET utf8,
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_id` (`page_id`,`language_code`),
  KEY `language_code` (`language_code`),
  KEY `language_code_2` (`language_code`),
  CONSTRAINT `cms_page_language_ibfk_1` FOREIGN KEY (`language_code`) REFERENCES `cms_language` (`code`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `cms_page_language_ibfk_2` FOREIGN KEY (`page_id`) REFERENCES `cms_page` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
--  Records of `cms_page_language`
-- ----------------------------
BEGIN;
INSERT INTO `cms_page_language` VALUES ('3', '3', 'nl', '1', 'Home', 'home', null, null, null), ('4', '4', 'nl', '1', 'Pagina 2', 'pagina-2', null, null, null), ('5', '5', 'nl', '1', 'Hoofdmenu', null, null, null, null);
COMMIT;

-- ----------------------------
--  Table structure for `cms_page_language_content`
-- ----------------------------
DROP TABLE IF EXISTS `cms_page_language_content`;
CREATE TABLE `cms_page_language_content` (
  `page_id` int(11) NOT NULL,
  `language_code` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `field` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `value` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`page_id`,`language_code`,`field`),
  UNIQUE KEY `page_id` (`page_id`,`language_code`,`field`) USING BTREE,
  KEY `language_code` (`language_code`),
  KEY `field` (`field`) USING BTREE,
  CONSTRAINT `cms_page_language_content_ibfk_1` FOREIGN KEY (`language_code`) REFERENCES `cms_language` (`code`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `cms_page_language_content_ibfk_2` FOREIGN KEY (`page_id`) REFERENCES `cms_page` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
--  Records of `cms_page_language_content`
-- ----------------------------
BEGIN;
INSERT INTO `cms_page_language_content` VALUES ('3', 'nl', 'content', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec aliquet lobortis lorem, eu rutrum leo aliquam in. Nullam dapibus posuere ornare. Nunc feugiat volutpat magna non elementum. Vivamus tristique facilisis elit quis imperdiet. Pellentesque gravida eros nec lectus eleifend tempor. Maecenas sed pellentesque sem.</p>\r\n<p>Quisque pharetra lacus vitae tortor rhoncus lacinia. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Curabitur ultrices, nisi ac consequat gravida, nisi urna ultricies velit, ut tempus elit lectus eu libero. Integer volutpat aliquet tristique. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Integer blandit massa odio, non gravida elit fermentum a. Etiam vitae mattis ante. Vivamus vitae metus viverra, tristique est id, fringilla purus. Suspendisse nec dapibus elit. Sed vestibulum lacus vitae rutrum pulvinar.</p>\r\n<p>Suspendisse vitae mattis mi, mattis ullamcorper orci. Cras tempor nisl ac lorem tristique maximus. In vulputate, tellus et euismod vehicula, diam ante varius enim, at facilisis elit eros eget nisi. Proin scelerisque pharetra lectus vitae faucibus. Vivamus iaculis, ante ut euismod sodales, nibh leo eleifend mauris, vel ultrices dolor est quis dui. Nunc vestibulum malesuada tellus et aliquet. Integer interdum ante leo, consectetur rhoncus nisl commodo eu. Vivamus efficitur est eu faucibus tempus. Nulla tincidunt ut dolor porttitor eleifend.</p>\r\n<p>Aenean aliquet sit amet lectus sed gravida. Donec volutpat, nisi at venenatis venenatis, leo est tempus magna, eget ultricies mi est sed nisl. Etiam hendrerit, erat nec mattis lobortis, leo orci rhoncus elit, vitae posuere ante est quis nisl. Aliquam pharetra euismod rhoncus. Proin odio metus, tincidunt tempus justo quis, luctus placerat metus. Nam sit amet nisi et massa viverra tincidunt. Cras sit amet felis aliquet, tincidunt mi sit amet, hendrerit mi.</p>'), ('4', 'nl', 'content', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec aliquet lobortis lorem, eu rutrum leo aliquam in. Nullam dapibus posuere ornare. Nunc feugiat volutpat magna non elementum. Vivamus tristique facilisis elit quis imperdiet. Pellentesque gravida eros nec lectus eleifend tempor. Maecenas sed pellentesque sem. Quisque pharetra lacus vitae tortor rhoncus lacinia. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Curabitur ultrices, nisi ac consequat gravida, nisi urna ultricies velit, ut tempus elit lectus eu libero. Integer volutpat aliquet tristique. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Integer blandit massa odio, non gravida elit fermentum a. Etiam vitae mattis ante. Vivamus vitae metus viverra, tristique est id, fringilla purus. Suspendisse nec dapibus elit. Sed vestibulum lacus vitae rutrum pulvinar.</p>\r\n<p>Suspendisse vitae mattis mi, mattis ullamcorper orci. Cras tempor nisl ac lorem tristique maximus. In vulputate, tellus et euismod vehicula, diam ante varius enim, at facilisis elit eros eget nisi. Proin scelerisque pharetra lectus vitae faucibus. Vivamus iaculis, ante ut euismod sodales, nibh leo eleifend mauris, vel ultrices dolor est quis dui. Nunc vestibulum malesuada tellus et aliquet. Integer interdum ante leo, consectetur rhoncus nisl commodo eu. Vivamus efficitur est eu faucibus tempus. Nulla tincidunt ut dolor porttitor eleifend. Aenean aliquet sit amet lectus sed gravida. Donec volutpat, nisi at venenatis venenatis, leo est tempus magna, eget ultricies mi est sed nisl. Etiam hendrerit, erat nec mattis lobortis, leo orci rhoncus elit, vitae posuere ante est quis nisl. Aliquam pharetra euismod rhoncus. Proin odio metus, tincidunt tempus justo quis, luctus placerat metus. Nam sit amet nisi et massa viverra tincidunt. Cras sit amet felis aliquet, tincidunt mi sit amet, hendrerit mi.</p>\r\n<p>Fusce non pellentesque eros. Vestibulum vitae arcu auctor, convallis ex eget, porta nulla. In fringilla efficitur massa. Aliquam laoreet malesuada aliquam. Vivamus ligula felis, sagittis vel pretium ut, maximus et est. Curabitur vel ipsum nunc. In vestibulum eu elit nec scelerisque. Suspendisse hendrerit finibus tellus accumsan sagittis. Praesent et libero gravida, commodo ante consequat, ultrices urna. Praesent a sapien sed odio imperdiet elementum non eu ante. Donec id dui ut ligula tincidunt auctor sit amet non ante.</p>');
COMMIT;

-- ----------------------------
--  Table structure for `cms_translation_key`
-- ----------------------------
DROP TABLE IF EXISTS `cms_translation_key`;
CREATE TABLE `cms_translation_key` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(127) CHARACTER SET utf8mb4 DEFAULT NULL,
  `db` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
--  Table structure for `cms_translation_value`
-- ----------------------------
DROP TABLE IF EXISTS `cms_translation_value`;
CREATE TABLE `cms_translation_value` (
  `key_id` int(11) NOT NULL,
  `language_code` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `value` longtext CHARACTER SET utf8mb4,
  PRIMARY KEY (`key_id`,`language_code`),
  KEY `language_code` (`language_code`),
  CONSTRAINT `cms_translation_value_ibfk_1` FOREIGN KEY (`language_code`) REFERENCES `cms_language` (`code`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `cms_translation_value_ibfk_2` FOREIGN KEY (`key_id`) REFERENCES `cms_translation_key` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
--  Table structure for `cms_user`
-- ----------------------------
DROP TABLE IF EXISTS `cms_user`;
CREATE TABLE `cms_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8 NOT NULL,
  `password` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `blocked` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `role` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `remember_me` blob DEFAULT NULL,
  `settings` blob DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
--  Table structure for `finder_file`
-- ----------------------------
DROP TABLE IF EXISTS `cms_file`;
CREATE TABLE `cms_file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `extension` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `mimetype` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `is_folder` tinyint(10) NOT NULL DEFAULT '0',
  `folder_id` int(11) DEFAULT NULL,
  `size` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) DEFAULT NULL,
  `key` VARCHAR(255) DEFAULT NULL,
  `hash` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `cms_file_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `cms_file` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `finder_permission`
-- ----------------------------
CREATE TABLE `cms_file_permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` varchar(16) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `file_id` int(11) DEFAULT NULL,
  `right` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role` (`role`,`file_id`) USING BTREE,
  UNIQUE KEY `user_id` (`user_id`,`file_id`) USING BTREE,
  KEY `file_id` (`file_id`),
  KEY `role_2` (`role`),
  CONSTRAINT `finder_permission_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `cms_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `finder_permission_ibfk_2` FOREIGN KEY (`file_id`) REFERENCES `cms_file` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=360 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
--  Table structure for `ga_day_visit`
-- ----------------------------
DROP TABLE IF EXISTS `cms_analytics_day`;
CREATE TABLE `cms_analytics_day` (
  `date` date NOT NULL,
  `visits` int(11) NOT NULL DEFAULT '0',
  `unique_visits` int(11) NOT NULL,
  PRIMARY KEY (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
--  Table structure for `ga_visit_data`
-- ----------------------------
DROP TABLE IF EXISTS `cms_analytics_metric`;
CREATE TABLE `cms_analytics_metric` (
  `date` date NOT NULL,
  `type` enum('source','os','page','browser','location','resolutionDesktop','resolutionTablet','resolutionMobile') NOT NULL DEFAULT 'source',
  `value` varchar(128) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `visits` int(11) NOT NULL,
  PRIMARY KEY (`date`,`type`,`value`),
  KEY `date` (`date`),
  KEY `type` (`type`),
  KEY `value` (`value`),
  KEY `visits` (`visits`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Records of `cms_page`
-- ----------------------------
BEGIN;
INSERT INTO `cms_page` (id, parent_id, alias, template, display_order, `key`, type, level, lft, rgt, link, menu_max_level, created_at, updated_at) VALUES
  ('5', null, null, null, 1, 'main', 'menu', '0', '1', '6', null, '1', NOW(), NOW()),
  ('6', null, null, 'default', null, 'page-not-found', 'page', null, null, null, null, null, NOW(), NOW()),
  ('3', '5', null, 'default', '1', 'default', 'page', '1', '2', '3', null, null, NOW(), NOW()),
  ('4', '5', null, 'default', '2', null, 'page', '1', '4', '5', null, null, NOW(), NOW());

INSERT INTO `cms_page_language_content` (page_id, language_code, field, value) VALUES (6, 'nl', 'content', 'Helaas! De door uw opgevraagde pagina kon niet worden gevonden.');
INSERT INTO `cms_page_language` (page_id, language_code, active, name, slug) VALUES (6, 'nl', 1, 'Pagina niet gevonden', 'page-not-found');
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
