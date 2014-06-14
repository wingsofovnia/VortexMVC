<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 13-Jun-14
 * Time: 21:53
 */

namespace Vortex\Database\DAO;
use Vortex\Database\Connection;
use Vortex\Exceptions\DAOException;

abstract class Entity {
    protected $_object_id;

    public function save() {
        $dao = Connection::getDAO();
        if (!$this->_object_id) {
            $this->_object_id = $dao->insert($this);
            return true;
        } else {
            $objectData = $dao->readObject($this);
            return $dao->update($this->_object_id, $objectData['attributes']);
        }
    }

    public function find($additions = array()) {
        $dao = Connection::getDAO();
        $objectData = $dao->readObject($this);

        $ref = new \ReflectionClass($this);
        $object_type_name = $ref->getName();
        $params = $additions == array() ? $objectData['attributes'] : array_merge($objectData['attributes'], $additions);

        $obj = $dao->findObjectIds($object_type_name, $params);
        if (!$obj)
            throw new DAOException('Can\'t find such object');

        $this->_object_id = $obj[0];
        return true;
    }

    public function delete($additions = array()) {
        $dao = Connection::getDAO();
        if (!$this->_object_id) {
            $res = $this->find($additions);
            if (!$res)
                throw new DAOException('Object_id is not specified and finding with additions failed. No such object!');
        }
        return $dao->delete($this->_object_id);
    }

    public final function getObjectId() {
        return $this->_object_id;
    }
} 