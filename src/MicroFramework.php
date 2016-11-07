<?php

namespace it\hce\microframework\core;


use it\hce\microframework\core\exceptions\MicroFrameworkException;
use it\hce\microframework\core\factories\ResourcesFactory;
use it\hce\microframework\core\helpers\HeaderHelper;
use it\hce\microframework\core\helpers\PathHelper;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

/**
 * Class MicroFramework
 * @package it\hce\microframework\core
 * @author Marco 'Gatto' Boffo <mboffo@hce.it>
 */
class MicroFramework
{
    private $controller;

    /**
     * MicroFramework constructor.
     * Loads the framework's components and initializes the requested file
     */
    public function __construct()
    {
        try {
            $this->init();
        } catch (MicroFrameworkException $e) {
            HeaderHelper::set500Header();

            try {
                $this->controller = new Controller(Controller::get500Controller(), $e->__toString());
            } catch (MicroFrameworkException $e) {
                die('Unexpected error');
            }
        }

        $this->printTemplate();
    }

    private function init()
    {
        $this->controller = new Controller(Controller::getControllerFromUrl());

        if ($this->controller->isAjax()) {
            HeaderHelper::setJsonHeader();
        }

        if (!$this->controller->isAjax()) {
            ResourcesFactory::writeResources($this->controller->isRtl());
        }
    }

    private function printTemplate()
    {
        HeaderHelper::printHeader();
        echo $this->controller;
    }

    private static function createDirIfNotExists($path){
        if (!file_exists($path)) {
          return  mkdir($path );
        }
        return true;
    }

    /**
     * Prints the whole project
     * @param string $folder
     */
    public static function printProject($folder = 'static', $exclude = ['css', 'js'])
    {
        // define the destination folder
        $destinationFolder = PathHelper::getBasePath() . $folder;

        // write the whole resource pack
        ResourcesFactory::writeResources(true);
        ResourcesFactory::writeResources(false);

        // creates the static folders and files
        self::createDirIfNotExists($destinationFolder . '/css/');
        self::createDirIfNotExists($destinationFolder . '/js/');
        copy(PathHelper::getPublicPath('css/main.css'), $destinationFolder . '/css/main.css');
        copy(PathHelper::getPublicPath('css/main.css'), $destinationFolder . '/css/main.rtl.css');
        copy(PathHelper::getPublicPath('js/main.js'), $destinationFolder . '/js/main.js');

        $directory = new RecursiveDirectoryIterator(PathHelper::getPublicPath());
        $iterator = new RecursiveIteratorIterator($directory);
        $result = new RegexIterator($iterator, '/^.+\.json$/i', RecursiveRegexIterator::GET_MATCH);

        foreach ($result as $key => $value) {
            // write each controller in a sub directory
            $controller = new Controller($key);
            self::createDirIfNotExists(dirname($destinationFolder . '/' . array_slice(explode(PathHelper::getPublicPath(), $value[0]), -1)[0]));
            file_put_contents($destinationFolder . '/' . str_replace('.json', '.html', array_slice(explode(PathHelper::getPublicPath(), $value[0]), -1)[0]), $controller);
        }
    }
}
