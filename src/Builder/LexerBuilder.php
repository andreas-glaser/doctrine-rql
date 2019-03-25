<?php

namespace AndreasGlaser\DoctrineRql\Builder;

use AndreasGlaser\DoctrineRql\Extension\Xiag\Rql\Parser\Lexer;

/**
 * Class LexerBuilder
 *
 * @package AndreasGlaser\DoctrineRql\Builder
 * @author  Tim Commandeur
 */
class LexerBuilder implements LexerBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function build()
    {
        return new Lexer();
    }
}