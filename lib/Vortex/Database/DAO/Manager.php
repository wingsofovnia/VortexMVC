<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 12-Jun-14
 *
 * @package Vortex
 * @subpackage Database
 * @subpackage DAO
 */

namespace Vortex\Database\DAO;
use Vortex\Exceptions\DAOException;

class Manager {
    const META_ATTRIBUTES_TABLE = 'vf_attributes';
    const META_OBJECT_TABLE = 'vf_objects';
    const META_OBJECT_TYPES_TABLE = 'vf_objectTypes';
    const META_PARAMS_TABLE = 'vf_params';
    const META_REFERENCES_TABLE = 'vf_references';
    const META_UPDATE_TRIGGER_NAME = 'vf_update_checksum';

    private $fluentPDO;

    public function __construct(\FluentPDO $pdo) {
        $this->fluentPDO = $pdo;
    }

    /**
     * Serializes object into metamodel
     * @param Entity $object object <? extends Entity> to serialize
     * @return int object_id of new record
     * @throws \InvalidArgumentException
     * @throws \Vortex\Exceptions\DAOException
     */
    public function insert(Entity $object) {
        /* Parsing received object */
        $objectData = $this->readObject($object);

        /* Is it already exists? */
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
            $toInsert = array(
                'object_id'     =>  $object_id,
                'attr_id'       =>  $attr_id,
                $value['field'] =>  $value['value']
            );
            $this->fluentPDO->insertInto(Manager::META_PARAMS_TABLE, $toInsert)->execute();
        }

