<?php
/**
 * Project: rework-vortex
 * Author: superuser
 * Date: 09-Apr-16
 * Time: 18:21
 */

namespace vortex\routing;


class Rule {
    const HTTP_GET = "get";
    const HTTP_POST = "post";
    const HTTP_PUT = "put";
    const HTTP_DELETE = "delete";
    const HTTP_CONNECT = "connect";
    const HTTP_OPTIONS = "options";
    const HTTP_TRACE = "trace";

    static $_HTTP_METHODS = array();

    private $method;
    private $pattern;
    private $controller;
    private $action;

    public function __construct($method, $pattern, $controller, $action) {
        $method = strtolower($method);

        if (!in_array($method, array_values(self::$_HTTP_METHODS)))
            throw new \InvalidArgumentException("Invalid HTTP Method name = " . $method . ". Expected = " . implode(', ', self::$_HTTP_METHODS));
        else if (empty($pattern))
            throw new \InvalidArgumentException("Pattern param is empty");
        else if (empty($controller))
            throw new \InvalidArgumentException("Controller param is empty");
        else if (empty($action))
            throw new \InvalidArgumentException("Action param is empty");

        $this->method = $method;
        $this->pattern = $pattern;
        $this->controller = $controller;
        $this->action = $action;
    }

    /**
     * @return String
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * @return String
     */
    public function getController() {
        return $this->controller;
    }

    /**
     * @return String
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * @return String
     */
    public function getPattern() {
        return $this->pattern;
    }
}

$ref = new \ReflectionClass('vortex\routing\Rule');
Rule::$_HTTP_METHODS = $ref->getConstants();