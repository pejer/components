<?php

namespace DHP\components\layout;

/**
 *
 * Class Layout
 *
 * Blocks
 * How can we handle this? Maybe make functions of them in theme:
 *      block_sha_name-of-block()
 * and also have some-sort-of-variable with info about this template.
 * This way we could be able to implement block-functions and also have
 * the super & extends work...?
 *
 *
 * @todo    : Blocks!
 * @package DHP_core\component\layout
 */
class Layout
{
    protected $config = [];
    protected $view   = null;
    protected $compiler;

    public function __construct(
        array $config
    ) {
        $this->compiler = new Compile();
        $this->config   = $config;
    }

    public function render($template, $data = [])
    {
        $compiledTemplate = $this->compile($template, false);
        $view             = new View($this);
        $return           = $view->render($compiledTemplate, $data);
        unset($view);
        return $return;
    }

    public function compile(string $path, bool $overwrite = false)
    {
        $compiledTemplate = $this->getCompiledPath($path);
        if ($overwrite || !\file_exists($compiledTemplate)) {
            // Todo: handle multiple paths
            $templatePath = $this->config['layouts'][0] . $path;
            $tokenizer    = new Tokenizer($templatePath);
            $time_start   = Util::microtimeFloat();
            $mem_before   = Util::getMemoryUsage();
            $data         = $this->compiler->compile($tokenizer->tokenize());
            $mem_used     = Util::formatByte(Util::getMemoryUsage() - $mem_before);
            $time_taken   = sprintf('%01.4f', (Util::microtimeFloat() - $time_start));
            $date         = \date('Y-m-d H:i:s');
            $data         .= <<<EOF
<?php
/*
Template     : {$path}
Date         : {$date}
Generated in : {$time_taken}s
Memory usage : {$mem_used}

FILE END*/
EOF;

            $this->writeTemplate($path, $data);
        }
        return $compiledTemplate;
    }

    protected function getCompiledPath($temlate)
    {
        return $path = $this->config['cache'] . $temlate . '.php';
    }

    protected function writeTemplate($template, $data)
    {
        Util::writeFile($this->getCompiledPath($template), $data);
    }
}
