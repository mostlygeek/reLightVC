<?php
/**
 * The base class that all other PageControllers should extend. Depending on the setup,
 * you might want an AppController to extend this one, and then have all your controllers
 * extend your AppController.
 *
 * @package lightvc
 * @author Anthony Bush
 * @todo Finish up documentation in here...
 * @since 2007-04-20
 * */
class Lvc_PageController {

    /**
     * Params is typically a combination of:
     *     _GET (stored in params['get'])
     *     _POST (stored in params['post'])
     *     _FILE (also stored in params['post'])
     *
     * @var array
     * */
    protected $params = array();
    /**
     * A reference to $params['post']['data'], typically a combination of:
     *     _POST['data'] (usually holds [Model][field])
     *     _FILE['data'] (usually holds [key][Model][field], but the request object should remap it to [Model][field][key])
     *
     * @var array
     * */
    protected $postData = array();
    /**
     * Reference to post data (i.e. $this->params['post'])
     *
     * @var array
     * */
    protected $post = array();
    /**
     * Reference to get data (i.e. $this->params['get'])
     *
     * @var array
     * */
    protected $get = array();
    /**
     * Controller Name (e.g. controller_name, not ControllerNameController)
     *
     * @var string
     * */
    protected $controllerName = null;
    /**
     * Action Name (e.g. action_name, not actionActionName)
     *
     * @var string
     * */
    protected $actionName = null;
    /**
     * Variables we will pass to the view.
     *
     * @var array()
     * */
    protected $viewVars = array();
    /**
     * Have we loaded the view yet?
     *
     * @var boolean
     * */
    protected $hasLoadedView = false;
    /**
     * Specifies whether or not to load the default view for the action. If the
     * action should not render any view, set it to false in the sub controller.
     *
     * @var boolean
     * */
    protected $loadDefaultView = true;
    /**
     * Don't set this yourself. It's used internally by parent controller /
     * actions to determine whether or not to use the layout value in
     * $layoutOverride rather than in $layout when requesting a sub action.
     *
     * @var string
     * @see setLayoutOverride(), $layoutOverride
     * */
    protected $useLayoutOverride = false;
    /**
     * Don't set this yourself. It's used internally by parent controller /
     * actions to determine which layout to use when requesting a sub action.
     *
     * @var string
     * @see setLayoutOverride(), $useLayoutOverride
     * */
    protected $layoutOverride = null;
    /**
     * Set this in your controller to use a layout.
     *
     * @var string
     * */
    protected $layout = null;
    /**
     * An array of view variables specifically for the layout file.
     *
     * @var array
     * */
    protected $layoutVars = array();

    /**
     * Set the parameters of the controller.
     * Actions will get their parameters through params['get'].
     * Actions can access the post data as needed.
     *
     * @param array $params an array of [paramName] => [paramValue] pairs
     * @return void
     * @author Anthony Bush
     * */
    public function setControllerParams(&$params) {
        $this->params = $params;
        // Make a reference to the form data so we can get to it easier.
        if (isset($this->params['post']['data'])) {
            $this->postData = & $this->params['post']['data'];
        }
        if (isset($this->params['post'])) {
            $this->post = & $this->params['post'];
        }
        if (isset($this->params['get'])) {
            $this->get = & $this->params['get'];
        }
    }

    /**
     * Don't call this yourself. It's used internally when creating new
     * controllers so the controllers are aware of their name without
     * needing any help from a user setting a member variable or from some
     * reflector class.
     *
     * @return void
     * @author Anthony Bush
     * */
    public function setControllerName($controllerName) {
        $this->controllerName = $controllerName;
    }

    /**
     * Set a variable for the view to use.
     *
     * @param string $varName variable name to make available in the view
     * @param $value value of the variable.
     * @return void
     * @author Anthony Bush
     * */
    public function setVar($varName, $value) {
        $this->viewVars[$varName] = $value;
    }

    /**
     * Set variables for the view in masse.
     *
     * @param $varArray an array of [varName] => [value] pairs.
     * @return void
     * @author Anthony Bush
     * */
    public function setVars(&$varArray) {
        $this->viewVars = $varArray + $this->viewVars;
    }

    /**
     * Get the current value for a view variable.
     *
     * @param string $varName
     * @return mixed
     * @author Anthony Bush
     * @since 2007-11-13
     * */
    public function getVar($varName) {
        if (isset($this->viewVars[$varName])) {
            return $this->viewVars[$varName];
        } else {
            return null;
        }
    }

    /**
     * Set a variable for the layout view.
     *
     * @param $varName variable name to make available in the view
     * @param $value value of the variable.
     * @return void
     * @author Anthony Bush
     * @since 2007-05-17
     * */
    public function setLayoutVar($varName, $value) {
        $this->layoutVars[$varName] = $value;
    }

    /**
     * Get the current value for a layout variable.
     *
     * @param string $varName
     * @return mixed
     * @author Anthony Bush
     * @since 2007-11-13
     * */
    public function getLayoutVar($varName) {
        if (isset($this->layoutVars[$varName])) {
            return $this->layoutVars[$varName];
        } else {
            return null;
        }
    }

    /**
     * Set the layout to use for the view.
     *
     * @return void
     * @author Anthony Bush
     * */
    public function setLayout($layout) {
        $this->layout = $layout;
    }

    /**
     * Don't call this yourself. It's used internally when requesting sub
     * actions in order to avoid loading the layout multiple times.
     *
     * @return void
     * @see $useLayoutOverride, $layoutOverride
     * @author Anthony Bush
     * */
    public function setLayoutOverride($layout) {
        $this->useLayoutOverride = true;
        $this->layoutOverride = $layout;
    }

