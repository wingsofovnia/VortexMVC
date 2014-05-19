<?php
/**
 * Project: OwnMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 * Time: 18:05
 */

class Vortex_Registry {
    private $vars = array();

    public function __set($index, $value) {
        $this->vars[$index] = $value;
    }

    public function __get($index) {
        return $this->vars[$index];
    }

    public function getVars() {
        return $this->vars;
    }
} 