-- Dumping structure for table Meta_Attributes
DROP TABLE IF EXISTS `Meta_Attributes`;
CREATE TABLE IF NOT EXISTS `Meta_Attributes` (
  `attr_id` int(11) NOT NULL AUTO_INCREMENT,
  `object_type_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(24) NOT NULL DEFAULT '0',
  PRIMARY KEY (`attr_id`),
  UNIQUE KEY `name` (`name`),
  KEY `object_type_id` (`object_type_id`),
  CONSTRAINT `meta_attributes_ibfk_1` FOREIGN KEY (`object_type_id`) REFERENCES `Meta_ObjectTypes` (`object_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table Meta_Objects
DROP TABLE IF EXISTS `Meta_Objects`;
CREATE TABLE IF NOT EXISTS `Meta_Objects` (
  `object_id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `object_type_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`object_id`),
  KEY `r_5` (`object_type_id`),
  KEY `r_1` (`parent_id`),
  CONSTRAINT `r_1` FOREIGN KEY (`parent_id`) REFERENCES `Meta_Objects` (`object_id`) ON DELETE CASCADE,
  CONSTRAINT `r_5` FOREIGN KEY (`object_type_id`) REFERENCES `Meta_ObjectTypes` (`object_type_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table Meta_ObjectTypes
DROP TABLE IF EXISTS `Meta_ObjectTypes`;
CREATE TABLE IF NOT EXISTS `Meta_ObjectTypes` (
  `object_type_id` int(11) NOT NULL,
  `name` varchar(24) DEFAULT NULL,
  `description` varchar(24) DEFAULT NULL,
  PRIMARY KEY (`object_type_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table Meta_Params
DROP TABLE IF EXISTS `Meta_Params`;
CREATE TABLE IF NOT EXISTS `Meta_Params` (
  `object_id` int(11) NOT NULL,
  `attr_id` int(11) NOT NULL,
  `text_value` varchar(50) DEFAULT NULL,
  `int_value` int(11) DEFAULT NULL,
  `float_value` float DEFAULT NULL,
  `date_value` date DEFAULT NULL,
  `time_value` time DEFAULT NULL,
  KEY `r_6` (`object_id`),
  KEY `r_7` (`attr_id`),
  CONSTRAINT `r_7` FOREIGN KEY (`attr_id`) REFERENCES `Meta_Attributes` (`attr_id`),
  CONSTRAINT `r_6` FOREIGN KEY (`object_id`) REFERENCES `Meta_Objects` (`object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;