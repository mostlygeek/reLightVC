<?php
/**
 * Routes a request using only GET data.
 *
 * You can change the default keys for controller and action detection using
 * {@link setControllerKey()} and {@link setActionKey()} respectively.
 *
 * @package lightvc
 * @author Anthony Bush
 * @since 2007-04-22
 * */
class Lvc_GetRouter implements Lvc_Router_Interface
{

    protected $controllerKey = 'controller';
    protected $actionKey = 'action';
    protected $actionParamsKey = null;
    protected $routes = array();

    public function setControllerKey($controllerKey) {
        $this->controllerKey = $controllerKey;
    }

    public function setActionKey($actionKey) {
        $this->actionKey = $actionKey;
    }

    public function setActionParamsKey($actionParamsKey) {
        $this->actionParamsKey = $actionParamsKey;
    }

    /**
     * Add a param order for a controller / action.
     *
     * For example:
     *
     *     $router->addRoute('pages', 'show_page', array('page_name'));
     *
     * will route:
     *
     *     ?controller=pages&action=show_page&page_name=about
     *
     * to:
     *
     *     PagesController::actionShowPage('about');
     *
     * whereas without the route the controller would be invoked with:
     *
     *     PagesController::actionShowPage();
     *
     * and you'd have to access the page_name via $this->get['page_name'].
     *
     * @return void
     * @author Anthony Bush
     * @since 2007-05-10
     * */
    public function addRoute($controllerName, $actionName, $actionParamsOrder = array()) {
        $this->routes[$controllerName][$actionName] = $actionParamsOrder;
    }

    /**
     * Set all routes at once. Useful if you want to specify routes in a
     * config file and then pass them to this router all at once. See
     * {@link addRoute()} for routing specifications.
     *
     * @return void
     * @author Anthony Bush
     * @since 2007-05-10
     * */
    public function setRoutes(&$routes) {
        $this->routes = $routes;
    }

    /**
     * Construct the router and set all routes at once. See {@link setRoutes()}
     * for more info.
     *
     * @return void
     * @author Anthony Bush
     * @see setRoutes()
     * @since 2007-05-10
     * */
    public function __construct(&$routes = null) {
        if (!is_null($routes)) {
            $this->setRoutes($routes);
        }
    }

    /**
     * Attempts to routes a request using only the GET data.
     *
     * @param Lvc_HttpRequest $request A request object to route.
     * @return boolean
     * @author Anthony Bush
     * @since 2007-04-22
     * */
    public function route($request) {
        $params = $request->getParams();

        // Use GET parameters to set controller, action, and action params
        if (isset($params['get'][$this->controllerKey])) {

            $request->setControllerName($params['get'][$this->controllerKey]);

            if (isset($params['get'][$this->actionKey])) {
                $request->setActionName($params['get'][$this->actionKey]);
            } else {
                $request->setActionName(Lvc_Config::getDefaultActionName());
            }

            // Using paramsKey method?
            if (!is_null($this->actionParamsKey) && isset($params['get'][$this->actionParamsKey])) {
                $request->setActionParams($params['get'][$this->actionParamsKey]);
            }
            // Using routes?
            else if (!empty($this->routes)) {
                if (isset($this->routes[$request->getControllerName()])
                        && isset($this->routes[$request->getControllerName()][$request->getActionName()])
                ) {
                    $actionParams = array();
                    foreach ($this->routes[$request->getControllerName()][$request->getActionName()] as $paramName) {
                        $actionParams[$paramName] = @$params['get'][$paramName];
                    }
                    $request->setActionParams($actionParams);
                }
            }

            return true;
        } else {
            return false;
        }
    }

}