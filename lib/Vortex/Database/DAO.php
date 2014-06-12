<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 12-Jun-14
 * Time: 21:31
 */

namespace Vortex\Database;
use Vortex\Exceptions\DatabaseException;
use Vortex\Logger;

class DAO {
    const META_ATTRIBUTES_TABLE = 'vf_attributes';
    const META_OBJECT_TABLE = 'vf_objects';
    const META_OBJECT_TYPES_TABLE = 'vf_objectTypes';
    const META_PARAMS_TABLE = 'vf_params';

    private $fluentPDO;

    public function __construct(\FluentPDO $pdo) {
        $this->fluentPDO = $pdo;
    }

    public function insert($object) {
        /* Parsing received object */
        $objectData = $this->readObject($object);

        /* Checking DAO's tables */
        if (!$this->checkTables())
            $this->createMetaScheme();

        $data = $this->fluentPDO->from(DAO::META_OBJECT_TABLE)
                                ->where('checksum', $objectData['checksum'])
                                ->fetch();
        if ($data)
            return $data['object_id'];

        /* Starting transaction */
        $this->fluentPDO->getPdo()->beginTransaction();

        /* Reading object's metadata from database */
        $objectMeta = $this->getObjectMeta($objectData['object_type']);
        if(!$objectMeta)
            $objectMeta = $this->registerObject($objectData['object_type'], array_keys($objectData['attributes']));

        /* Writing object into metamodel */
        $query = $this->fluentPDO->insertInto(DAO::META_OBJECT_TABLE, array(
            'object_type_id'    =>  $objectMeta['object_type_id'],
            'checksum'          =>  $objectData['checksum']
        ));

        $object_id = $query->execute();

        /* Writing object's params */
        foreach ($objectMeta['attributes'] as $name => $attr_id) {
            $value = $objectData['attributes'][$name];
            $field = $this->getTextField($value);
            $toInsert = array(
                'object_id'     =>  $object_id,
                'attr_id'       =>  $attr_id,
                $field          =>  $value
            );
            $this->fluentPDO->insertInto(DAO::META_PARAMS_TABLE, $toInsert)->execute();
        }

        /* Commiting...whew */
        $this->fluentPDO->getPdo()->commit();
        return $object_id;
    }

    public function select($object_id) {
        $objectData = $this->fluentPDO->from(DAO::META_PARAMS_TABLE)
                                       ->where(DAO::META_PARAMS_TABLE . '.object_id', $object_id)
                                       ->leftJoin(DAO::META_ATTRIBUTES_TABLE . ' ON ' . DAO::META_PARAMS_TABLE . '.attr_id = ' . DAO::META_ATTRIBUTES_TABLE . '.attr_id')
                                       ->select(DAO::META_ATTRIBUTES_TABLE . '.*, ' . DAO::META_ATTRIBUTES_TABLE . '.name AS prop_name')
                                       ->leftJoin(DAO::META_OBJECT_TYPES_TABLE . ' ON ' . DAO::META_ATTRIBUTES_TABLE . '.object_type_id = ' . DAO::META_OBJECT_TYPES_TABLE . '.object_type_id')
                                       ->select(DAO::META_OBJECT_TYPES_TABLE . '.*')
                                       ->fetchAll();
        if (!$objectData)
            throw new DatabaseException('No such object id!');

        /* Creating and restoring object */
        $obj = new $objectData[0]['name']();
        $refObject = new \ReflectionObject($obj);

        $value = '';
        foreach ($objectData as $prop) {
            foreach ($prop as $key => $val) {
                if (!strlen($val))
                    continue;
                if (strpos($key, '_value') !== false) {
                    if (strpos($key, 'boolean') !== false)
                        $val = $val == 1 ? true : false;
                    $value = $val;
                }
            }
            $refProperty = $refObject->getProperty($prop['prop_name']);
            $refProperty->setAccessible(true);
            $refProperty->setValue($obj, $value);
        }

        return $obj;
    }

