<?php

namespace it\hce\microframework\core;


use it\hce\microframework\core\exceptions\MicroFrameworkException;
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
}
