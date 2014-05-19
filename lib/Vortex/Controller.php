<?php
/**
 * Project: OwnMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 * Time: 17:55
 */

abstract class Vortex_Controller {
    protected $request;
    protected $response;

    public function __construct($request, $response) {
        $this->request = $request;
        $this->response = $response;
        $this->init();
    }

    public function init() {}

    abstract public function indexAction();
} 