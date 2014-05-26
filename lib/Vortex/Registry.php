<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 */

/**
 * Class Vortex_Registry
 * This class is a simple Registry (key-value storage) implementation
 */
class Vortex_Registry {
    private $vars = array();

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
        $index = (string) $index;
        return isset($this->vars[$index]) ? $this->vars[$index] : null;
    }

    /**
     * Returns an array of all values of this Registry
     * @return array all values from registry
     */
    public function getVars() {
        return $this->vars;
    }
} 