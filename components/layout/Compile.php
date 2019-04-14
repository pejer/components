<?php

namespace DHP\components\layout;

/**
 * Class Compile
 *
 * Compiles layouts and stores them for later use.
 *
 * @package DHP_core\component\layout
 */
class Compile implements LayoutCompileInterface
{

    protected $compiledMethods = [
        Tokenizer::CODE_TYPE_VARIABLE => 'compileVariable',
        Tokenizer::CODE_TYPE_TEXT     => 'compileText',
        Tokenizer::CODE_TYPE_COMMENT  => 'compileComment',
        Tokenizer::CODE_LOGIC_IF      => 'compileLogicIf',
        Tokenizer::CODE_LOGIC_END     => 'compileLogicEnd',
        Tokenizer::CODE_LOGIC_FOR     => 'compileLogicFor',
        Tokenizer::CODE_LOGIC_ELSE    => 'compileLogicElse'
      ];

    protected $logicStates = [];
    protected $logicStatesIndex = -1;

    public function __construct()
    {
    }

    public function compile($tokens)
    {
        $compiledData = <<<EOF
<?php
// @codingStandardsIgnoreFile
// phpcs:ignoreFile
/** @noinspection  ALL*/
?>
EOF;

        foreach ($tokens as $token) {
            if (!isset($this->compiledMethods[$token[0]])) {
                throw new \RuntimeException("No compile method for {$token[0]}.");
            }
            $compiledData .= $this->{$this->compiledMethods[$token[0]]}($token[1]);
        }
        return $compiledData;
    }

    // Todo: Handle variables, filters and methods mo betteah!
    protected function compileVariable($tokenData, $skipTag = false)
    {
        switch (true) {
            case (\strpos($tokenData, '(') !== false):
                $parts     = explode('(', $tokenData);
                $tokenData = '$this->' . $parts[0] . '(';
                // what if this is a variable....?
                $args = explode(',', trim($parts[1], ') '));
                foreach ($args as $arg) {
                    if (!($arg[0] == '"' || $arg[0] == "'")) {
                        $arg = $this->compileVariable(trim($arg), true);
                    }
                    $tokenData .= $arg . ', ';
                }
                $tokenData = trim($tokenData, ', ') . ')';
                break;
            case (\strpos($tokenData, '|') !== false):
                $filters = explode('|', $tokenData);
                $var = array_shift($filters);
                $var = $this->compileVariable($var, true);
                $tokenData = '$this->filter([\'' . implode('\',\'', $filters).'\'], ';
                $tokenData .= ' ' . $var . ')';
                break;
            case (\strpos($tokenData, '.') !== false):
                $keys      = \explode('.', $tokenData);
                $tokenData = '$this->unwrap($' . \array_shift($keys) . ',[';
                foreach ($keys as $key) {
                    $tokenData .= "\"{$key}\",";
                }
                $tokenData = trim($tokenData, ', ');
                $tokenData .= '])';
                break;
            default:
                $tokenData = "\${$tokenData}";
                break;
        }
        if ($skipTag) {
            return $tokenData;
        }
        return <<<EOF
<?php echo $tokenData; ?>
EOF;
    }

    protected function compileText($tokenData)
    {
        return <<<EOF
{$tokenData}
EOF;
    }

    protected function compileLogicIf($tokenData)
    {
        $return = [];
        $words  = preg_split('/\s/i', $tokenData, null, \PREG_SPLIT_NO_EMPTY);
        foreach ($words as $word) {
            $word = strtolower($word);
            switch ($word) {
                case 'and':
                case '&&':
                case 'AND':
                    $return[] = '&&';
                    break;
                case '==':
                case '===':
                case '<=':
                case '>=':
                case '!==':
                case '!===':
                case '!<=':
                case '!>=':
                case 'true':
                case 'false':
                case 'null':
                    $return[] = $word;
                    break;
                default: // Its a variable. For now :)
                    $return[] = '$' . $word;
                    break;
            }
        }
        $return[] = '';
        $this->logicStateAppend('if');
        return '<?php if (' . trim(implode(' ', $return)) . "): ?>\n";
    }

    protected function compileLogicElse($tokenData) { 
      switch($this->logicStateGetCurrent()) {
          case 'forloop':
               $return = "<?php endforeach; else: ?>";
              $this->logicStateRemove();
          break;
          default:
                $return = "<?php else: ?>\n";
          break;
      }
      return $return;
    }

    protected function compileLogicEnd($tokenData)
    {
      switch ($this->logicStateGetCurrent()) {
            case 'if':
                $tokenData = 'endif';
                break;
            case 'forloop':
              $tokenData = 'endforeach; endif';
              $this->logicStateRemove();
            default:
                break;
        }
        $this->logicStateRemove();
        return '<?php ' . $tokenData . ';?>' . "\n";
    }

    protected function compileLogicFor($tokenData)
    {
        list($var, $collection) = explode('in', $tokenData);
        $var        = trim($var);
        $collection = trim($collection);
        $this->logicStateAppend('if');
        $this->logicStateAppend('forloop');
        return <<<EOF
<?php 

if (!empty(\$$collection) && count(\$$collection) > 0) :
\$loop = (object) [
    'index' => 0,
    'zeroIndex' => -1,
    'parent' => isset(\$loop)? \$loop : null,
    'first' => true,
    'last' => false,
    'length' => count(\$$collection)
];
foreach (\$$collection as \$$var): ++\$loop->index; ++\$loop->zeroIndex;\$loop->first = \$loop->index == 1?true:false;\$loop->last = \$loop->index == \$loop->length? true: false;?>

EOF;
    }

    /**
     * @param mixed $tokenData The token data.
     * @return string
     *
     * @codingStandardIgnore
     */
    protected function compileComment($tokenData)
    {
        return <<<EOF
<?php if (\$this->debug): ?>
<!-- {$tokenData} -->
<?php endif; ?>
EOF;
    }

    protected function logicStateAppend($state)
    {
        $this->logicStates[] = $state;
        ++$this->logicStatesIndex;
    }

    protected function logicStateGetCurrent()
    {
        $return = null;
        if ($this->logicStatesIndex>-1
            && !empty($this->logicStates[$this->logicStatesIndex])
        ) {
            $return = $this->logicStates[$this->logicStatesIndex];
        }
        return $return;
    }

    protected function logicStateGetPrevious()
    {
        $return = null;
        if ($this->logicStatesIndex>0
          && !empty($this->logicStates[$this->logicStatesIndex-1])
        ) {
            $return = $this->logicStates[$this->logicStatesIndex-1];
        }
        return $return;
    }
    protected function logicStateRemove()
    {
        \array_pop($this->logicStates);
        --$this->logicStatesIndex;
    }
}
