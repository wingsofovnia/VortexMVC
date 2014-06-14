<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 12-Jun-14
 * Time: 21:31
 */

namespace Vortex\Database\DAO;
use Vortex\Exceptions\DAOException;

class Manager {
    const META_ATTRIBUTES_TABLE = 'vf_attributes';
    const META_OBJECT_TABLE = 'vf_objects';
    const META_OBJECT_TYPES_TABLE = 'vf_objectTypes';
    const META_PARAMS_TABLE = 'vf_params';
    const META_REFERENCES_TABLE = 'vf_references';

    private $fluentPDO;

    public function __construct(\FluentPDO $pdo) {
        $this->fluentPDO = $pdo;
    }

    public function insert(Entity $object) {
        /* Parsing received object */
        $objectData = $this->readObject($object);

        /* Checking DAO's tables */
        if (!$this->checkTables())
            $this->createMetaScheme();

        $data = $this->fluentPDO->from(Manager::META_OBJECT_TABLE)
                                ->where('checksum', $objectData['checksum'])
                                ->fetch();
        if ($data)
            return $data['object_id'];

        /* Starting transaction */
        if (!$this->fluentPDO->getPdo()->inTransaction())
            $this->fluentPDO->getPdo()->beginTransaction();

        /* Reading object's metadata from database */
        $objectMeta = $this->getObjectMeta($objectData['object_type']);
        if(!$objectMeta)
            $objectMeta = $this->registerObject($objectData['object_type'], array_keys($objectData['attributes']));

        /* Writing object into metamodel */
        $query = $this->fluentPDO->insertInto(Manager::META_OBJECT_TABLE, array(
            'object_type_id'    =>  $objectMeta['object_type_id'],
            'checksum'          =>  $objectData['checksum']
        ));

        $object_id = $query->execute();

        /* Writing object's params */
        foreach ($objectMeta['attributes'] as $name => $attr_id) {
            $value = $objectData['attributes'][$name];
            $prep = $this->prepareParam($value);

            if (is_object($prep['value']))
                $prep['value'] = $this->insert($prep['value']);

            $toInsert = array(
                'object_id'     =>  $object_id,
                'attr_id'       =>  $attr_id,
                $prep['field']  =>  $prep['value']
            );
            $this->fluentPDO->insertInto(Manager::META_PARAMS_TABLE, $toInsert)->execute();
        }

        /* Committing...whew */
        if ($this->fluentPDO->getPdo()->inTransaction())
            $this->fluentPDO->getPdo()->commit();
        return $object_id;
    }

    public function select($object_id) {
        /* Reading attributes of object */
        $objectData = $this->fluentPDO->from(Manager::META_PARAMS_TABLE)
                                       ->where(Manager::META_PARAMS_TABLE . '.object_id', $object_id)
                                       ->leftJoin(Manager::META_ATTRIBUTES_TABLE . ' ON ' . Manager::META_PARAMS_TABLE . '.attr_id = ' . Manager::META_ATTRIBUTES_TABLE . '.attr_id')
                                       ->select(Manager::META_ATTRIBUTES_TABLE . '.*, ' . Manager::META_ATTRIBUTES_TABLE . '.name AS prop_name')
                                       ->leftJoin(Manager::META_OBJECT_TYPES_TABLE . ' ON ' . Manager::META_ATTRIBUTES_TABLE . '.object_type_id = ' . Manager::META_OBJECT_TYPES_TABLE . '.object_type_id')
                                       ->select(Manager::META_OBJECT_TYPES_TABLE . '.*')
                                       ->fetchAll();
        if (!$objectData)
            throw new DAOException('No such object id!');

        /* Processing raw data */
        $object_type = $objectData[0]['name'];

        $attributes = array();
        foreach ($objectData as $props)
            $attributes[$props['prop_name']] = $this->readParamField($props);

        $attributes['_object_id'] = $objectData[0]['object_id'];

        /* Returning cooked object */
        return $this->createObject($object_type, $attributes);
    }

