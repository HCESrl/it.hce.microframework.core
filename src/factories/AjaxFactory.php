<?php

namespace it\hce\microframework\core\factories;


class AjaxFactory
{
    private $isAjax;

    /**
     * AjaxFactory constructor.
     * @param bool $isAjax
     */
    public function __construct($isAjax = false)
    {
        if ($this->isAjax = $isAjax) {
            $this->setHeader();
        }
    }

    private function setHeader()
    {
        header('Content-Type: application/json');
    }

    /**
     * Checks if the page is AJAX based
     * @return bool
     */
    public function isAjax()
    {
        return $this->isAjax;
    }
}
