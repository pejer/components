<?php

namespace DHP\components\utils;

/**
 *
 * Created by: Henrik Pejer, mr@henrikpejer.com
 * Date: 2014-02-21 13:44
 *
 */

class String
{

    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Mimics str_replace behaviour
     *
     * @param string|array $search  what to search for
     * @param string|array $replace what to replace with
     * @param null|int     $count   how many times to replace
     *
     * @return $this
     */
    public function replace($search, $replace, $count = null)
    {
        $this->value = str_replace($search, $replace, $this->value, $count);
        return $this;
    }

    /**
     * Mimics str_pad behaviour
     *
     * @param int    $length    how long should the string be padded to
     * @param string $padString the string to pad with
     * @param int    $padType   STR_PAD_x - constant values
     *
     * @return $this
     */
    public function pad($length, $padString = null, $padType = null)
    {
        $this->value = str_pad($this->value, $length, $padString, $padType);
        return $this;
    }

    /**
     * @param string $pattern     pattern to match
     * @param string $replacement what to replace pattern with
     * @param int    $limit       how many times should we replace
     * @param null   $count       count variable
     *
     * @return $this
     */
    public function pregReplace($pattern, $replacement, $limit = -1, &$count = null)
    {
        $this->value = preg_replace($pattern, $replacement, $this->value, $limit, $count);
        return $this;
    }

    /**
     * Mimics preg_match behaviour
     *
     * @param string     $pattern Pattern to match
     * @param array|null $matches found matches
     * @param int        $flags
     * @param int        $offset
     *
     * @return
     */
    public function pregMatch($pattern, &$matches = null, $flags = 0, $offset = 0)
    {
        return preg_match($pattern, $this->value, $matches, $flags, $offset);
    }

    /**
     * Mimics preg_match_all behaviour
     *
     * @param      $pattern
     * @param null $matches
     * @param int  $flags
     * @param int  $offset
     *
     * @return $this
     */
    public function pregMatchAll($pattern, &$matches = null, $flags = 0, $offset = 0)
    {
        return preg_match_all($pattern, $this->value, $matches, $flags, $offset);
    }

    /**
     * Used to update the value of the string
     */
    public function __invoke()
    {
        $this->value = func_get_arg(0);
    }

    /**
     * When class is used in an echo-type context, we return the value
     *
     * @return mixed
     */
    public function __toString()
    {
        return $this->value;
    }
}
