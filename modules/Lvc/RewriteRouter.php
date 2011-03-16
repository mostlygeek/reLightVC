<?php

/**
 * Attempts to route a request using the GET value for the 'url' key, which
 * should be set by the mod_rewrite rules. Any additional "directories" are
 * used as parameters for the action (using numeric indexes). Any extra GET
 * data is also amended to the action parameters.
 *
 * If you need the numeric indexes to map to specific parameter names, use
 * the {@link Lvc_ParamOrderRewriteRouter} instead.
 *
 * @package lightvc
 * @author Anthony Bush
 * @since 2007-04-22
 * */
class Lvc_RewriteRouter implements Lvc_Router_Interface {

    /**
     * Attempts to route a request using the GET value for the 'url' key, which
     * should be set by the mod_rewrite rules. Any additional "directories" are
     * used as parameters for the action (using numeric indexes). Any extra GET
     * data is also amended to the action parameters.
     *
     * @param Lvc_HttpRequest $request A request object to route.
     * @return boolean
     * @author Anthony Bush
     * @since 2007-04-22
     * */
    public function route($request) {
        $params = $request->getParams();

        if (isset($params['get']['url'])) {

            // Use mod_rewrite's url
            $url = explode('/', $params['get']['url']);
            $count = count($url);

            // Set controller, action, and some action params from the segmented URL.
            if ($count > 0) {
                $request->setControllerName($url[0]);

                $actionParams = array();
                if ($count > 1) {
                    $request->setActionName($url[1]);
                    if ($count > 2) {
                        for ($i = 2; $i < $count; $i++) {
                            if (!empty($url[$i])) {
                                $actionParams[] = $url[$i];
                            }
                        }
                    }
                }

                $request->setActionParams($actionParams);
                return true;
            }
        }
        return false;
    }

}