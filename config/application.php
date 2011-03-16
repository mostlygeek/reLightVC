<?php

define('ROOT_DIR', realpath('../')); // the root of the application

// Include and configure the LighVC framework
//include_once(ROOT_DIR . 'modules/lightvc.php');

/**
 * Register an autoloader
 */
$autoloadConfig = array(
    'Lvc'   => ROOT_DIR . '/modules/',

    // specific files to autoload
    'AppController' => ROOT_DIR . '/classes/AppController.class.php',
    'AppView'       => ROOT_DIR . '/classes/AppView.class.php',
);

spl_autoload_register(function($className) use ($autoloadConfig) {
    $namespace = substr($className, 0, strpos($className, '_'));
    if (strlen($namespace)) {
        if (isset($autoloadConfig[$namespace])) {
            $pathToFile = $autoloadConfig[$namespace] . str_replace('_', '/', $className).'.php';
            require_once($pathToFile);
            return;
        }
    } elseif (isset($autoloadConfig[$className])) {
        require_once($autoloadConfig[$className]);
        return;
    }

    echo '<pre>';
    echo "Class: $className\n";
    print_r($autoloadConfig);

    echo '</pre>';
    die();
    
    trigger_error("Could not autoload: " . $className, E_USER_ERROR);
});

Lvc_Config::addControllerPath(ROOT_DIR . '/controllers/');
Lvc_Config::addControllerViewPath(ROOT_DIR . '/views/');
Lvc_Config::addLayoutViewPath(ROOT_DIR . '/views/layouts/');
Lvc_Config::addElementViewPath(ROOT_DIR . '/views/elements/');
Lvc_Config::setViewClassName('AppView');

// Load Routes
include_once('./routes.php');