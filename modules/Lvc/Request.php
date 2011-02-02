<?php
/**
 * A request provides information about what controller and action to run and
 * what parameters to run them with.
 *
 * @package lightvc
 * @author Anthony Bush
 * @since 2007-04-20
 * */
class Lvc_Request {

    protected $controllerName = '';
    protected $controllerParams = array();
    protected $actionName = '';
    protected $actionParams = array();

    public function getControllerName() {
        return $this->controllerName;
    }

    public function &getControllerParams() {
        return $this->controllerParams;
    }

    public function getActionName() {
        return $this->actionName;
    }

    public function &getActionParams() {
        return $this->actionParams;
    }

    public function setControllerName($controllerName) {
        $this->controllerName = $controllerName;
    }

    public function setControllerParams(&$controllerParams) {
        $this->controllerParams = $controllerParams;
    }

    public function setActionName($actionName) {
        $this->actionName = $actionName;
    }

    public function setActionParams($actionParams) {
        $this->actionParams = $actionParams;
    }

    /**
     * Override this in sub request objects to have custom error messages appended to
     * LightVC messages.  For example, when HTTP Requests error, it might be useful
     * to put the requested URL in the error log with the "Unable to load controller"
     * message.
     *
     * @return string
     * @since 2008-03-14
     * */
    public function getAdditionalErrorInfo() {
        return '';
    }

}