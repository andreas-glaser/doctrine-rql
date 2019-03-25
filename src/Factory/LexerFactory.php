<?php

namespace AndreasGlaser\DoctrineRql\Factory;

use AndreasGlaser\DoctrineRql\Builder\LexerBuilder;
use AndreasGlaser\DoctrineRql\Builder\LexerBuilderInterface;

/**
 * Class RQLParser
 *
 * @package AndreasGlaser\DoctrineRql\Helper
 * @author  Tim Commandeur
 */
class LexerFactory
{
    /**
     * @var \AndreasGlaser\DoctrineRql\Builder\LexerBuilderInterface
     */
    private static $lexerBuilder;

    /**
     * Sets the lexer instance.
     *
     * @param \AndreasGlaser\DoctrineRql\Builder\LexerBuilderInterface
     * @author Tim Commandeur
     */
    public static function setLexerBuilder(LexerBuilderInterface $lexerBuilder)
    {
        static::$lexerBuilder = $lexerBuilder;
    }

    /**
     * Returns the Lexer instance.
     *
     * @return \Xiag\Rql\Parser\Lexer
     * @author Tim Commandeur
     */
    public static function createLexer()
    {
        return static::getLexerBuilder()->build();
    }

    /**
     * @return \AndreasGlaser\DoctrineRql\Builder\LexerBuilderInterface
     */
    private static function getLexerBuilder()
    {
        return static::$lexerBuilder ?: new LexerBuilder();
    }
}