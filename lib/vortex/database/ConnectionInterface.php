<?php
/**
 * Project: vortex.com
 * Author: superuser
 * Date: 07-May-15
 * Time: 18:21
 */

namespace vortex\database;


interface ConnectionInterface {
    public function select($query, $bindings = array());
    public function first($query, $bindings = array());
    public function insert($query, $bindings = array());
    public function update($query, $bindings = array());
    public function delete($query, $bindings = array());
    public function exec($query);

    public function beginTransaction();
    public function commit();
    public function rollBack();
} 