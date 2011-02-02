<?php
/**
 * FrontController takes a Request object and invokes the appropriate controller
 * and action.
 *
 * Example Usage:
 *
 *     $fc = new Lvc_FrontController();
 *     $fc->addRouter(new Lvc_GetRouter());
 *     $fc->processRequest(new Lvc_HttpRequest());
 *
 * @package lightvc
 * @author Anthony Bush
 * @since 2007-04-20
 * */
class Lvc_FrontController {

    protected $routers = array();

    /**
     * Add a router to give it a chance to route the request.
     *
     * The first router to return true to the {@link route()} call
     * will be the last router called, so add them in the order you want them
     * to run.
     *
     * @return void
     * @author Anthony Bush
     * */
    public function addRouter(Lvc_RouterInterface $router) {
        $this->routers[] = $router;
    }

    /**
     * Processes the request data by instantiating the appropriate controller and
     * running the appropriate action.
     *
     * @return void
     * @throws Lvc_Exception
     * @author Anthony Bush
     * */
    public function processRequest(Lvc_Request $request) {
        try {
            // Give routers a chance to (re)-route the request.
            foreach ($this->routers as $router) {
                if ($router->route($request)) {
                    break;
                }
            }

            // If controller name or action name are not set, set them to default.
            $controllerName = $request->getControllerName();
            if (empty($controllerName)) {
                $controllerName = Lvc_Config::getDefaultControllerName();
                $actionName = Lvc_Config::getDefaultControllerActionName();
                $actionParams = $request->getActionParams() + Lvc_Config::getDefaultControllerActionParams();
                $request->setActionParams($actionParams);
            } else {
                $actionName = $request->getActionName();
                if (empty($actionName)) {
                    $actionName = Lvc_Config::getDefaultActionName();
                }
            }

            $controller = Lvc_Config::getController($controllerName);
            if (is_null($controller)) {
                throw new Lvc_Exception('Unable to load controller "' . $controllerName . '"');
            }
            $controller->setControllerParams($request->getControllerParams());
            $controller->runAction($actionName, $request->getActionParams());
        } catch (Lvc_Exception $e) {
            // Catch exceptions and append additional error info if the request object has anything to say.
            $moreInfo = $request->getAdditionalErrorInfo();
            if (!empty($moreInfo)) {
                throw new Lvc_Exception($e->getMessage() . '. ' . $moreInfo);
            } else {
                throw $e;
            }
        }
    }

}