<?php

/**
 * Configuration class for the LVC suite of classes.
 *
 * @package lightvc
 * @author Anthony Bush
 * @since 2007-04-20
 *
 */
class Lvc_Config {

    protected static $controllerPaths = array();
    protected static $controllerSuffix = '.php'; // e.g. _controller.php
    protected static $controllerViewPaths = array();
    protected static $controllerViewSuffix = '.php'; // e.g. .tpl.php
    protected static $layoutViewPaths = array();
    protected static $layoutViewSuffix = '.php'; // e.g. .tpl.php
    protected static $elementViewPaths = array();
    protected static $elementViewSuffix = '.php'; // e.g. .tpl.php
    protected static $viewClassName = 'Lvc_View'; // e.g. AppView
    protected static $layoutContentVarName = 'layoutContent'; // e.g. content_for_layout
    /**
     * Sets whether or not to send action params as an array or as arguments
     * to the function.
     *
     * true => action($params)
     * false => action($param1, $param2, $param3, ...)
     *
     * @var boolean
     * */
    protected static $sendActionParamsAsArray = false;
    // These may be moved into some sort of routing thing later. For now:
    /**
     * The controller name to use if no controller name can be gathered from the request.
     *
     * @var string
     * */
    protected static $defaultControllerName = 'page';
    /**
     * The action name to call on the defaultControllerName if no controller name can be gathered from the request.
     *
     * @var string
     * */
    protected static $defaultControllerActionName = 'view';
    /**
     * The action params to use when calling defaultControllerActionName if no controller name can be gathered from the request.
     *
     * @var string
     * */
    protected static $defaultControllerActionParams = array('page_name' => 'home');
    /**
     * The default action name to call on a controller if the controller name
     * was gathered from the request, but the action name couldn't be.
     *
     * @var string
     * */
    protected static $defaultActionName = 'index';

    // Configuration Methods

    public static function addControllerPath($path) {
        self::$controllerPaths[] = $path;
    }

    public static function setControllerSuffix($suffix) {
        self::$controllerSuffix = $suffix;
    }

    public static function addControllerViewPath($path) {
        self::$controllerViewPaths[] = $path;
    }

    public static function setControllerViewSuffix($suffix) {
        self::$controllerViewSuffix = $suffix;
    }

    public static function addLayoutViewPath($path) {
        self::$layoutViewPaths[] = $path;
    }

    public static function setLayoutViewSuffix($suffix) {
        self::$layoutViewSuffix = $suffix;
    }

    public static function addElementViewPath($path) {
        self::$elementViewPaths[] = $path;
    }

    public static function setElementViewSuffix($suffix) {
        self::$elementViewSuffix = $suffix;
    }

    public static function setViewClassName($className) {
        self::$viewClassName = $className;
    }

    public static function setLayoutContentVarName($varName) {
        self::$layoutContentVarName = $varName;
    }

    public static function getLayoutContentVarName() {
        return self::$layoutContentVarName;
    }

    public static function setSendActionParamsAsArray($bool) {
        self::$sendActionParamsAsArray = $bool;
    }

    public static function getSendActionParamsAsArray() {
        return self::$sendActionParamsAsArray;
    }

    public static function setDefaultControllerName($defaultControllerName) {
        self::$defaultControllerName = $defaultControllerName;
    }

    public static function setDefaultControllerActionName($defaultControllerActionName) {
        self::$defaultControllerActionName = $defaultControllerActionName;
    }

    public static function setDefaultControllerActionParams($defaultControllerActionParams) {
        self::$defaultControllerActionParams = $defaultControllerActionParams;
    }

    public static function setDefaultActionName($defaultActionName) {
        self::$defaultActionName = $defaultActionName;
    }

    public static function getDefaultControllerName() {
        return self::$defaultControllerName;
    }

    public static function getDefaultControllerActionName() {
        return self::$defaultControllerActionName;
    }

    public static function getDefaultControllerActionParams() {
        return self::$defaultControllerActionParams;
    }

    public static function getDefaultActionName() {
        return self::$defaultActionName;
    }

    // Retrieval Methods

    public static function getController($controllerName) {
        foreach (self::$controllerPaths as $path) {
            $file = $path . $controllerName . self::$controllerSuffix;
            if (file_exists($file)) {
                include_once($file);
                $controllerClass = self::getControllerClassName($controllerName);
                $controller = new $controllerClass();
                $controller->setControllerName($controllerName);
                return $controller;
            }
        }
        return null;
    }

    public static function getControllerClassName($controllerName) {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $controllerName))) . 'Controller';
    }

    public static function getActionFunctionName($actionName) {
        return 'action' . str_replace(' ', '', ucwords(str_replace('_', ' ', $actionName)));
    }

    public static function getControllerView($viewName, &$data = array()) {
        return self::getView($viewName, $data, self::$controllerViewPaths, self::$controllerViewSuffix);
    }

    public static function getElementView($elementName, &$data = array()) {
        return self::getView($elementName, $data, self::$elementViewPaths, self::$elementViewSuffix);
    }

    public static function getLayoutView($layoutName, &$data = array()) {
        return self::getView($layoutName, $data, self::$layoutViewPaths, self::$layoutViewSuffix);
    }

    /**
     * As an Lvc developer, you'll probably want to use `getControllerView`,
     * `getElementView`, or `getLayoutView`.
     *
     * Example usage:
     *
     *     // Pass the whole file name and leave off the last parameters
     *     getView('/full/path/to/file/file.php', $data);
     *
     *     // Pass the view name and specify the paths to scan and the suffix to append.
     *     getView('file', $data, array('/full/path/to/file/'), '.php');
     *
     * @var mixed Lvc_View object if one is found, otherwise null.
     * @see getControllerView(), getElementView(), getLayoutView(), Lvc_Config::setViewClassName()
     * */
    public static function getView($viewName, &$data = array(), &$paths = array(''), $suffix = '') {
        foreach ($paths as $path) {
            $file = $path . $viewName . $suffix;
            if (file_exists($file)) {
                return new self::$viewClassName($file, $data);
            }
        }
        return null;
    }

    public static function dump() {
        echo '<pre>';

        echo '<strong>Controller Paths:</strong>' . "\n";
        print_r(self::$controllerPaths);
        echo '<strong>Controller Suffix:</strong> ' . self::$controllerSuffix . "\n\n";

        echo '<strong>Layout View Paths:</strong>' . "\n";
        print_r(self::$layoutViewPaths);
        echo '<strong>Layout View Suffix:</strong> ' . self::$layoutViewSuffix . "\n\n";

        echo '<strong>Controller View Paths:</strong>' . "\n";
        print_r(self::$controllerViewPaths);
        echo '<strong>Controller View Suffix:</strong> ' . self::$controllerViewSuffix . "\n\n";

        echo '<strong>Element View Paths:</strong>' . "\n";
        print_r(self::$elementViewPaths);
        echo '<strong>Element View Suffix:</strong> ' . self::$elementViewSuffix . "\n\n";

        echo '</pre>';
    }

}