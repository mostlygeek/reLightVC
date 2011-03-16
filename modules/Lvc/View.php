<?php
/**
 * A View can be outputted or have its output returned (i.e. it's renderable).
 * It can not be executed.
 *
 * $inc = new Lvc_View('foo.php', array());
 * $inc->output();
 * $output = $inc->getOutput();
 *
 * @package lightvc
 * @author Anthony Bush
 * @since 2007-04-20
 * */
class Lvc_View {

    /**
     * Full path to file name to be included.
     *
     * @var string
     * */
    protected $fileName;
    /**
     * Data to be exposed to the view template file.
     *
     * @var array
     * */
    protected $data;
    /**
     * A reference to the parent controller
     *
     * @var Lvc_Controller
     * */
    protected $controller;

    /**
     * Construct a view to be rendered.
     *
     * @param string $fileName Full path to file name of the view template file.
     * @param array $data an array of [varName] => [value] pairs. Each varName will be made available to the view.
     * @return void
     * @author Anthony Bush
     * */
    public function __construct($fileName, &$data) {
        $this->fileName = $fileName;
        $this->data = $data;
    }

    /**
     * Output the view (aka render).
     *
     * @return void
     * @author Anthony Bush
     * */
    public function output() {
        extract($this->data, EXTR_SKIP);
        include($this->fileName);
    }

    /**
     * Return the output of the view.
     *
     * @return string output of view
     * @author Anthony Bush
     * */
    public function getOutput() {
        ob_start();
        $this->output();
        return ob_get_clean();
    }

    /**
     * Render a sub element from within a view.
     *
     * Views are not allowed to have business logic, but they can call upon
     * other generic, shared, views, called elements here.
     *
     * @param string $elementName name of element to render
     * @param array $data optional data to pass to the element.
     * @return void
     * @throws Lvc_Exception
     * @author Anthony Bush
     * */
    protected function renderElement($elementName, $data = array()) {
        $view = Lvc_Config::getElementView($elementName, $data);
        if (!is_null($view)) {
            $view->setController($this->controller);
            $view->output();
        } else {
            error_log('Unable to render element "' . $elementName . '"');
            // throw new Lvc_Exception('Unable to render element "' . $elementName . '"');
        }
    }

    /**
     * Set the controller when constructing a view if you want {@link setLayoutVar()}
     * to be callable from a view.
     *
     * @return void
     * @author Anthony Bush
     * @since 2007-05-17
     * */
    public function setController($controller) {
        $this->controller = $controller;
    }

    /**
     * Set a variable for the layout file.  You can set the page title from a static
     * page's view file this way.
     *
     * @param $varName variable name to make available in the view
     * @param $value value of the variable.
     * @return void
     * @author Anthony Bush
     * @since 2007-05-17
     * */
    public function setLayoutVar($varName, $value) {
        $this->controller->setLayoutVar($varName, $value);
    }

}