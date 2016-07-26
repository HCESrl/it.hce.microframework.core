<?php

namespace it\hce\microframework\core;


use it\hce\microframework\core\exceptions\BadDefinitionException;
use it\hce\microframework\core\exceptions\ControllerNotFoundException;
use it\hce\microframework\core\factories\ModelsFactory;
use it\hce\microframework\core\factories\TemplateFactory;
use it\hce\microframework\core\helpers\PathHelper;
use JsonSchema\Validator;

class Controller
{
    /* STATIC */

    const htaccessGetVar = 'file';
    const defaultController = 'homepage/homepage';
    const controllersExt = '.json';
    const templatesFolder = 'templates.';

    /* DYNAMIC */
    private $models = array();
    private $template;
    private $config = '';

    /**
     * Controller constructor.
     * @param $controllerPath
     */
    public function __construct($controllerPath)
    {
        // gets the real controller path on the filesystem
        $this->controllerPath = $controllerPath;

        // read the config file
        $this->readConfiguration();

        // check configuration form
        $this->checkConfiguration();

        // load the requested resources
        $this->loadResources();
    }

    private function readConfiguration()
    {
        if (file_exists($this->controllerPath)) {
            $this->config = json_decode(file_get_contents($this->controllerPath));
        } else {
            throw new ControllerNotFoundException('The requested controller was not found in: ' . $this->controllerPath);
        }
    }

    private function checkConfiguration()
    {
        $validator = new Validator();
        $validator->check($this->config, file_get_contents(dirname(__FILE__) . 'resources/controllerSchema.json'));

        if (!$validator->isValid()) {
            throw new BadDefinitionException('The JSON file is not valid');
        }
    }

    private function loadResources()
    {
        $this->loadGlobalModel();
        $this->loadModels();
        $this->loadTemplate();
    }

    private function loadGlobalModel()
    {
        $this->models = array_merge($this->models, ModelsFactory::loadGlobalModel());
    }

    private function loadModels()
    {
        $this->models = array_merge($this->models, ModelsFactory::loadModels($this->config->components));
    }

    private function loadTemplate()
    {
        $this->template = TemplateFactory::loadTemplate(self::templatesFolder . $this->config->templateName, $this->models);
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

    public function __toString()
    {
        return $this->template;
    }
}
