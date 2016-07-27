<?php

namespace it\hce\microframework\core;


use it\hce\microframework\core\exceptions\FileNotFoundException;

/**
 * Class MicroFramework
 * @package it\hce\microframework\core
 * @author Marco 'Gatto' Boffo <mboffo@hce.it>
 */
class MicroFramework {

    /**
     * MicroFramework constructor.
     * Loads the framework's components and initializes the requested file
     */
    public function __construct()
    {
        try {
            // Reads from the URL
            $file = isset($_GET['file']) ? $this->getPublicPath($_GET['file'] . '.php') : $this->getPublicPath('homepage/homepage.php');
            if(file_exists($file))
            {
                // Setup
                $framework = $this;

                // Starts the magic
                include_once($file);
            } else {
                header("HTTP/1.0 404 Not Found");
                throw new FileNotFoundException("File <strong>$file</strong> not found in application files.");
            }
        } catch (\Exception $e) {
            exit  ("<h2 style='color:red'>MICROFRAMEWORK ERROR</h2><p>Error type: ". get_class($e) . "</p><p>Error message: " . $e->getMessage() ."</p>");
        }

    }

    /**
     * @return string project's base path
     */
    public static function getBasePath()
    {
		return realpath(dirname(__FILE__)) . '/../../../../'; //TODO: refactor
	}

    /**
     * @param string $file inside the public path
     * @return string complete path
     */
    public static function getPublicPath($file = '')
    {
        return self::getBasePath() . 'public/' . $file;
    }

    /**
     * @param string $file inside the templates path
     * @return string complete path
     */
    public static function getTemplatesPath($file = '')
    {
        return self::getBasePath() . 'templates/' . $file;
    }

    /**
     * @param string $file inside the components path
     * @return string complete path
     */
    public static function getComponentsPath($file = '')
    {
        return self::getBasePath() . 'components/' . $file;
    }

    /**
     * @param string $file inside the config path
     * @return string complete path
     */
    public static function getConfigPath($file = '')
    {
        return self::getBasePath() . 'config/' . $file;
    }

    /**
     * @param string $file inside the resources path
     * @return string complete path
     */
    public static function getResourcesPath($file = '')
    {
        return self::getBasePath() . 'resources/' . $file;
    }

    /**
     * @return string complete cache path
     */
    public static function getCachePath()
    {
        return self::getBasePath() . 'cache/';
    }
}