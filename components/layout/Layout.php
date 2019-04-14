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
        $compiledTemplate = $this->compile($template, true);
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
            $time_start   = \microtime(true);
            $mem_before   = \memory_get_usage();
            $data         = $this->compiler->compile($tokenizer->tokenize());
            $mem_used     = sprintf('%01.2f %s',(\memory_get_usage() - $mem_before) / 1024, 'Kb');
            $time_taken   = sprintf('%01.4f %s', (microtime(true) - $time_start),'s');
            $date         = \date('Y-m-d H:i:s');
            $data         .= <<<EOF
<?php
/*
Template     : {$path}
Date         : {$date}
Generated in : {$time_taken}
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
        \file_put_contents($this->getCompiledPath($template), $data);
    }
}