    /**
     * Returns the action name of this controller
     *
     * @return string
     * @author lzhang
     * */
    public function getActionName() {
        return $this->actionName;
    }

    /**
     * Determine whether or not the the controller has the specified action.
     *
     * @param string $actionName the action name to check for.
     * @return boolean
     * @author Anthony Bush
     * */
    public function hasAction($actionName) {
        if (method_exists($this, Lvc_Config::getActionFunctionName($actionName))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Runs the requested action and returns the output from it.
     *
     * Typically called by the FrontController.
     *
     * @param string $actionName the action name to run.
     * @param array $actionParams the parameters to pass to the action.
     * @return string output from running the action.
     * @author Anthony Bush
     * */
    public function getActionOutput($actionName, &$actionParams = array()) {
        ob_start();
        $this->runAction($actionName, $actionParams);
        return ob_get_clean();
    }

    /**
     * Runs the requested action and outputs its results.
     *
     * Typically called by the FrontController.
     *
     * @param string $actionName the action name to run.
     * @param array $actionParams the parameters to pass to the action.
     * @return void
     * @throws Lvc_Exception
     * @author Anthony Bush
     * */
    public function runAction($actionName, &$actionParams = array()) {
        $this->actionName = $actionName;
        $func = Lvc_Config::getActionFunctionName($actionName);
        if (method_exists($this, $func)) {
            $this->beforeAction();

            // Call the action
            if (Lvc_Config::getSendActionParamsAsArray()) {
                $this->$func($actionParams);
            } else {
                call_user_func_array(array($this, $func), $actionParams);
            }

            // Load the view
            if (!$this->hasLoadedView && $this->loadDefaultView) {
                $this->loadView($this->controllerName . '/' . $actionName);
            }

            $this->afterAction();
            return true;
        } else {
            throw new Lvc_Exception('No action `' . $actionName . '`. Write the `' . $func . '` method');
        }
    }

    /**
     * Load the requested controller view.
     *
     * For example, you can load another view in your controller with:
     *
     *     $this->loadView($this->getControllerName() . '/some_other_action');
     *
     * Or some other controller with:
     *
     *     $this->loadView('some_other_controller/some_other_action');
     *
     * Remember, the view for your action will be rendered automatically.
     *
     * @param string $controllerViewName 'controller_name/action_name' format.
     * @return void
     * @throws Lvc_Exception
     * @author Anthony Bush
     * */
    protected function loadView($controllerViewName) {

        $view = Lvc_Config::getControllerView($controllerViewName, $this->viewVars);
        if (is_null($view)) {
            throw new Lvc_Exception('Unable to load controller view "' . $controllerViewName . '" for controller "' . $this->controllerName . '"');
        } else {
            $view->setController($this);
            $viewContents = $view->getOutput();
        }

        if ($this->useLayoutOverride) {
            $this->layout = $this->layoutOverride;
        }
        if (!empty($this->layout)) {
            // Use an explicit name for this data so we don't override some other variable...
            $this->layoutVars[Lvc_Config::getLayoutContentVarName()] = $viewContents;
            $layoutView = Lvc_Config::getLayoutView($this->layout, $this->layoutVars);
            if (is_null($layoutView)) {
                throw new Lvc_Exception('Unable to load layout view "' . $this->layout . '" for controller "' . $this->controllerName . '"');
            } else {
                $layoutView->setController($this);
                $layoutView->output();
            }
        } else {
            echo($viewContents);
        }
        $this->hasLoadedView = true;
    }

    /**
     * Redirect to the specified url. NOTE that this function does not stop
     * execution.
     *
     * @param string $url URL to redirect to.
     * @return void
     * @author Anthony Bush
     * */
    protected function redirect($url) {
        header('Location: ' . $url);
    }

    /**
     * Execute code before every action.
     * Override this in sub classes
     *
     * @return void
     * @author Anthony Bush
     * */
    protected function beforeAction() {

    }

    /**
     * Execute code after every action.
     * Override this in sub classes
     *
     * @return void
     * @author Anthony Bush
     * */
    protected function afterAction() {

    }

    /**
     * Use this inside a controller action to get the output from another
     * controller's action. By default, the layout functionality will be
     * disabled for this "sub" action.
     *
     * Example Usage:
     *
     *     $enrollmentVerifyBox = $this->requestAction('enrollment_verify', array(), 'eligibility');
     *
     * @param string $actionName name of action to invoke.
     * @param array $actionParams parameters to invoke the action with.
     * @param string $controllerName optional controller name. Current controller will be used if not specified.
     * @param array $controllerParams optional controller params. Current controller params will be passed on if not specified.
     * @param string $layout optional layout to force for the sub action.
     * @return string output from requested controller's action.
     * @throws Lvc_Exception
     * @author Anthony Bush
     * */
    protected function requestAction($actionName, $actionParams = array(), $controllerName = null, $controllerParams = null, $layout = null) {
        if (empty($controllerName)) {
            $controllerName = $this->controllerName;
        }
        if (is_null($controllerParams)) {
            $controllerParams = $this->params;
        }
        $controller = Lvc_Config::getController($controllerName);
        if (is_null($controller)) {
            throw new Lvc_Exception('Unable to load controller "' . $controllerName . '"');
        }
        $controller->setControllerParams($controllerParams);
        $controller->setLayoutOverride($layout);
        return $controller->getActionOutput($actionName, $actionParams);
    }

    /**
     * Get the controller name. Mostly used internally...
     *
     * @return string controller name
     * @author Anthony Bush
     * */
    public function getControllerName() {
        return $this->controllerName;
    }

    /**
     * Get the controller params. Mostly used internally...
     *
     * @return array controller params
     * @author Anthony Bush
     * */
    public function getControllerParams() {
        return $this->params;
    }

}