<?php
/**
 * Project: OwnMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 * Time: 23:43
 */

abstract class Vortex_Model {
    protected $db;

    public final function __constructor() {
        $this->init();
    }

    protected function init() {}

    protected  function connect() {
        $this->db = Vortex_Connection::getInstance();
    }
} 