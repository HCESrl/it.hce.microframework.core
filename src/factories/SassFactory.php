<?php

namespace it\hce\microframework\core\factories;


use it\hce\microframework\core\MicroFramework;
use it\hce\microframework\core\exceptions\ResourceWriteException;
use Leafo\ScssPhp\Compiler;
use MatthiasMullie\Minify\CSS;
use CSSJanus;

class SassFactory {
    private $compiler;
    private $main;
    private $minifier;

    public function __construct()
    {
        $this->compiler = new Compiler();
        $this->main = file_get_contents(MicroFramework::getResourcesPath() . 'css/main.scss');
        $this->minifier = new CSS();
    }

    public function collectSCSS()
    {
        $this->compiler->setImportPaths(MicroFramework::getResourcesPath() . 'css/');
    }

    public function write($file)
    {
        $compiledSass = $this->compiler->compile($this->main);

        if(is_writable(dirname($file))) {
            if(isset($_GET['rtl']) && $_GET['rtl'] === 'true') {
                include_once(MicroFramework::getBasePath() . 'vendor/cssjanus/cssjanus/src/CSSJanus.php');
                $this->minifier->add(CSSJanus::transform($compiledSass));
            } else {
                $this->minifier->add($compiledSass);
            }

            file_put_contents($file, $this->minifier->minify());
        } else {
            throw new ResourceWriteException($file . ' not writable');
        }
    }
}