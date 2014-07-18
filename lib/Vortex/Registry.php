<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 */

namespace Vortex;

/**
 * Class Vortex_Registry is a simple Registry (key-value storage) implementation
 * @package Vortex
 */
class Registry {
    protected $vars;

    /**
     * Create registry on a basis of existing array
     * @param array $array an array (default - array())
     * @throws \InvalidArgumentException
     */
    public function __construct($array = array()) {
        if (!is_array($array))
            throw new \InvalidArgumentException('Only arrays allowed for registry!');
        $this->vars = $array;
    }

    /**
     * Value setter
     * @param string $index key
     * @param mixed $value value
     */
    public function __set($index, $value) {
        $this->vars[(string)$index] = $value;
    }

    /**
     * Value getter
     * @param string $index key of the value
     * @return mixed actually, value
     */
    public function __get($index) {
        $index = (string)$index;
        return isset($this->vars[$index]) ? $this->vars[$index] : null;
    }

    /**
     * Returns an array of all values of this Registry
     * @return array all values from registry
     */
    public function getVars() {
        return $this->vars;
    }

    /**
     * Merges it's vars with another Registry or array
     * @param Registry|array $data data to merge
     * @return bool true, if operation was successful
     */
    public function merge($data) {
        if (is_object($data) && is_a($data, 'Vortex\Registry')) {
            $this->vars = array_merge($this->vars, $data->getVars());
            return true;
        } else if (is_array($data)) {
            $this->vars = array_merge($this->vars, $data);
            return true;
        }
        return false;
    }
} 