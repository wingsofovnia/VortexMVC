<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 07-Jun-14
 * Time: 14:04
 */
require_once LIB_PATH . '/FluentPDO/FluentPDO.php';

class Vortex_Database_PDO extends FluentPDO {
    const CACHE_NAMESPACE_TAG = 'pdo';
    const META_OBJECT_TYPES_CACHE_TAG = 'Meta_ObjectTypes';
    const META_ATTRIBUTES_CACHE_TAG = 'Meta_Attributes';

    protected $config;
    protected $cache;

    function __construct(PDO $pdo, FluentStructure $structure = null) {
        parent::__construct($pdo, $structure);
        $this->config = Vortex_Config::getInstance();

        $this->cache = new Vortex_Service_Cache(new Vortex_Service_Drivers_FileStorage());
        $this->cache->setLifeTime(Vortex_Service_Cache::UNLIMITED_LIFETIME);
        $this->cache->setNamespace(Vortex_Database_PDO::CACHE_NAMESPACE_TAG);
    }

    /**
     * Serialize object to meta-model
     * @param Vortex_Service_ISerializable $object object to save
     * @return int Id of the serialized object
     * @throws Vortex_Exception_IllegalArgument if object is null
     * @throws Vortex_Exception_DatabaseError if such object has been already serialized
     */
    public function insert(Vortex_Service_ISerializable $object) {
        if (!is_object($object))
            throw new Vortex_Exception_IllegalArgument('Object should be not null');
        /* Starting transaction and getting object's data */
        $this->getPdo()->beginTransaction();
        $className = get_class($object);
        $ref = new ReflectionObject($object);
        $props = $ref->getProperties();

        $toSerialize = array();
        foreach ($props as $prop) {
            $propName = $prop->getName();
            $objProp = $ref->getProperty($propName);
            $objProp->setAccessible(true);
            $toSerialize[$propName] = $objProp->getValue($object);
        }
        $checksum = md5(serialize($toSerialize) . $className);
        $data = $this->from($this->config->getMetaObjectTable())
                     ->where('checksum', $checksum)
                     ->fetch();
        if ($data)
            throw new Vortex_Exception_DatabaseError('Such object has been already serialized!');

        //TODO: getObjectTypeId and getAttributeIds should be merged
        $objectTypeID = $this->getObjectTypeId($className);
        if ($objectTypeID == null) {
            $newRecord = $this->registerObject($className, array_keys($toSerialize));
            $objectTypeID = $newRecord['object_type_id'];
            $attributes = $newRecord['attr_ids'];
        } else {
            $attributes = $this->getAttributeIds($objectTypeID);
        }

        /* Writing object */
        $query = $this->insertInto($this->config->getMetaObjectTable(), array(
            'object_type_id'    =>  $objectTypeID,
            'checksum'          =>  $checksum
        ));
        $objectID = $query->execute();


        /* Writing object's attributes */
        foreach ($toSerialize as $attrName => $attrVal) {
            $valueField = $this->getTextField($attrVal);
            $toInsert =  array(
                'object_id'     =>  $objectID,
                'attr_id'       =>  $attributes[$attrName]
            );
            if ($valueField != null)
                $toInsert[$valueField] = $attrVal;
            $this->insertInto($this->config->getMetaParamsTable(), $toInsert)->execute();
        }

        /* Commiting...whew */
        $this->getPdo()->commit();
        return $objectID;
    }

