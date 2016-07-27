<?php

namespace it\hce\microframework\core;


use it\hce\microframework\core\factories\ResourcesFactory;
use it\hce\microframework\core\helpers\HeaderHelper;

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
        $this->controller = new Controller(Controller::getControllerFromUrl());

        if ($this->controller->isAjax()) {
            HeaderHelper::setJsonHeader();
        }

        if (!$this->controller->isAjax()) {
            ResourcesFactory::writeResources($this->controller->isRtl());
        }

        $this->printTemplate();
    }

    private function printTemplate()
    {
        HeaderHelper::printHeader();
        echo $this->controller;
    }
}
