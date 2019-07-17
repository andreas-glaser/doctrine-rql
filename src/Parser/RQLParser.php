<?php

namespace AndreasGlaser\DoctrineRql\Parser;

use AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Lexer as Lexer;
use AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Parser;
use Graviton\RqlParser as Graviton;

class RQLParser implements RQLParserInterface
{
    /**
     * @var Graviton\Lexer
     */
    private $lexer;

    /**
     * @var Graviton\Parser
     */
    private $parser;

    public static function create(): RQLParserInterface
    {
        return new static(new Lexer(), new Parser());
    }

    public function __construct(Graviton\Lexer $lexer, Graviton\Parser $parser)
    {
        $this->lexer = $lexer;
        $this->parser = $parser;
    }

    /**
     * @inheritdoc
     */
    public function parse(string $rql): Graviton\Query
    {
        return $this->parser->parse($this->lexer->tokenize($rql));
    }
}
