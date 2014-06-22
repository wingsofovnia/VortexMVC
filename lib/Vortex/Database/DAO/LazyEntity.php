<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 14-Jun-14
 * Time: 20:28
 */

namespace Vortex\Database\DAO;

use Vortex\Database\Connection;

class LazyEntity {
    private $object_id;
    private $object;

    public function __construct($id) {
        $this->setObjectId($id);
    }

    /**
     * Gets object, that DAOLazyObject represents
     * @return object DAOEntity
     */
    public function getObject() {
        if (!$this->object)
            $this->loadObject();
        return $this->object;
    }

    /**
     * Gets id of object, that DAOLazyObject represents
     * @return int object id
     */
    public function getObjectId() {
        return $this->object_id;
    }

    /**
     * Sets id of object, that DAOLazyObject represents
     * @param int $object_id object id
     * @throws \InvalidArgumentException if param $object_id is empty
     */
    public function setObjectId($object_id) {
        if (!is_numeric($object_id))
            throw new \InvalidArgumentException('Param $object_id should be an int value!');
        $this->object_id = $object_id;
    }

    public function __get($prop) {
        if (!$this->object)
            $this->loadObject();
        return $this->object->$prop;
    }

    public function __set($prop, $val) {
        if (!$this->object)
            $this->loadObject();
        $this->object->$prop = $val;
    }

    public function __call($name, $args) {
        if (!$this->object)
            $this->loadObject();
        $this->object->$name($args);
    }

    private function loadObject() {
        if ($this->object)
            return;
        $dao = Connection::getDAO();
        $this->object = $dao->select($this->object_id);
    }

    public function __toString() {
        return 'LazyEntity = {id: ' . $this->object_id . '}';
    }
}