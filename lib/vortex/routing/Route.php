<?php
/**
 * Project: rework-vortex
 * Author: superuser
 * Date: 09-Apr-16
 * Time: 19:08
 */

namespace vortex\routing;


class Route {
    private $controller;
    private $action;
    private $params = array();

    function __construct($action, $controller, $params) {
        $this->action = $action;
        $this->controller = $controller;
        $this->params = $params;
    }

    /**
     * @return mixed
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * @return mixed
     */
    public function getController() {
        return $this->controller;
    }

    /**
     * @return array
     */
    public function getParams() {
        return $this->params;
    }



} 