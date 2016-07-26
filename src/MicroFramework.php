<?php

namespace it\hce\microframework\core;


use it\hce\microframework\core\factories\ResourcesFactory;

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
        ResourcesFactory::writeResources();
        $this->controller = new Controller(Controller::getControllerFromUrl());

        $this->printTemplate();
    }

    private function printTemplate()
    {
        echo $this->controller;
    }
}
