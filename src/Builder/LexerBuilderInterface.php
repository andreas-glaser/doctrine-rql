<?php

namespace AndreasGlaser\DoctrineRql\Builder;

/**
 * Interface LexerBuilderInterface
 * @package AndreasGlaser\DoctrineRql\Builder
 * @author  Tim Commandeur
 */
interface LexerBuilderInterface
{
    /**
     * Builds a Lexer instance.
     *
     * @return \Xiag\Rql\Parser\Lexer
     */
    public function build();
}