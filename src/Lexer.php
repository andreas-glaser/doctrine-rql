<?php

/**
 * Class Lexer
 *
 * Require Xiag\Rql\Parser
 *
 * @package AndreasGlaser\DoctrineRql
 * @author  Andreas Glaser
 * @author  Rodrigo de Aquino <rodrigo@totlab.com.br>
 */

namespace AndreasGlaser\DoctrineRql;

use Xiag\Rql\Parser\Lexer as BaseLexer;
use Xiag\Rql\Parser\Exception\SyntaxErrorException;
use Xiag\Rql\Parser\Token;
use Xiag\Rql\Parser\TokenStream;

use Xiag\Rql\Parser\SubLexerChain;
use Xiag\Rql\Parser\SubLexer as XiagSubLexer;
use Xiag\Rql\Parser\SubLexerInterface;

use AndreasGlaser\DoctrineRql\SubLexer as SubLexer;

/**
 * @author   List of contributors <https://github.com/libgraviton/GravitonRqlParserBundle/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class Lexer extends BaseLexer
{
    /**
     * Permit some special characters like dot, space, underline on strings passed as parameters, to use with LIKE too.
     *
     * @author Andreas Glaser
     * @author Rodrigo de Aquino <rodrigo@totlab.com.br>
     *
     * @return SubLexerChain
     */
    public static function createDefaultSubLexer()
    {
        return (new SubLexerChain())
            ->addSubLexer(new XiagSubLexer\ConstantSubLexer())
            ->addSubLexer(new XiagSubLexer\PunctuationSubLexer())
            ->addSubLexer(new XiagSubLexer\FiqlOperatorSubLexer())
            ->addSubLexer(new XiagSubLexer\RqlOperatorSubLexer())
            ->addSubLexer(new XiagSubLexer\TypeSubLexer())

            ->addSubLexer(new SubLexer\GlobSubLexer())
            ->addSubLexer(new SubLexer\StringSubLexer())
            ->addSubLexer(new XiagSubLexer\DatetimeSubLexer())
            ->addSubLexer(new XiagSubLexer\NumberSubLexer())

            ->addSubLexer(new XiagSubLexer\SortSubLexer());
    }

}