    public function find($object_type, $params) {
        $query = $this->fluentPDO->from(DAO::META_PARAMS_TABLE)
                                 ->leftJoin(DAO::META_ATTRIBUTES_TABLE . ' ON ' . DAO::META_PARAMS_TABLE . '.attr_id = ' . DAO::META_ATTRIBUTES_TABLE . '.attr_id')
                                 ->leftJoin(DAO::META_OBJECT_TABLE . ' ON ' . DAO::META_PARAMS_TABLE . '.object_id = ' . DAO::META_OBJECT_TABLE . '.object_id')
                                 ->leftJoin(DAO::META_OBJECT_TYPES_TABLE . ' ON ' . DAO::META_OBJECT_TABLE . '.object_type_id = ' . DAO::META_OBJECT_TYPES_TABLE . '.object_type_id')
                                 ->where(DAO::META_OBJECT_TYPES_TABLE . '.name', $object_type)
                                 ->select(DAO::META_OBJECT_TABLE . '.*');
        foreach ($params as $key => $value) {
            $query->where(DAO::META_ATTRIBUTES_TABLE . '.name', $key);
            $valueField = $this->getTextField($value);
            $query->where(DAO::META_PARAMS_TABLE . '.' . $valueField, $value);
        }
        $objects = $query->fetchAll();

        if (!$objects)
            return false;
        if (count($objects) == 1)
            return $objects[0]['object_id'];

        $ids = array();
        foreach ($objects as $obj)
            array_push($ids, $obj['object_id']);

        return $ids;
    }

    public function update($object_id, $set = array()) {
        $query = $this->fluentPDO->update(DAO::META_PARAMS_TABLE)
                                 ->leftJoin(DAO::META_ATTRIBUTES_TABLE . ' ON ' . DAO::META_PARAMS_TABLE . '.attr_id = ' . DAO::META_ATTRIBUTES_TABLE . '.attr_id')
                                 ->set($set)
                                 ->where(DAO::META_PARAMS_TABLE . '.object_id', $object_id)
                                 ->execute();
        return !empty($query);
    }

    public function delete($object_id) {
        $query = $this->fluentPDO->deleteFrom(DAO::META_OBJECT_TABLE)
                                 ->where('object_id', $object_id)
                                 ->execute();
        return !empty($query);
    }

    private function readObject($object) {
        if (!is_object($object))
            throw new \InvalidArgumentException('DAO is used only for saving objects!');
        $checksum = md5(json_encode($object));

        $name = get_class ($object);
        $nameEsc = str_replace('\\', "\\\\", $name);
        $raw = (array)$object;

        $attributes = array();
        foreach ($raw as $attr => $val) {
            $attr_name = trim(preg_replace('('.$nameEsc.'|\*|)', '', $attr));
            $attributes[$attr_name] = $val;
        }

        return array(
            'object_type'   =>  $name,
            'checksum'      =>  $checksum,
            'attributes'    =>  $attributes
        );
    }

    private function getObjectMeta($objectType) {
        $query = $this->fluentPDO->from(DAO::META_ATTRIBUTES_TABLE)
                                 ->leftJoin(DAO::META_OBJECT_TYPES_TABLE . ' ON ' . DAO::META_ATTRIBUTES_TABLE . '.object_type_id = ' . DAO::META_OBJECT_TYPES_TABLE . '.object_type_id')
                                 ->fetchAll();
        if (!$query)
            return false;

        $attr_ids = array();
        foreach ($query as $queryData)
            $attr_ids[$queryData['name']] = $queryData['attr_id'];

        return array(
            'object_type'       =>  $objectType,
            'object_type_id'    =>  $query[0]['object_type_id'],
            'attributes'        =>  $attr_ids
        );
    }

    private function registerObject($object_type, $attributes) {
        $objectID = $this->fluentPDO->insertInto(DAO::META_OBJECT_TYPES_TABLE, array(
            'name'   =>  $object_type
        ))->execute();

        $attr_ids = array();
        foreach ($attributes as $attrName) {
            $attr_id = $this->fluentPDO->insertInto(DAO::META_ATTRIBUTES_TABLE, array(
                'name'              =>  $attrName,
                'object_type_id'    =>  $objectID
            ));
            $attr_ids[$attrName] = $attr_id->execute();
        }
        return array(
            'object_type'       =>  $object_type,
            'object_type_id'    =>  $objectID,
            'attributes'        =>  $attr_ids
        );
    }