    public function findObjectIds($object_type, $params) {
        $query = $this->fluentPDO->from(Manager::META_PARAMS_TABLE)
                                 ->leftJoin(Manager::META_ATTRIBUTES_TABLE . ' ON ' . Manager::META_PARAMS_TABLE . '.attr_id = ' . Manager::META_ATTRIBUTES_TABLE . '.attr_id')
                                 ->leftJoin(Manager::META_OBJECT_TABLE . ' ON ' . Manager::META_PARAMS_TABLE . '.object_id = ' . Manager::META_OBJECT_TABLE . '.object_id')
                                 ->leftJoin(Manager::META_OBJECT_TYPES_TABLE . ' ON ' . Manager::META_OBJECT_TABLE . '.object_type_id = ' . Manager::META_OBJECT_TYPES_TABLE . '.object_type_id')
                                 ->where(Manager::META_OBJECT_TYPES_TABLE . '.name', $object_type)
                                 ->select(Manager::META_OBJECT_TABLE . '.*');
        foreach ($params as $key => $value) {
            $query->where(Manager::META_ATTRIBUTES_TABLE . '.name', $key);
            $prep = $this->prepareParam($value);
            $query->where(Manager::META_PARAMS_TABLE . '.' . $prep['field'], $prep['value']);
        }

        $objects = $query->fetchAll();
        if (!$objects)
            return false;

        $ids = array();
        foreach ($objects as $obj)
            array_push($ids, $obj['object_id']);

        return $ids;
    }

    public function findObjects($object_type, $params) {
        $object_ids = $this->findObjectIds($object_type, $params);
        if (!$object_ids)
            return false;

        $objectsData = $this->fluentPDO->from(Manager::META_PARAMS_TABLE)
                                       ->leftJoin(Manager::META_ATTRIBUTES_TABLE . ' ON ' . Manager::META_PARAMS_TABLE . '.attr_id = ' . Manager::META_ATTRIBUTES_TABLE . '.attr_id')
                                       ->leftJoin(Manager::META_OBJECT_TABLE . ' ON ' . Manager::META_PARAMS_TABLE . '.object_id = ' . Manager::META_OBJECT_TABLE . '.object_id')
                                       ->leftJoin(Manager::META_OBJECT_TYPES_TABLE . ' ON ' . Manager::META_OBJECT_TABLE . '.object_type_id = ' . Manager::META_OBJECT_TYPES_TABLE . '.object_type_id')
                                       ->where(Manager::META_OBJECT_TABLE . '.object_id', $object_ids)
                                       ->select(Manager::META_OBJECT_TABLE . '.*')
                                       ->select(Manager::META_ATTRIBUTES_TABLE . '.name')
                                       ->fetchAll();

        $rawObjects = array();
        foreach ($objectsData as $data) {
            $rawObjects[$data['object_id']][$data['name']] = $this->readParamField($data);
        }

        $cookedObjects = array();
        foreach ($rawObjects as $object_id => $attributes) {
            $attributes['_object_id'] = $object_id;
            $cookedObjects[] = $this->createObject($object_type, $attributes);
        }

        return $cookedObjects;
    }

    public function update($object_id, $set = array()) {
        if (!$this->fluentPDO->getPdo()->inTransaction())
            $this->fluentPDO->getPdo()->beginTransaction();

        $attributes = $this->getObjectAttributes($object_id);
        foreach ($set as $param_name => $param_value) {
            $prep = $this->prepareParam($param_value);

            $delete = $this->fluentPDO->deleteFrom(Manager::META_PARAMS_TABLE)
                                      ->where('object_id', $object_id)
                                      ->where('attr_id', $attributes[$param_name])
                                      ->execute();
            if (!$delete) {
                $this->fluentPDO->getPdo()->rollBack();
                throw new DAOException('Deleting param #' . $param_name . '# failed!');
            }

            $toInsert = array(
                'object_id'     =>  $object_id,
                'attr_id'       =>  $attributes[$param_name],
                $prep['field']  =>  $prep['value']
            );
            $this->fluentPDO->insertInto(Manager::META_PARAMS_TABLE, $toInsert)
                            ->execute();
        }
        if ($this->fluentPDO->getPdo()->inTransaction())
            $this->fluentPDO->getPdo()->commit();
        return true;
    }

    public function delete($object_id) {
        $query = $this->fluentPDO->deleteFrom(Manager::META_OBJECT_TABLE)
                                 ->where('object_id', $object_id)
                                 ->execute();
        return !empty($query);
    }