    /**
     * Selects serialized object from meta-model by ID
     * @param $objectId id of the object
     * @return Vortex_Service_ISerializable object
     * @throws Vortex_Exception_DatabaseError if no such object
     */
    public function select($objectId) {
        $attr_t = $this->config->getMetaAttributesTable();
        $param_t = $this->config->getMetaParamsTable();
        $types_t = $this->config->getMetaObjectTypesTable();

        $objectData = $this->from($param_t)
                           ->where($param_t . '.object_id', $objectId)
                           ->leftJoin($attr_t . ' ON ' . $param_t . '.attr_id = ' . $attr_t . '.attr_id')
                           ->select($attr_t . '.*, ' . $attr_t . '.name AS prop_name')
                           ->leftJoin($types_t . ' ON ' . $attr_t . '.object_type_id = ' . $types_t . '.object_type_id')
                           ->select($types_t . '.*');
        $checksum = md5($objectData->getQuery(false));
        $data = $this->cache->load($checksum);
        $objectData = $data ? $data : $objectData->fetchAll();
        $this->cache->save($checksum, $objectData);

        if (!$objectData)
            throw new Vortex_Exception_DatabaseError('No such object id!');

        /* Creating and restoring object */
        $obj = new $objectData[0]['name']();
        $refObject = new ReflectionObject($obj);
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

    /**
     * Gets id of serialized object by it's params
     * @param string $objectType name of the object type to search
     * @param array $params assoc array of search params
     * @return array|bool|int false, if no object; int id (if one object) or array of id's
     */
    public function find($objectType, $params) {
        $attr_t = $this->config->getMetaAttributesTable();
        $types_t = $this->config->getMetaObjectTypesTable();
        $obj_t = $this->config->getMetaObjectTable();
        $param_t = $this->config->getMetaParamsTable();

        $query = $this->from($types_t)
                      ->where($types_t . '.name', $objectType)
                      ->leftJoin($obj_t . ' ON ' . $types_t . '.object_type_id = ' . $obj_t . '.object_type_id')
                      ->leftJoin($attr_t . ' ON ' . $types_t . '.object_type_id = ' . $attr_t . '.object_type_id')
                      ->leftJoin($param_t . ' ON ' . $attr_t . '.attr_id = ' . $param_t . '.attr_id')
                      ->select($obj_t . '.*');
        foreach ($params as $key => $value) {
            $query->where($attr_t . '.name', $key);
            $valueField = $this->getTextField($value);
            $query->where($param_t . '.' . $valueField, $value);
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

    /**
     * Create UPDATE query or update serialized object in meta-model
     * @param string|int $what name of the table or object id
     * @param array $set params to update
     * @param null $primaryKey
     * @return bool|UpdateQuery
     */
    public function update($what, $set = array(), $primaryKey = null) {
        if (is_numeric($what)) {
            $attr_t = $this->config->getMetaAttributesTable();
            $params_t = $this->config->getMetaParamsTable();
            $query = parent::update($params_t)
                           ->leftJoin($attr_t . ' ON ' . $params_t . '.attr_id = ' . $attr_t . '.attr_id')
                           ->set($set)
                           ->where($params_t . '.object_id', $what)
                           ->execute();
            return !empty($query);
        } else {
            return parent::update($what, $set, $primaryKey);
        }
    }

    /**
     * Create DELETE query or delete object by id from meta-model
     * @param string|int $what table name of object id
     * @param null $primaryKey
     * @return bool|DeleteQuery
     */
    public function delete($what, $primaryKey = null) {
        if (is_numeric($what)) {
            $query = $this->deleteFrom($this->config->getMetaObjectTable())
                          ->where('object_id', $what)
                          ->execute();
            return !empty($query);
        } else {
            return parent::delete($what, $primaryKey);
        }
    }

    /**
     * Register new object in meta-model
     * @param $name name of the class
     * @param $attributes attributes of the class
     * @return array|bool object_type_id and attr_ids of the new object, or false, if such object already registred
     */
    public function registerObject($name, $attributes) {
        if ($this->getObjectTypeId($name) != null)
            return false;
        $objectID = $this->insertInto($this->config->getMetaObjectTypesTable(), array(
            'name'   =>  $name
        ))->execute();

        $attr_ids = array();
        foreach ($attributes as $attrName) {
            $attr_id = $this->insertInto($this->config->getMetaAttributesTable(), array(
                'name'              =>  $attrName,
                'object_type_id'    =>  $objectID
            ));
            $attr_ids[$attrName] = $attr_id->execute();
        }
        return array(
            'object_type_id'    =>  $objectID,
            'attr_ids'          =>  $attr_ids
        );
    }

    protected function getObjectTypeId($name) {
        $objectTypes = $this->cache->load(self::META_OBJECT_TYPES_CACHE_TAG);
        if (!$objectTypes) {
            $query = $this->from($this->config->getMetaObjectTypesTable())
                          ->fetchAll();
            $objectTypes = array();
            foreach ($query as $type)
                $objectTypes[$type['name']] = $type['object_type_id'];
            $this->cache->save(self::META_OBJECT_TYPES_CACHE_TAG, $objectTypes);
        }
        return isset($objectTypes[$name]) ? $objectTypes[$name] : null;
    }

    protected function getAttributeIds($objectTypeId) {
        $attributes = $this->cache->load(self::META_ATTRIBUTES_CACHE_TAG);
        if (!$attributes) {
            $query = $this->from($this->config->getMetaAttributesTable())
                          ->fetchAll();
            $attributes = array();
            foreach ($query as $attr)
                $attributes[$objectTypeId][$attr['name']] = $attr['attr_id'];
            $this->cache->save(self::META_ATTRIBUTES_CACHE_TAG, $attributes);
        }
        return isset($attributes[$objectTypeId]) ? $attributes[$objectTypeId] : null;
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
            case "string":
                $valueField = 'text_value';
                break;
            default:
                $valueField = null;
                break;
        }
        return $valueField;
    }
} 