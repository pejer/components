<?php

namespace DHP_core\component\layout;

use DHP_core\component\interfaces\LayoutViewInterface;
use DHP_core\component\interfaces\RequestInterface;
use DHP_core\component\interfaces\ResponseInterface;
use DHP_core\component\interfaces\UnicornInterface;

class View implements LayoutViewInterface
{

    protected $debug        = false;
    protected $layout       = null;
    protected $current_vars = [];

    public function __construct(Layout $layout)
    {
        $this->layout = $layout;
    }

    public function setDebug(bool $debug)
    {
        $this->debug = $debug;
    }

    public function render(string $template, array $vars = [], $parent = null)
    {
        $this->current_vars = $vars;
        extract($vars);
        ob_start();
        require $template;
        $this->current_vars = [];
        return ob_get_clean();
    }

    protected function include(string $template)
    {
        return $this->layout->render($template, $this->current_vars);
    }

    protected function filter($filters, string $string)
    {
        $filters = \is_array($filters) ? $filters : [$filters];
        $filteredString = $string;
        foreach ($filters as $filter) {
            switch ($filter) {
                case 'escape':
                case 'html':
                case 'e':
                    $filteredString = \htmlspecialchars($filteredString, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    break;
                case 'uppercase':
                case 'upper':
                    $filteredString =
                        \function_exists('mb_strtoupper') ? \mb_strtoupper($filteredString) : \strtoupper(
                            $filteredString
                        );
                    break;
                case 'ucfirst':
                    $filteredString = \ucfirst($filteredString);
                    break;
                case 'lower':
                case 'lowercase':
                    $filteredString =
                        \function_exists('mb_strtolower') ? \mb_strtolower($filteredString) : \strtolower(
                            $filteredString
                        );
                    break;
                case 'url':
                case 'url_encode':
                    $filteredString = \urlencode($filteredString);
                    break;
                case 'striptags':
                case 'strip_tags':
                    $filteredString = \strip_tags($filteredString);
                    break;
            }
        }
        return $filteredString;
    }

    protected function unwrap($variable, $keys)
    {
        $ref = &$variable;
        foreach ($keys as $key) {
            if (\is_array($ref)) {
                $ref = &$ref[$key];
            } else {
                $ref = &$ref->{$key};
            }
        }
        return $ref;
    }
}