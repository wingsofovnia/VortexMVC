<?php
/**
 * Project: OwnMVC
 * Author: Ilia Ovchinnikov
 * Date: 20-May-14
 * Time: 01:10
 */

class BeanModel implements Vortex_Service_ISerializable {
    private $private;
    protected $protected;
    protected $protectedInited = 20;
    public $public;

    public function __construct() {
        $this->protected = '_I_M_PROTECTED';
        $this->private = '__I_M_PRIVATE';
        $this->public = 'I_M_PUBLIC!';
    }
}