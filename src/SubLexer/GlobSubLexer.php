<?php

namespace AndreasGlaser\DoctrineRql\SubLexer;

use Xiag\Rql\Parser\SubLexer as XiagSubLexer;
use Xiag\Rql\Parser\Token;
use Xiag\Rql\Parser\Glob;

/**
 * Permit some special characters like dot, space, underline on strings passed as parameters, to use with LIKE too.
 * @author Rodrigo de Aquino <rodrigo@totlab.com.br>
 */
class GlobSubLexer extends XiagSubLexer\GlobSubLexer
{
    /**
     * @inheritdoc
     */
    public function getTokenAt($code, $cursor)
    {
        if (!preg_match('/([a-z0-9 _.\*\?]|\%[0-9a-f]{2})+/Ai', $code, $matches, null, $cursor))
        {
            return null;
        }
        elseif (strpos($matches[0], '?') === false && strpos($matches[0], '*') === false)
        {
            return null;
        }

        return new Token(
            Token::T_GLOB,
            $this->decodeGlob($matches[0]),
            $cursor,
            $cursor + strlen($matches[0])
        );
    }

    private function decodeGlob($glob)
    {
        return preg_replace_callback(
            '/[^\*\?]+/i',
            function ($encoded) {
                return Glob::encode(rawurldecode($encoded[0]));
            },
            $glob
        );
    }

}