    private function createMetaScheme() {
        $queries[] = 'CREATE TABLE ' . DAO::META_ATTRIBUTES_TABLE . ' ( attr_id int(11) NOT NULL AUTO_INCREMENT, object_type_id int(11) NOT NULL DEFAULT \'0\', name varchar(24) NOT NULL DEFAULT \'0\', PRIMARY KEY (attr_id), UNIQUE KEY name (name), KEY object_type_id (object_type_id) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
        $queries[] = 'CREATE TABLE ' . DAO::META_OBJECT_TABLE . ' ( object_id int(11) NOT NULL AUTO_INCREMENT, parent_id int(11) DEFAULT NULL, object_type_id int(11) DEFAULT NULL, checksum char(32) NOT NULL, PRIMARY KEY (object_id), KEY r_5 (object_type_id), KEY r_1 (parent_id) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
        $queries[] = 'CREATE TABLE ' . DAO::META_OBJECT_TYPES_TABLE . ' ( object_type_id int(11) NOT NULL, name varchar(32) DEFAULT NULL, PRIMARY KEY (object_type_id), UNIQUE KEY name (name) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
        $queries[] = 'CREATE TABLE ' . DAO::META_PARAMS_TABLE . ' ( object_id int(11) NOT NULL, attr_id int(11) NOT NULL, text_value varchar(50) DEFAULT NULL, int_value int(11) DEFAULT NULL, float_value float DEFAULT NULL, boolean_value bit(1) DEFAULT NULL, KEY r_6 (object_id), KEY r_7 (attr_id) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

        $queries[] = 'ALTER TABLE ' . DAO::META_ATTRIBUTES_TABLE . ' ADD CONSTRAINT meta_attributes_ibfk_1 FOREIGN KEY (object_type_id) REFERENCES ' . DAO::META_OBJECT_TYPES_TABLE . ' (object_type_id);';
        $queries[] = 'ALTER TABLE ' . DAO::META_OBJECT_TABLE . ' ADD CONSTRAINT r_1 FOREIGN KEY (parent_id) REFERENCES ' . DAO::META_OBJECT_TABLE . ' (object_id) ON DELETE CASCADE, ADD CONSTRAINT r_5 FOREIGN KEY (object_type_id) REFERENCES ' . DAO::META_OBJECT_TYPES_TABLE . ' (object_type_id) ON DELETE CASCADE;';
        $queries[] = 'ALTER TABLE ' . DAO::META_PARAMS_TABLE . ' ADD CONSTRAINT r_6 FOREIGN KEY (object_id) REFERENCES ' . DAO::META_OBJECT_TABLE . ' (object_id), ADD CONSTRAINT r_7 FOREIGN KEY (attr_id) REFERENCES ' . DAO::META_ATTRIBUTES_TABLE . ' (attr_id);';

        foreach ($queries as $query) {
            $statement = $this->fluentPDO->getPdo()->query($query);
            if (!$statement)
                throw new DatabaseException('Error occurred while recreating meta-model scheme');
        }

        return true;
    }

    private function checkTables() {
        $check = array(DAO::META_ATTRIBUTES_TABLE, DAO::META_OBJECT_TABLE, DAO::META_OBJECT_TYPES_TABLE, DAO::META_PARAMS_TABLE);

        foreach ($check as $table) {
            $query = $this->fluentPDO->getPdo()->query('SELECT 1 FROM ' . $table);
            if (!$query)
                return false;
        }
        return true;
    }

    private function getTextField(&$value) {
        switch (gettype($value)) {
            case "boolean":
                $valueField = 'boolean_value';
                $value = $value == true ? 1 : 0;
                break;
            case "integer":
                $valueField = 'int_value';
                break;
            case "double":
                $valueField = 'float_value';
                $value = (float) $value;
                break;
            default:
                $valueField = 'text_value';
                $value = (string)$value;
                break;
        }
        return $valueField;
    }
}