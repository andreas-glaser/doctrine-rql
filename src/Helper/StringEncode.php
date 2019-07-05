<?php

namespace AndreasGlaser\DoctrineRql\Helper;

/**
 * Class StringEncode
 *
 * @package AndreasGlaser\DoctrineRql\Helper
 */
class StringEncode
{
    /**
     * @param string $string
     *
     * @return string
     * @source https://github.com/xiag-ag/rql-parser#string-encoding-in-php
     */
    public static function encode(string $string): string
    {
        return strtr(rawurlencode($string), [
            '-' => '%2D',
            '_' => '%5F',
            '.' => '%2E',
            '~' => '%7E',
        ]);
    }
}
