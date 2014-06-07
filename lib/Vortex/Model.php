<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 */

/**
 * Class Vortex_Model
 * A general class of Models
 */
abstract class Vortex_Model {
    protected $db;

    /**
     * Connects to database
     */
    protected function connect() {
        $this->db = Vortex_Connection::getConnection();
    }
} 