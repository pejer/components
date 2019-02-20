<?php

namespace DHP\components\layout;


class Tokenizer
{

    const CODE_TYPE_UNKNOWN = -1;
    const CODE_TYPE_TEXT = 0;
    const CODE_TYPE_VARIABLE = 1;
    const CODE_TYPE_LOGIC = 2;
    const CODE_TYPE_COMMENT = 3;
    const CODE_LOGIC_IF = 4;
    const CODE_LOGIC_END = 5;
    const CODE_LOGIC_FOR = 6;
    const CODE_LOGIC_END_FOR = 7;

    protected $conf = [
        'comment'  => ['{#', '#}'],
        'logic'    => ['{%', '%}'],
        'variable' => ['{{', '}}'],
    ];

    protected $tokens = [];

    protected $pos = 0;

    protected $length   = null;
    protected $code     = '';
    protected $codeType = self::CODE_TYPE_UNKNOWN;

    public function __construct(string $file, $conf = [])
    {
        $this->conf = \array_merge($this->conf, $conf);

        // Get all _eventual_ tokens
        $this->code   = \file_get_contents($file);
        $this->pos    = 0;
        $this->length = \strlen($this->code) - 1;
    }

    public function tokenize()
    {
        // get all matching stuff
        $pattern = '/(' . \preg_quote('{', '/') . ')?/s';
        \preg_match_all($pattern, $this->code, $this->matches, \PREG_OFFSET_CAPTURE);

        while ($this->pos < $this->length) {
            switch ($this->codeType) {
                case self::CODE_TYPE_UNKNOWN:
                    $this->codeTypeUnknown();
                    break;
                case self::CODE_TYPE_COMMENT:
                    $this->codeTypeComment();
                    break;
                case self::CODE_TYPE_VARIABLE:
                    $this->codeTypeVariable();
                    break;
                case self::CODE_TYPE_LOGIC:
                    $this->codeTypeLogic();
                    break;
            }
        }
        return $this->tokens;
    }

    protected function codeTypeUnknown()
    {

        $pos = $this->pos;
        while ($pos <= $this->length) {
            if (!empty($this->matches[0][$pos][0])) {
                break;
            }
            ++$pos;
        }

        $this->addToken(self::CODE_TYPE_TEXT, \substr($this->code, $this->pos, $pos - $this->pos));
        if ($pos >= $this->length) {
            $this->pos = $pos;
            return;
        }
        // Check what the next _thing_ is
        switch ($this->code[$pos + 1]) {
            case '#':
                $this->codeType = self::CODE_TYPE_COMMENT;
                break;
            case '{':
                $this->codeType = self::CODE_TYPE_VARIABLE;
                break;
            case '%':
                $this->codeType = self::CODE_TYPE_LOGIC;
                break;
        }
        $this->pos = $pos + 2;
    }

    protected function addToken($tokenType, $data)
    {
        $this->tokens[] = [$tokenType, $data];
    }

    protected function codeTypeComment()
    {
        $pattern = '/^(.*)' . preg_quote('#}', '/') . '/';
        preg_match($pattern, substr($this->code, $this->pos), $match);

        $this->pos += strlen($match[0]);
        $this->addToken(self::CODE_TYPE_COMMENT, trim($match[1]));
        $this->codeType = self::CODE_TYPE_UNKNOWN;
    }

    protected function codeTypeVariable()
    {
        $pattern = '/^(.*)\}\}/U';
        preg_match($pattern, substr($this->code, $this->pos), $match);

        $this->pos += strlen($match[0]);
        $this->addToken(self::CODE_TYPE_VARIABLE, trim($match[1]));
        $this->codeType = self::CODE_TYPE_UNKNOWN;
    }

    protected function codeTypeLogic()
    {
        $pattern = '/^(\S+)\s(.*)' . preg_quote('%}', '/') . '/U';
        preg_match($pattern, trim(substr($this->code, $this->pos)), $match);
        // $match[1] is what logic operation that it is
        switch (strtolower($match[1])) {
            case 'if':
                $this->addToken(self::CODE_LOGIC_IF, $match[2]);
                break;
            case 'for':
                $this->addToken(self::CODE_LOGIC_FOR, $match[2]);
                break;
            case 'endif':
            case 'endfor':
                $this->addToken(self::CODE_LOGIC_END, $match[1]);
                break;
        }
        $this->pos      += strlen($match[0]) + 2;
        $this->codeType = self::CODE_TYPE_UNKNOWN;
    }

    public function getPos()
    {
        return $this->pos;
    }
}