    public function readObject($object) {
        if (!is_object($object))
            throw new \InvalidArgumentException('DAO is used only for saving objects!');

        if (!is_a($object, 'Vortex\Database\DAO\Entity'))
            throw new DAOException('Object should extends DAOEntity abstract class!');

        $checksum = md5(json_encode($object));

        $name = get_class ($object);
        $nameEsc = str_replace('\\', "\\\\", $name);
        $raw = (array)$object;

        $attributes = array();
        foreach ($raw as $attr => $val) {
            $attr_name = trim(preg_replace('('.$nameEsc.'|\*|)', '', $attr));
            if ($attr_name[0] === '_')
                continue;
            $attributes[$attr_name] = $val;
        }

        return array(
            'object_type'   =>  $name,
            'checksum'      =>  $checksum,
            'attributes'    =>  $attributes
        );
    }

    public function createObject($object_type, $attributes) {
        $obj = new $object_type();
        $refObject = new \ReflectionObject($obj);

        foreach ($attributes as $attr => $value) {
            $refProperty = $refObject->getProperty($attr);
            $refProperty->setAccessible(true);
            $refProperty->setValue($obj, $value);
        }
        return $obj;
    }

    private function getObjectMeta($objectType) {
        $query = $this->fluentPDO->from(Manager::META_ATTRIBUTES_TABLE)
                                 ->leftJoin(Manager::META_OBJECT_TYPES_TABLE . ' ON ' . Manager::META_ATTRIBUTES_TABLE . '.object_type_id = ' . Manager::META_OBJECT_TYPES_TABLE . '.object_type_id')
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
        $objectID = $this->fluentPDO->insertInto(Manager::META_OBJECT_TYPES_TABLE, array(
            'name'   =>  $object_type
        ))->execute();

        $attr_ids = array();
        foreach ($attributes as $attrName) {
            $attr_id = $this->fluentPDO->insertInto(Manager::META_ATTRIBUTES_TABLE, array(
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
        $queries[] = 'SET foreign_key_checks = 0;';
        $queries[] = 'DROP TABLE IF EXISTS ' . Manager::META_ATTRIBUTES_TABLE . ';';
        $queries[] = 'DROP TABLE IF EXISTS ' . Manager::META_OBJECT_TABLE . ';';
        $queries[] = 'DROP TABLE IF EXISTS ' . Manager::META_OBJECT_TYPES_TABLE . ';';
        $queries[] = 'DROP TABLE IF EXISTS ' . Manager::META_PARAMS_TABLE . ';';

        $queries[] = 'CREATE TABLE ' . Manager::META_ATTRIBUTES_TABLE . ' ( attr_id int(11) NOT NULL AUTO_INCREMENT, object_type_id int(11) NOT NULL DEFAULT \'0\', name varchar(24) NOT NULL DEFAULT \'0\', PRIMARY KEY (attr_id), UNIQUE KEY name (name), KEY object_type_id (object_type_id) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
        $queries[] = 'CREATE TABLE ' . Manager::META_OBJECT_TABLE . ' ( object_id int(11) NOT NULL AUTO_INCREMENT, parent_id int(11) DEFAULT NULL, object_type_id int(11) DEFAULT NULL, checksum char(32) NOT NULL, PRIMARY KEY (object_id), KEY r_5 (object_type_id), KEY r_1 (parent_id) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
        $queries[] = 'CREATE TABLE ' . Manager::META_OBJECT_TYPES_TABLE . ' ( object_type_id int(11) NOT NULL, name varchar(32) DEFAULT NULL, PRIMARY KEY (object_type_id), UNIQUE KEY name (name) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
        $queries[] = 'CREATE TABLE ' . Manager::META_PARAMS_TABLE . ' ( object_id int(11) NOT NULL, attr_id int(11) NOT NULL, int_value int(11) DEFAULT NULL, boolean_value bit(1) DEFAULT NULL, float_value float DEFAULT NULL, text_value varchar(50) DEFAULT NULL, array_value varchar(512) DEFAULT NULL, reference_value int(11) DEFAULT NULL, KEY r_6 (object_id), KEY r_7 (attr_id) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
        //$queries[] = 'CREATE TABLE ' . DAO::META_REFERENCES_TABLE . ' ( attr_id int(10) NOT NULL, object_id int(10) NOT NULL, reference int(10) unsigned DEFAULT NULL, KEY FK_vf_reference_vf_params (attr_id), KEY FK_vf_reference_vf_objects (object_id) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

        $queries[] = 'SET foreign_key_checks = 1;';
        $queries[] = 'ALTER TABLE ' . Manager::META_ATTRIBUTES_TABLE . ' ADD CONSTRAINT meta_attributes_ibfk_1 FOREIGN KEY (object_type_id) REFERENCES ' . Manager::META_OBJECT_TYPES_TABLE . ' (object_type_id);';
        $queries[] = 'ALTER TABLE ' . Manager::META_OBJECT_TABLE . ' ADD CONSTRAINT r_1 FOREIGN KEY (parent_id) REFERENCES ' . Manager::META_OBJECT_TABLE . ' (object_id) ON DELETE CASCADE, ADD CONSTRAINT r_5 FOREIGN KEY (object_type_id) REFERENCES ' . Manager::META_OBJECT_TYPES_TABLE . ' (object_type_id) ON DELETE CASCADE;';
        $queries[] = 'ALTER TABLE ' . Manager::META_PARAMS_TABLE . ' ADD CONSTRAINT r_6 FOREIGN KEY (object_id) REFERENCES ' . Manager::META_OBJECT_TABLE . ' (object_id), ADD CONSTRAINT r_7 FOREIGN KEY (attr_id) REFERENCES ' . Manager::META_ATTRIBUTES_TABLE . ' (attr_id);';
        //$queries[] = 'ALTER TABLE ' . DAO::META_REFERENCES_TABLE . ' ADD CONSTRAINT FK_vf_reference_vf_params FOREIGN KEY (attr_id) REFERENCES vf_params (attr_id), ADD CONSTRAINT FK_vf_reference_vf_objects FOREIGN KEY (object_id) REFERENCES vf_objects (object_id);';

        $statement = $this->fluentPDO->getPdo()->query(implode('', $queries));
        if (!$statement)
            throw new DAOException('Error occurred while recreating meta-model scheme');

        return true;
    }

    private function checkTables() {
        $check = array(Manager::META_ATTRIBUTES_TABLE, Manager::META_OBJECT_TABLE, Manager::META_OBJECT_TYPES_TABLE, Manager::META_PARAMS_TABLE);

        foreach ($check as $table) {
            $query = $this->fluentPDO->getPdo()->query('SELECT 1 FROM ' . $table);
            if (!$query)
                return false;
        }
        return true;
    }

    private function prepareParam($value) {
        $preparedValue = $value;
        switch (gettype($value)) {
            case "boolean":
                $field = 'boolean_value';
                $preparedValue = $value == true ? 1 : 0;
                break;
            case "integer":
                $field = 'int_value';
                break;
            case "double":
                $field = 'float_value';
                $preparedValue = (float) $value;
                break;
            case "float":
                $field = 'float_value';
                break;
            case "array":
                $field = 'array_value';
                $preparedValue = json_encode($field);
                break;
            case "object":
                $field = 'reference_value';
                break;
            default:
                $field = 'text_value';
                $preparedValue = (string)$value;
                break;
        }
        return array(
            'field'     =>  $field,
            'value'     =>  $preparedValue
        );
    }

    private function readParamField($paramFields) {
        $filtered = array_intersect_key($paramFields, array_flip(array(
            'int_value', 'boolean_value', 'text_value', 'float_value', 'array_value', 'reference_value'
        )));
        foreach ($filtered as $name => $value) {
            if (!empty($value)) {
                switch ($name) {
                    case "int_value":
                        $param = (int)$value;
                        break;
                    case "boolean_value":
                        $param = (boolean)$value;
                        break;
                    case "float_value":
                        $param = (float)$value;
                        break;
                    case "array_value":
                        $param = json_decode($value);
                        break;
                    case "reference_value":
                        $param = new LazyObject($value);
                        break;
                    default:
                        $param = (string)$value;
                        break;
                }
                return $param;
            }
        }
        return false;
    }

    private function getObjectAttributes($object_id) {
        $adata = $this->fluentPDO->from(Manager::META_ATTRIBUTES_TABLE)
                                 ->leftJoin(Manager::META_OBJECT_TABLE . ' ON ' . Manager::META_ATTRIBUTES_TABLE . '.object_type_id = ' . Manager::META_OBJECT_TABLE . '.object_type_id')
                                 ->where(Manager::META_OBJECT_TABLE . '.object_id', $object_id)
                                 ->fetchAll();
        $attributes = array();
        foreach ($adata as $value) {
            $attributes[$value['name']] = $value['attr_id'];
        }
        return $attributes;
    }
}