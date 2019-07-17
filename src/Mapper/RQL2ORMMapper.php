<?php

namespace AndreasGlaser\DoctrineRql\Mapper;

use AndreasGlaser\DoctrineRql\Factory\ORMVisitorFactory;
use AndreasGlaser\DoctrineRql\Factory\ORMVisitorFactoryInterface;
use AndreasGlaser\DoctrineRql\Parser\RQLParser;
use AndreasGlaser\DoctrineRql\Parser\RQLParserInterface;
use Doctrine\ORM\QueryBuilder;

class RQL2ORMMapper implements RQL2ORMMapperInterface
{
    /**
     * @var RQLParserInterface
     */
    private $rqlParser;

    /**
     * @var ORMVisitorFactoryInterface
     */
    private $ormVisitorFactory;

    public static function create(): RQL2ORMMapperInterface
    {
        return new static(RQLParser::create(), new ORMVisitorFactory());
    }

    public function __construct(RQLParserInterface $rqlParser, ORMVisitorFactoryInterface $ormVisitorFactory)
    {
        $this->rqlParser = $rqlParser;
        $this->ormVisitorFactory = $ormVisitorFactory;
    }

    /**
     * @inheritdoc
     */
    public function mapToQueryBuilder(QueryBuilder $qb, string $rql, bool $autoRootAlias = true): void
    {
        $query = $this->rqlParser->parse($rql);
        $visitor = $this->ormVisitorFactory->createVisitor($qb, $autoRootAlias);
        $visitor->visitQuery($query);
    }
}
