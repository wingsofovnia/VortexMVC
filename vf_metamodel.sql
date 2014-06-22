SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


DROP TABLE IF EXISTS vf_attributes;
CREATE TABLE IF NOT EXISTS vf_attributes (
  attr_id int(11) NOT NULL AUTO_INCREMENT,
  object_type_id int(11) NOT NULL DEFAULT '0',
  name varchar(24) NOT NULL DEFAULT '0',
  PRIMARY KEY (attr_id),
  UNIQUE KEY name (name),
  KEY object_type_id (object_type_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS vf_objects;
CREATE TABLE IF NOT EXISTS vf_objects (
  object_id int(11) NOT NULL AUTO_INCREMENT,
  parent_id int(11) DEFAULT NULL,
  object_type_id int(11) DEFAULT NULL,
  checksum char(32) NOT NULL,
  PRIMARY KEY (object_id),
  KEY r_5 (object_type_id),
  KEY r_1 (parent_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS vf_objectTypes;
CREATE TABLE IF NOT EXISTS vf_objectTypes (
  object_type_id int(11) NOT NULL,
  name varchar(32) DEFAULT NULL,
  PRIMARY KEY (object_type_id),
  UNIQUE KEY name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS vf_params;
CREATE TABLE IF NOT EXISTS vf_params (
  id int(11) NOT NULL AUTO_INCREMENT,
  object_id int(11) NOT NULL,
  attr_id int(11) NOT NULL,
  int_value int(11) DEFAULT NULL,
  boolean_value bit(1) DEFAULT NULL,
  float_value float DEFAULT NULL,
  text_value varchar(50) DEFAULT NULL,
  array_value varchar(512) DEFAULT NULL,
  reference_value int(11) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY r_6 (object_id),
  KEY r_7 (attr_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TRIGGER IF EXISTS vf_update_checksum;
DELIMITER //
CREATE TRIGGER vf_update_checksum AFTER INSERT ON vf_params
 FOR EACH ROW BEGIN
   SET @last_param_id = LAST_INSERT_ID();
   SET @object_id = (SELECT p.object_id
                                FROM vf_params as p
                            WHERE p.id = @last_param_id
                            LIMIT 1);

   SET @object_type = (SELECT ot.name
                                    FROM vf_objects as o
                            LEFT JOIN vf_objectTypes as ot ON o.object_type_id = ot.object_type_id
                                WHERE o.object_id = @object_id
                                    LIMIT 1);

   SET @params = (SELECT GROUP_CONCAT(COALESCE(CAST(p.int_value AS CHAR), CAST(p.float_value AS CHAR), p.text_value, p.array_value, CAST(p.reference_value AS CHAR)) SEPARATOR '') AS VALUE 
                    FROM vf_params as p
                        WHERE p.object_id = @object_id
                            GROUP BY 'all'
                                LIMIT 1);

   SET @object_heap = CONCAT(@object_type, @params);
   SET @chksm = MD5(@object_heap);

   UPDATE vf_objects as o SET o.checksum = @chksm WHERE o.object_id = @object_id;
END
//
DELIMITER ;


ALTER TABLE vf_attributes
  ADD CONSTRAINT meta_attributes_ibfk_1 FOREIGN KEY (object_type_id) REFERENCES vf_objectTypes (object_type_id);

ALTER TABLE vf_objects
  ADD CONSTRAINT r_1 FOREIGN KEY (parent_id) REFERENCES vf_objects (object_id) ON DELETE CASCADE,
  ADD CONSTRAINT r_5 FOREIGN KEY (object_type_id) REFERENCES vf_objectTypes (object_type_id) ON DELETE CASCADE;

ALTER TABLE vf_params
  ADD CONSTRAINT r_6 FOREIGN KEY (object_id) REFERENCES vf_objects (object_id),
  ADD CONSTRAINT r_7 FOREIGN KEY (attr_id) REFERENCES vf_attributes (attr_id);

SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;