<?php
/**
 * Routes a request using mod_rewrite data and regular expressions specified by
 * the LightVC user.
 *
 * Specify routes using {@link addRoute()}.
 *
 * @package lightvc
 * @author Anthony Bush
 * @since 2007-05-08
 * */
class Lvc_RegexRewriteRouter implements Lvc_RouterInterface {

    protected $routes = array();

    /**
     * Specify a regular expression and how it should be routed.
     *
     * For example:
     *
     *     $regexRouter->addRoute('|^wee/([^/]+)/?$|', array(
     *         'controller' => 'hello_world',
     *         'action' => 'index',
     *         'action_params' => array(1, 'constant_value')
     *     ));
     *
     * would map "wee/anything" and "wee/anything/" to:
     *
     *     HelloWorldController::actionIndex('anything', 'constant_value');
     *
     * but would not map "wee/anything/anything_else".
     *
     * The format of the $parsingInfo parameter is as follows:
     *
     *     'controller' => a hard coded controller name or an integer specifying which match in the regex to use.
     *     'action' => a hard coded action name or an integer specifying which match in the regex to use.
     *     'action_params' => array(
     *         a hard coded action value or an integer specifying which match in the regex to use,
     *         repeat above line as needed,
     *     ),
     *     'additional_params' => a hard coded integer specifying which match in the regex to use for additional parameters. These will be exploded by "/" and added to the action params.
     *
     * or
     *
     *     'redirect' => a replacement string that will be used to redirect to.  You can have parts of the original url mapped into the new one (like IDs).  See http://www.php.net/manual/en/function.preg-replace.php's documentation for the replacement parameter.
     *
     * You can specify as much or as little as you want in the $parsingInfo.
     * That is, if you don't specify the controller name or action name, then
     * the defaults will be used by the Lvc_FrontController.
     *
     * @param $regex regular expression to match the rewritten part with.
     * @param $parsingInfo an array containing any custom routing info.
     * @return void
     * @author Anthony Bush
     * @since 2007-05-08
     * */
    public function addRoute($regex, $parsingInfo = array()) {
        $this->routes[$regex] = $parsingInfo;
    }

    /**
     * Set all routes at once. Useful if you want to specify routes in a
     * config file and then pass them to this router all at once. See
     * {@link addRoute()} for routing specifications.
     *
     * @return void
     * @author Anthony Bush
     * @since 2007-05-08
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
     * @since 2007-05-09
     * */
    public function __construct(&$routes = null) {
        if (!is_null($routes)) {
            $this->setRoutes($routes);
        }
    }

    /**
     * Routes like {@link Lvc_RewriteRouter} does, with the additional check to
     * routes for specifying custom routes based on regular expressions.
     *
     * @param Lvc_HttpRequest $request A request object to route.
     * @return boolean
     * @author Anthony Bush
     * @since 2007-05-08
     * */
    public function route($request) {
        $params = $request->getParams();

        if (isset($params['get']['url'])) {

            // Use mod_rewrite's url
            $url = $params['get']['url'];

            $matches = array();
            foreach ($this->routes as $regex => $parsingInfo) {
                if (preg_match($regex, $url, $matches)) {

                    // Check for redirect action first
                    if (isset($parsingInfo['redirect'])) {
                        $redirectUrl = preg_replace($regex, $parsingInfo['redirect'], $url);
                        header('Location: ' . $redirectUrl);
                        exit();
                    }

                    // Get controller name if available
                    if (isset($parsingInfo['controller'])) {
                        if (is_int($parsingInfo['controller'])) {
                            // Get the controller name from the regex matches
                            $request->setControllerName(@$matches[$parsingInfo['controller']]);
                        } else {
                            // Use the constant value
                            $request->setControllerName($parsingInfo['controller']);
                        }
                    }

                    // Get action name if available
                    if (isset($parsingInfo['action'])) {
                        if (is_int($parsingInfo['action'])) {
                            // Get the action from the regex matches
                            $request->setActionName(@$matches[$parsingInfo['action']]);
                        } else {
                            // Use the constant value
                            $request->setActionName($parsingInfo['action']);
                        }
                    }

                    // Get action parameters
                    $actionParams = array();
                    if (isset($parsingInfo['action_params'])) {
                        foreach ($parsingInfo['action_params'] as $key => $value) {
                            if (is_int($value)) {
                                // Get the value from the regex matches
                                if (isset($matches[$value])) {
                                    $actionParams[$key] = $matches[$value];
                                } else {
                                    $actionParams[$key] = null;
                                }
                            } else {
                                // Use the constant value
                                $actionParams[$key] = $value;
                            }
                        }
                    }
                    if (isset($parsingInfo['additional_params'])) {
                        if (is_int($parsingInfo['additional_params'])) {
                            // Get the value from the regex matches
                            if (isset($matches[$parsingInfo['additional_params']])) {
                                $actionParams = $actionParams + explode('/', $matches[$parsingInfo['additional_params']]);
                            }
                        }
                    }


                    $request->setActionParams($actionParams);
                    return true;
                } // route matched
            } // loop through routes
        } // url _GET value set
        return false;
    }

}