        /* Committing...whew */
        if ($this->fluentPDO->getPdo()->inTransaction())
            $this->fluentPDO->getPdo()->commit();
        return $object_id;
    }

    /**
     * Unserializes object from metamodel to PHP Entity object
     * @param int $object_id an id of the object to unserialize
     * @return Entity model object <? extends Entity>
     * @throws DAOException
     */
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

    /**
     * Finds object_id's of object, that meets the search criteria
     * @param string $object_type name of the object_type
     * @param array $params array of attr_name => attr_value pairs
     * @return array|bool array of ids or false, if none found
     */
    public function findObjectIds($object_type, $params) {
        $attributes = array();
        $values = array();
        foreach ($params as $key => $value) {
            $attributes[] = $key;
            $values[Manager::META_PARAMS_TABLE . '.' . $value['field']][] = $value['value'];
        }

        $query = $this->fluentPDO->from(Manager::META_PARAMS_TABLE)
                                 ->leftJoin(Manager::META_ATTRIBUTES_TABLE . ' ON ' . Manager::META_PARAMS_TABLE . '.attr_id = ' . Manager::META_ATTRIBUTES_TABLE . '.attr_id')
                                 ->leftJoin(Manager::META_OBJECT_TABLE . ' ON ' . Manager::META_PARAMS_TABLE . '.object_id = ' . Manager::META_OBJECT_TABLE . '.object_id')
                                 ->leftJoin(Manager::META_OBJECT_TYPES_TABLE . ' ON ' . Manager::META_OBJECT_TABLE . '.object_type_id = ' . Manager::META_OBJECT_TYPES_TABLE . '.object_type_id')
                                 ->where(Manager::META_OBJECT_TYPES_TABLE . '.name', $object_type)
                                 ->select(Manager::META_OBJECT_TABLE . '.*')
                                 ->where(Manager::META_ATTRIBUTES_TABLE . '.name', $attributes);

        foreach ($values as $field => $val)
            $query->where($field, $val);

        $objects = $query->fetchAll('object_id');
        if (!$objects)
            return false;

        $ids = array();
        foreach ($objects as $obj)
            array_push($ids, $obj['object_id']);

        return $ids;
    }

    /**
     * The same with @see findObjectIds but returns cooked Entity objects
     * @param string $object_type name of the object_type
     * @param array $params array of attr_name => attr_value pairs
     * @return array|bool array of objects <? extends Entity> or false, if none found
     */
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

    /**
     * Updates the existing record of object in metamodel
     * Info! This method triggers a mysql trigger, to update md5 checksum of the object
     * @param int $object_id id of the object to update
     * @param array $set array of param => value pairs to update
     * @return bool true, if everything is ok
     * @throws \Vortex\Exceptions\DAOException
     */
    public function update($object_id, $set = array()) {
        if (!$this->fluentPDO->getPdo()->inTransaction())
            $this->fluentPDO->getPdo()->beginTransaction();

        $attributes = $this->getObjectAttributes($object_id);
        foreach ($set as $param_name => $value) {

            /* Deleting previous data */
            $delete = $this->fluentPDO->deleteFrom(Manager::META_PARAMS_TABLE)
                                      ->where('object_id', $object_id)
                                      ->where('attr_id', $attributes[$param_name])
                                      ->execute();
            if (!$delete) {
                $this->fluentPDO->getPdo()->rollBack();
                throw new DAOException('Deleting param #' . $param_name . '# failed!');
            }

            /* Inserting data */
            $toInsert = array(
                'object_id'     =>  $object_id,
                'attr_id'       =>  $attributes[$param_name],
                $value['field'] =>  $value['value']
            );
            $this->fluentPDO->insertInto(Manager::META_PARAMS_TABLE, $toInsert)
                            ->execute();
        }

        /* Fixing changes */
        if ($this->fluentPDO->getPdo()->inTransaction())
            $this->fluentPDO->getPdo()->commit();
        return true;
    }

    /**
     * Deletes an object from metamodel
     * @param int $object_id an id of the object
     * @return bool
     */
    public function delete($object_id) {
        $query = $this->fluentPDO->deleteFrom(Manager::META_OBJECT_TABLE)
                                 ->where('object_id', $object_id)
                                 ->execute();
        if (empty($query))
            return false;

        $query = $this->fluentPDO->deleteFrom(Manager::META_PARAMS_TABLE)
                                 ->where('object_id', $object_id)
                                 ->execute();
        return !empty($query);
    }

    /* Object transformation section */

    /**
     * Parses an Entity object
     * @param Entity $object a object to parse
     * @return array parsed object
     * @throws \InvalidArgumentException
     * @throws \Vortex\Exceptions\DAOException
     */
    public function readObject($object) {
        if (!is_object($object))
            throw new \InvalidArgumentException('DAO is used only for saving objects!');

        if (!is_a($object, 'Vortex\Database\DAO\Entity'))
            throw new DAOException('Object should extends DAOEntity abstract class!');

        /* This var collects data-picks from object, for making md5 checksum */
        $heap = '';

        $name = get_class($object);
        $nameEsc = str_replace('\\', "\\\\", $name);
        $raw = (array)$object;
        $heap .= $name;

        $attributes = array();
        foreach ($raw as $attr => $val) {
            $attr_name = trim(preg_replace('('.$nameEsc.'|\*|)', '', $attr));
            /* Skip attributes with _ prefix (system) */
            if ($attr_name[0] === '_')
                continue;

            /* Determining a name of a field for this kind of data in metamodel */
            $field = $this->determineParamField($val);

            /* Processing complex types of data */
            $val = $this->prepareValue($val);

            $attributes[$attr_name] = array(
                'field'     =>  $field,
                'value'     =>  $val
            );

            $heap .= $val;
        }
        return array(
            'object_type'   =>  $name,
            'checksum'      =>  md5($heap),
            'attributes'    =>  $attributes
        );
    }

    /**
     * Converts a value into appropriate for metamodel type
     * @param mixed $value a value
     * @return mixed a prepared value
     */
    private function prepareValue($value) {
        if (is_object($value) && is_a($value, 'Vortex\Database\DAO\Entity')) {
            $child_object_id = $value->getObjectId();
            if (!$child_object_id)
                $value->save();
            $value = $value->getObjectId();
        } else if (is_array($value)) {
            $this->lazyArray($value);
            $value = serialize($value);
        } else if (is_bool($value)) {
            $value = $value == true ? 1 : 0;
        } else if (is_resource($value) || empty($value)) {
            $value = '';
        }
        return $value;
    }

    /**
     * Creates an user Entity object and fills it with params
     * @param string $object_type name of the object's class
     * @param array $attributes   array of property-value pairs
     * @return Entity an object, with specified object type
     * @throws DAOException
     */
    public function createObject($object_type, $attributes) {
        if (!class_exists($object_type))
            throw new DAOException('Creating object #' . $object_type . '# failed because of missing class!');
        $obj = new $object_type();
        $refObject = new \ReflectionObject($obj);

        foreach ($attributes as $attr => $value) {
            $refProperty = $refObject->getProperty($attr);
            $refProperty->setAccessible(true);
            $refProperty->setValue($obj, $value);
        }
        return $obj;
    }

    /**
     * Registers new object type
     * @param string $object_type name of class
     * @param array $attributes array of properties of this class
     * @return array array of object type metadata
     */
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

    private function lazyArray(array &$array) {
        array_walk_recursive($array, function(&$leaf) {
            if (!is_a($leaf, 'Vortex\Database\DAO\Entity'))
                return;
            $object_id = $leaf->getObjectId();
            if (!$object_id)
                $leaf->save();
            $leaf = new LazyEntity($leaf->getObjectId());
        });
    }

    /**
     * Determines in what column of metamodel this value should be placed
     * @param mixed $value a value
     * @return string a name of a column in META_PARAMS_TABLE
     */
    private function determineParamField($value) {
        $fields = array(
            'boolean'       =>  'boolean_value',
            'integer'       =>  'int_value',
            'double'        =>  'float_value',
            'array'         =>  'array_value',
            'object'        =>  'reference_value',
            'string'        =>  'text_value'
        );

        $valueType = gettype($value);
        return isset($fields[$valueType]) ? $fields[$valueType] : $fields['string'];
    }

    /**
     * Reads a value from a set of values with META_PARAMS_TABLE value columns as their's keys
     * @param array $paramFields array of (.*)_value => value pairs
     * @return mixed a value
     */
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
                        $param = unserialize($value);
                        break;
                    case "reference_value":
                        $param = new LazyEntity($value);
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

    /**
     * Gets a set of attributes of particular object type by object_id
     * @param int $object_id object_id
     * @return array an array of attr_name => attr_id values
     */
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

    /**
     * Reads object type metadata from db, such as object_type_id and it's attributes
     * @param string $objectType name of class
     * @return array|bool array of metadata or false, if object was not registered in metamodel
     */
    private function getObjectMeta($objectType) {
        $query = $this->fluentPDO->from(Manager::META_ATTRIBUTES_TABLE)
                                 ->leftJoin(Manager::META_OBJECT_TYPES_TABLE . ' ON ' . Manager::META_ATTRIBUTES_TABLE . '.object_type_id = ' . Manager::META_OBJECT_TYPES_TABLE . '.object_type_id')
                                 ->where(Manager::META_OBJECT_TYPES_TABLE . '.name', $objectType)
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
}