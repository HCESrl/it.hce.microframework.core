<?php

namespace it\hce\microframework\core;


use it\hce\microframework\core\exceptions\MicroFrameworkException;
use it\hce\microframework\core\factories\ModelsFactory;
use it\hce\microframework\core\factories\TemplateFactory;
use it\hce\microframework\core\helpers\PathHelper;
use JsonSchema\Validator;

class Controller
{
    /* STATIC */
    const htaccessGetVar = 'file';
    const defaultController = 'homepage/homepage';
    const serverErrorController = 'errors/500';
    const controllersExt = '.json';
    const templatesFolder = 'templates.';

    /* DYNAMIC */
    private $models = array();
    private $template;
    private $config = '';
    private $isStaticOutput = false;

    /**
     * Controller constructor.
     * @param $controllerPath
     * @param $message string a global message
     * @throws MicroFrameworkException
     */
    public function __construct($controllerPath, $message = '', $isStaticOutput = false)
    {
        // gets the real controller path on the filesystem
        $this->controllerPath = $controllerPath;
        $this->message = $message;
        $this->isStaticOutput = $isStaticOutput;

        try {
            $this->init();
        } catch (MicroFrameworkException $e) {
            throw $e;
        }
    }

    private function init()
    {
        // read the config file
        $this->readConfiguration();

        // check configuration form
        $this->checkConfiguration();

        // load the requested resources
        $this->loadResources();
    }

    public function getOutputExtension() {
        return ($this->isXml()?'xml':'html');
    }

    private function readConfiguration()
    {
        if (file_exists($this->controllerPath)) {
            $this->config = json_decode(file_get_contents($this->controllerPath));
        } else {
            throw new MicroFrameworkException('The requested controller was not found in: ' . $this->controllerPath);
        }
    }

    private function checkConfiguration()
    {
        $validator = new Validator();
        $validator->check($this->config, file_get_contents(dirname(__FILE__) . '/resources/controllerSchema.json'));

        if (!$validator->isValid()) {
            throw new MicroFrameworkException('The JSON file is not valid');
        }
    }

    private function loadResources()
    {
        try {
            $this->loadGlobalModel();
            $this->loadModels();
            $this->loadTemplate();
        } catch (MicroFrameworkException $e) {
            throw $e;
        }
    }

    private function loadGlobalModel()
    {
        $this->models = array_merge($this->models, ModelsFactory::loadGlobalModel());
        $this->models['GLOBAL']->config = $this->config;
        $this->models['GLOBAL']->isRtl = $this->isRtl();
        $this->models['GLOBAL']->message = $this->message;
    }

    /**
     * Check if we are in a RTL environment
     * @return bool
     */
    public function isRtl()
    {
        return isset($this->config->direction) && $this->config->direction === 'rtl';
    }

    private function loadModels()
    {
        try {
            $this->models = array_merge($this->models, ModelsFactory::loadModels($this->config->components));
        } catch (MicroFrameworkException $e) {
            throw $e;
        }
    }

    private function loadTemplate()
    {
        $this->template = TemplateFactory::loadTemplate(self::templatesFolder . $this->config->templateName, $this->models, $this->isStaticOutput);
    }

    /**
     * Gets the requested controller, based on URL
     * @return string controller's path
     */
    public static function getControllerFromUrl()
    {
        if (isset($_GET[self::htaccessGetVar])) {
            return PathHelper::getPublicPath($_GET[self::htaccessGetVar] . self::controllersExt);
        } else {
            return PathHelper::getPublicPath(self::defaultController . self::controllersExt);
        }
    }

    public static function get500Controller()
    {
        return PathHelper::getPublicPath(self::serverErrorController . self::controllersExt);
    }

    /**
     * Check if we are in a Ajax environment
     * @return bool
     */
    public function isAjax()
    {
        return isset($this->config->ajax) && $this->config->ajax;
    }

    public function isXml()
    {
        return isset($this->config->xml) && $this->config->xml;
    }

    public function __toString()
    {
        return $this->template;
    }
}
