<?php

namespace it\hce\microframework\core;


use it\hce\microframework\core\exceptions\MicroFrameworkException;
use it\hce\microframework\core\factories\ResourcesFactory;
use it\hce\microframework\core\helpers\HeaderHelper;
use it\hce\microframework\core\helpers\PathHelper;
use RecursiveDirectoryIterator;
use FilesystemIterator;
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

        if ($this->controller->isXml()) {
            HeaderHelper::setXmlHeader();
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

    public static function cleanupDirectory($dir) {
        $di = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
        $ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ( $ri as $file ) {
            $file->isDir() ?  rmdir($file) : unlink($file);
        }
        return true;
    }

    /**
     * Prints the whole project
     * @param string $folder
     */
    public static function printProject($folder = 'static/', $exclude = ['css', 'js'])
    {

        echo "\033[34m  starting printProject\033[0m\n";
        // define the destination folder
        $destinationFolder = PathHelper::getBasePath() . $folder;

        // clean the destination folder
        self::cleanupDirectory($destinationFolder);


        echo "\033[34m  finished writing resources \033[0m\n";

        // creates the static folders and files
        self::createDirIfNotExists($destinationFolder . '/css/');
        self::createDirIfNotExists($destinationFolder . '/js/');


        // write the whole resource pack
        ResourcesFactory::writeResources(true, $destinationFolder);
        ResourcesFactory::writeResources(false, $destinationFolder);


        $directory = new RecursiveDirectoryIterator(PathHelper::getPublicPath());
        $iterator = new RecursiveIteratorIterator($directory);
        $result = new RegexIterator($iterator, '/^.+\.json$/i', RecursiveRegexIterator::GET_MATCH);



        echo "\033[34m  starting iterator \033[0m\n";


        foreach ($result as $key => $value) {
            echo "\033[34m  processing $key  \033[0m\n";
            // write each controller in a sub directory
            $controller = new Controller($key);
            self::createDirIfNotExists(dirname($destinationFolder . '/' . array_slice(explode(PathHelper::getPublicPath(), $value[0]), -1)[0]));
            file_put_contents($destinationFolder . '/' . str_replace('.json', '.'.$controller->getOutputExtension(), array_slice(explode(PathHelper::getPublicPath(), $value[0]), -1)[0]), $controller);
        }
    }
}
