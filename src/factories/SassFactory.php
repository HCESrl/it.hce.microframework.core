<?php

namespace it\hce\microframework\core\factories;


use CSSJanus;
use it\hce\microframework\core\exceptions\ResourceWriteException;
use it\hce\microframework\core\helpers\PathHelper;
use Leafo\ScssPhp\Compiler;
use MatthiasMullie\Minify\CSS;

class SassFactory
{
    const mainScssPath = 'css/main.scss';
    const scssPath = 'css/';

    private $compiler;
    private $main;
    private $minifier;
    private $compiledCss;

    public function __construct($rtl = false)
    {
        // load the compiler
        $this->compiler = new Compiler();

        //load the minifier
        $this->minifier = new CSS();

        // load main.scss
        $this->main = file_get_contents(PathHelper::getResourcesPath(self::mainScssPath));
        //TODO: throw exception if file does not exists

        // set rtl
        $this->rtl = $rtl;
    }

    /**
     * Collects the needed files
     */
    public function collect()
    {
        $this->compiler->setImportPaths(PathHelper::getResourcesPath(self::scssPath));
    }

    /**
     * Compiles SCSS
     * @return string the compiled css
     */
    public function compile()
    {
        $compiledSass = $this->compiler->compile($this->main);

        if ($this->rtl) {
            $compiledSass = $this->rightToLeft($compiledSass);
        }

        // minify the result
        return $this->compiledCss = $this->minify($compiledSass);
    }

    private function rightToLeft($compiledSass)
    {
        return CSSJanus::transform($compiledSass);
    }

    private function minify($compiledSass)
    {
        $this->minifier->add($compiledSass);
        return $this->minifier->minify();
    }

    /**
     * Writes the loaded SCSS in a given css file (overwrite)
     * @param $file
     * @throws ResourceWriteException
     */
    public function write($file)
    {
        if (is_writable(dirname($file))) {
            file_put_contents($file, $this->compiledCss);
        } else {
            throw new ResourceWriteException($file . ' not writable');
        }
    }
}
