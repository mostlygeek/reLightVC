<?php
/**
 * An HTTP request contains parameters from the GET, POST, PUT, and
 * DELETE arena.
 *
 * @package lightvc
 * @author Anthony Bush
 * @since 2007-04-20
 * */
class Lvc_HttpRequest extends Lvc_Request {

    protected $params = array();

    public function __construct() {

        $params = array();

        // Save GET data
        if (isset($_GET)) {
            $params['get'] = & $_GET;
        } else {
            $params['get'] = array();
        }

        // Ensure that we have some mode_rewritten url.
        if (!isset($params['get']['url'])) {
            $params['get']['url'] = '';
        }

        // Save POST data
        $params['post'] = & $_POST;

        // Save FILE data (consilidate it with _POST data)
        foreach ($_FILES as $name => $data) {
            if ($name != 'data') {
                $params['post'][$name] = $data;
            } else {
                // Convert _FILE[data][key][model][field] -> [data][model][field][key]
                // so that it matches up with _POST "data"
                foreach ($data as $key => $modelData) {
                    foreach ($modelData as $model => $fields) {
                        foreach ($fields as $field => $value) {
                            $params['post']['data'][$model][$field][$key] = $value;
                        }
                    }
                }
            }
        }

        // Set params that will be used by routers.
        $this->setParams($params);
        // An HTTP request will default to passing all the parameters to the controller.
        $this->setControllerParams($params);
    }

    public function &getParams() {
        return $this->params;
    }

    public function setParams(&$params) {
        $this->params = $params;
    }

    /**
     * Provides additional error information that might be useful when debugging
     * errors.
     *
     * @return string
     * @since 2008-03-14
     * */
    public function getAdditionalErrorInfo() {
        if (isset($_SERVER['REQUEST_URI'])) {
            return 'Request URL was ' . $_SERVER['REQUEST_URI'];
        } else {
            return parent::getAdditionalErrorInfo();
        }
    }

}