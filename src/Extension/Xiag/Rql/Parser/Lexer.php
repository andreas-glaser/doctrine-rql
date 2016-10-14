<?php

namespace AndreasGlaser\DoctrineRql\Extension\Xiag\Rql\Parser;

use Xiag\Rql\Parser\SubLexer as XiagSubLexer;

/**
 * Class Lexer
 *
 * @package AndreasGlaser\DoctrineRql
 * @author  Andreas Glaser
 */
class Lexer extends \Xiag\Rql\Parser\Lexer
{
    /**
     * Workaround for https://github.com/xiag-ag/rql-parser/issues/8
     *
     * @param string $code
     *
     * @return \Xiag\Rql\Parser\TokenStream
     * @author Andreas Glaser
     */
    public function tokenize($code)
    {
        $code = str_replace('.', '%2E', $code);

        return parent::tokenize($code);
    }
}
