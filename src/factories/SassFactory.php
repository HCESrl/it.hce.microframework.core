<?php

namespace it\hce\microframework\core\factories;


use CSSJanus;
use Sass;
use it\hce\microframework\core\exceptions\MicroFrameworkException;
use it\hce\microframework\core\helpers\PathHelper;

class SassFactory
{
    const mainScssPath = 'css/main.scss';
    const mainRtlScssPath = 'css/main.rtl.scss';
    const scssPath = 'css/';

    private $compiler;
    private $main;
    private $mainRtl;
    private $compiledCss;

    /**
     * SassFactory constructor.
     * @param bool $rtl
     * @throws MicroFrameworkException
     */
    public function __construct($rtl = false)
    {
        // load the compiler
        $this->compiler = new Sass();

        // set minification
        $this->compiler->setStyle(Sass::STYLE_COMPRESSED);

        // set rtl
        $this->rtl = $rtl;

        if ($this->rtl) {
            // load main.rtl.scss
            if (!file_exists(PathHelper::getResourcesPath(self::mainRtlScssPath))) {
                throw new MicroFrameworkException('main.rtl.scss not found');
            }

            $this->mainRtl = file_get_contents(PathHelper::getResourcesPath(self::mainRtlScssPath));
        }

        // load main.scss
        if (!file_exists(PathHelper::getResourcesPath(self::mainScssPath))) {
            throw new MicroFrameworkException('main.scss not found');
        }

        $this->main = file_get_contents(PathHelper::getResourcesPath(self::mainScssPath));
    }

    /**
     * Collects the needed files
     */
    public function collect()
    {
        $this->compiler->setIncludePath(PathHelper::getResourcesPath(self::scssPath));
    }

    /**
     * Compiles SCSS
     * @throws MicroFrameworkException
     * @return string the compiled css
     */
    public function compile()
    {
        try {
            $compiledSass = $this->compiler->compile($this->main);
            
            if ($this->rtl) {
                $compiledSass = $this->rightToLeft($compiledSass);
                $rtlSass = $this->compiler->compile($this->mainRtl);
                $compiledSass = $compiledSass . $rtlSass;
            }

            return $this->compiledCss = $compiledSass;
        } catch (Exception $e) {
            throw new MicroFrameworkException($e->getMessage());
        }
    }

    private function rightToLeft($compiledSass)
    {
        return CSSJanus::transform($compiledSass);
    }

    /**
     * Writes the loaded SCSS in a given css file (overwrite)
     * @param $file
     * @throws MicroFrameworkException
     */
    public function write($file)
    {
        if ((!file_exists($file) && is_writable(dirname($file))) || is_writable($file)) {
            file_put_contents($file, $this->compiledCss);
        } else {
            if((!file_exists($file) && !is_writable(dirname($file))) ){
                throw new MicroFrameworkException($file . ' does not exist and directory is not writable');
            } else if(!is_writable($file)){
            throw new MicroFrameworkException($file . ' is not writable');
            } else {
                throw new MicroFrameworkException("Unknown error trying to write $file ");
            }
        }
    }
}
