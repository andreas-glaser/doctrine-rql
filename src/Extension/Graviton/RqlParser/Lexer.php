<?php

namespace AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser;

use Graviton\RqlParser\SubLexer as GravitonSubLexer;

/**
 * Class Lexer
 *
 * @package AndreasGlaser\DoctrineRql
 * @author  Andreas Glaser
 */
class Lexer extends \Graviton\RqlParser\Lexer
{
    /**
     * Workaround for https://github.com/xiag-ag/rql-parser/issues/8
     *
     * @param string $code
     *
     * @return \Graviton\RqlParser\TokenStream
     * @author Andreas Glaser
     */
    public function tokenize($code)
    {
        $code = str_replace('.', '%2E', $code);

        return parent::tokenize($code);
    }
}
