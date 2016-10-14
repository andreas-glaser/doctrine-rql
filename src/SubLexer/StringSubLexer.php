<?php

namespace AndreasGlaser\DoctrineRql\SubLexer;

use Xiag\Rql\Parser\SubLexer as XiagSubLexer;
use Xiag\Rql\Parser\Token;

/**
 * Permit some special characters like dot, space, underline on strings passed as parameters, to use with LIKE too.
 * @author Rodrigo de Aquino <rodrigo@totlab.com.br>
 */
class StringSubLexer extends XiagSubLexer\StringSubLexer
{
    /**
     * @inheritdoc
     */
    public function getTokenAt($code, $cursor)
    {
        if (!preg_match('/([a-z0-9 _.]|\%[0-9a-f]{2})+/Ai', $code, $matches, null, $cursor))
        {
            return null;
        }
        elseif (ctype_digit($matches[0]))
        {
            return null;
        }

        return new Token(
            Token::T_STRING,
            rawurldecode($matches[0]),
            $cursor,
            $cursor + strlen($matches[0])
        );
    }
}
