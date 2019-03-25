<?php

namespace AndreasGlaser\DoctrineRql\Factory;

use AndreasGlaser\DoctrineRql\Builder\LexerBuilder;
use AndreasGlaser\DoctrineRql\Builder\LexerBuilderInterface;
use AndreasGlaser\DoctrineRql\Extension\Xiag\Rql\Parser\Lexer;
use PHPUnit\Framework\TestCase;

/**
 * Class LexerSubtypeWrapper
 *
 * @package AndreasGlaser\DoctrineRql\Factory
 * @author  Tim Commandeur
 */
class LexerSubtypeWrapper extends \Xiag\Rql\Parser\Lexer
{

}

/**
 * Class LexerBuilderWrapper
 *
 * @package AndreasGlaser\DoctrineRql\Factory
 * @author  Tim Commandeur
 */
class LexerBuilderWrapper implements LexerBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function build()
    {
        return new LexerSubtypeWrapper();
    }
}

/**
 * Class LexerFactoryTest
 *
 * @package AndreasGlaser\DoctrineRql\Factory
 * @author  Tim Commandeur
 */
class LexerFactoryTest extends TestCase
{
    public function testCreateLexerWithDefaultBuilder()
    {
        $this->assertInstanceOf(Lexer::class, LexerFactory::createLexer());
    }

    public function testCreateLexerWithOtherBuilder()
    {
        LexerFactory::setLexerBuilder(new LexerBuilderWrapper());

        try
        {
            $result = LexerFactory::createLexer();
            $this->assertNotInstanceOf(Lexer::class, $result);
            $this->assertInstanceOf(LexerSubtypeWrapper::class, $result);
        }
        finally
        {
            LexerFactory::setLexerBuilder(new LexerBuilder());
        }
    }
}
