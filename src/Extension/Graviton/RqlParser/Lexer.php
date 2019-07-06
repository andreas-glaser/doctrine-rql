<?php

namespace AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser;

use Graviton\RqlParser\Lexer as GravitonLexer;
use Graviton\RqlParser\TokenStream;

/**
 * Class Lexer
 *
 * @package AndreasGlaser\DoctrineRql
 */
class Lexer extends GravitonLexer
{
    /**
     * Workaround for https://github.com/xiag-ag/rql-parser/issues/8
     *
     * @param string $code
     *
     * @return TokenStream
     */
    public function tokenize($code): TokenStream
    {
        $code = str_replace('.', '%2E', $code);

        return parent::tokenize($code);
    }
}
