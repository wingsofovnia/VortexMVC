<?php
/**
 * Project: OwnMVC
 * Author: Ilia Ovchinnikov
 * Date: 20-May-14
 * Time: 01:10
 */

class BeanModel implements Vortex_Service_ISerializable {
    protected $protected;
    protected $protInited = 20;
    private $private;
    public $public;

    public function __construct() {
        $this->protected = 'PROT!';
        $this->private = 'PRIV!';
        $this->public = 'HERE I AM!';
    }

    public function toString() {
        return 'obj = ' . $this->private . '; ' . $this->protected . '; ' . $this->public;
    }
}