<?php

namespace AndreasGlaser\DoctrineRql\Parser;

use Graviton\RqlParser\Exception\SyntaxErrorException;
use Graviton\RqlParser\Query as RqlQuery;

interface RQLParserInterface
{
    /**
     * Parses given RQL string into an abstract syntax tree (AST).

     * @param string $rql
     *
     * @return Query
     *
     * @throws SyntaxErrorException
     */
    public function parse(string $rql): RqlQuery;
}
