<?php

namespace AndreasGlaser\DoctrineRql\Visitor;

use AndreasGlaser\DoctrineRql\Extension\Doctrine\ORM\Query\ExpressionBuilder;
use AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Node\Query\AbstractNullOperatorNode;
use AndreasGlaser\Helpers\ArrayHelper;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Graviton\RqlParser\Glob;
use Graviton\RqlParser\Node;
use Graviton\RqlParser\Node\AbstractQueryNode;
use Graviton\RqlParser\Node\Query\AbstractArrayOperatorNode;
use Graviton\RqlParser\Node\Query\AbstractLogicalOperatorNode;
use Graviton\RqlParser\Node\Query\AbstractScalarOperatorNode;
use Graviton\RqlParser\Query as RqlQuery;

/**
 * Class ORMVisitor
 *
 * @package AndreasGlaser\DoctrineRql\Visitor
 */
class ORMVisitor
{
    /**
     * @var ExpressionBuilder
     */
    private $expressionBuilder;

    /**
     * @var array
     */
    protected $scalarMap = [
        \Graviton\RqlParser\Node\Query\ScalarOperator\EqNode::class => 'eq',
        \Graviton\RqlParser\Node\Query\ScalarOperator\NeNode::class => 'neq',
        \Graviton\RqlParser\Node\Query\ScalarOperator\LtNode::class => 'lt',
        \Graviton\RqlParser\Node\Query\ScalarOperator\GtNode::class => 'gt',
        \Graviton\RqlParser\Node\Query\ScalarOperator\LeNode::class => 'lte',
        \Graviton\RqlParser\Node\Query\ScalarOperator\GeNode::class => 'gte',
        \Graviton\RqlParser\Node\Query\ScalarOperator\LikeNode::class => 'like',
    ];

    /**
     * @var array
     */
    protected $nullOperatorMap = [
        \AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Node\Query\NullOperator\IsNullNode::class => 'isNull',
        \AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Node\Query\NullOperator\IsNotNullNode::class => 'isNotNull',
    ];

    /**
     * @var array
     */
    protected $arrayMap = [
        \Graviton\RqlParser\Node\Query\ArrayOperator\InNode::class => 'in',
        \Graviton\RqlParser\Node\Query\ArrayOperator\OutNode::class => 'notIn',
    ];

    /**
     * @var array
     */
    protected $logicMap = [
        \Graviton\RqlParser\Node\Query\LogicalOperator\AndNode::class => \Doctrine\ORM\Query\Expr\Andx::class,
        \Graviton\RqlParser\Node\Query\LogicalOperator\OrNode::class => \Doctrine\ORM\Query\Expr\Orx::class,
        \Graviton\RqlParser\Node\Query\LogicalOperator\NotNode::class => \AndreasGlaser\DoctrineRql\Extension\Doctrine\ORM\Query\Expr\Notx::class,
    ];

    /**
     * @var QueryBuilder
     */
    protected $qb;

    /**
     * @var string|null
     */
    protected $autoRootAlias;

    /**
     * @var array
     */
    protected $aliasMap = [];

    /**
     * @var int
     */
    protected $parameterCount = 0;

    /**
     * @param QueryBuilder $qb
     * @param RqlQuery     $query
     * @param bool         $autoRootAlias
     *
     * @throws VisitorException
     */
    public function append(QueryBuilder $qb, RqlQuery $query, bool $autoRootAlias = true): void
    {
        $this->reset();

        $this->qb = $qb;

        if ($autoRootAlias) {
            $this->autoRootAlias = ArrayHelper::getFirstValue($this->qb->getRootAliases());
        } else {
            $this->autoRootAlias = null;
        }

        $this->buildPathToAliasMap();
        $this->visitQuery($query);
    }

    /**
     * Resets values for object re-use.
     *
     * @return ORMVisitor
     */
    public function reset(): self
    {
        $this->qb = null;
        $this->autoRootAlias = null;
        $this->aliasMap = [];
        $this->parameterCount = 0;

        return $this;
    }

    protected function buildPathToAliasMap(): void
    {
        $rootAlias = ArrayHelper::getFirstValue($this->qb->getRootAliases());
        $this->aliasMap[$rootAlias] = $rootAlias;

        if (array_key_exists($rootAlias, $this->qb->getDQLParts()['join'])) {
            /** @var Expr\Join $part */
            foreach ($this->qb->getDQLParts()['join'][$rootAlias] AS $part) {
                $alias = $part->getAlias();
                $join = $part->getJoin();
                $path = $alias;
                $pieces = explode('.', $join);
                if ($parentAlias = ArrayHelper::getKeyByValue($this->aliasMap, $pieces[0])) {
                    $path = $parentAlias . '.' . $alias;
                }
                $this->aliasMap[$path] = $alias;
            }
        }
    }

    /**
     * @return ExpressionBuilder
     */
    protected function getExpressionBuilder(): ExpressionBuilder
    {
        if (null === $this->expressionBuilder) {
            $this->expressionBuilder = new ExpressionBuilder();
        }

        return $this->expressionBuilder;
    }

    /**
     * @param AbstractQueryNode $node
     *
     * @return Expr\Comparison|Expr\Composite|Expr\Func|string
     * @throws VisitorException
     */
    protected function walkNodes(AbstractQueryNode $node)
    {
        if ($node instanceof AbstractScalarOperatorNode) {
            return $this->visitScalar($node);
        } elseif ($node instanceof AbstractArrayOperatorNode) {
            return $this->visitArray($node);
        } elseif ($node instanceof AbstractLogicalOperatorNode) {
            return $this->visitLogic($node);
        } elseif ($node instanceof AbstractNullOperatorNode) {
            return $this->visitNullOperatorNode($node);
        } else {
            throw new VisitorException(sprintf('Unsupported node "%s"', get_class($node)));
        }
    }

    /**
     * @param RqlQuery $query
     *
     * @throws VisitorException
     */
    protected function visitQuery(RqlQuery $query): void
    {
        if ($selectNode = $query->getSelect()) {
            // todo: Implement this
        }

        if ($abstractQueryNode = $query->getQuery()) {
            $this->qb->andWhere($this->walkNodes($abstractQueryNode));
        }

        if ($query->getSort()) {
            $this->visitSort($query->getSort());
        }

        if ($query->getLimit()) {
            $this->visitLimit($query->getLimit());
        }
    }

    /**
     * @param AbstractScalarOperatorNode $node
     *
     * @return Expr\Comparison
     * @throws VisitorException
     */
    protected function visitScalar(AbstractScalarOperatorNode $node): Expr\Comparison
    {
        if (!$method = ArrayHelper::get($this->scalarMap, get_class($node))) {
            throw new VisitorException(sprintf('Unsupported node "%s"', get_class($node)));
        }

        $pathToField = $node->getField();
        $parameterName = $this->addParameter($node->getValue());
        $expr = $this->getExpressionBuilder()->$method($this->pathToAlias($pathToField), $parameterName);

        return $expr;
    }

    /**
     * @param AbstractArrayOperatorNode $node
     *
     * @return Expr\Func
     * @throws VisitorException
     */
    protected function visitArray(AbstractArrayOperatorNode $node): Expr\Func
    {
        if (!$method = ArrayHelper::get($this->arrayMap, get_class($node))) {
            throw new VisitorException(sprintf('Unsupported node "%s"', get_class($node)));
        }

        $pathToField = $node->getField();
        $parameterNames = array_map([$this, 'addParameter'], $node->getValues());
        $expr = $this->qb->expr()->$method($this->pathToAlias($pathToField), implode(', ', $parameterNames));

        return $expr;
    }

    /**
     * @param AbstractLogicalOperatorNode $node
     *
     * @return Expr\Composite|Expr\Func
     * @throws VisitorException
     */
    protected function visitLogic(AbstractLogicalOperatorNode $node)
    {
        if (!$class = ArrayHelper::get($this->logicMap, get_class($node))) {
            throw new VisitorException(sprintf('Unsupported node "%s"', get_class($node)));
        }

        $expr = new $class();
        foreach ($node->getQueries() as $query) {
            $expr->add($this->walkNodes($query));
        }

        // Notx workaround
        if ($node instanceof Node\Query\LogicalOperator\NotNode) {
            $expr = new Expr\Func('NOT', $expr->getParts());
        }

        return $expr;
    }

    /**
     * Apply $queryBuilder->expr()->isNull(fieldName) / $queryBuilder->expr()->isNotNull()
     *
     * @param AbstractNullOperatorNode $node
     *
     * @return string
     * @throws VisitorException
     */
    protected function visitNullOperatorNode(AbstractNullOperatorNode $node): string
    {
        if (!$method = ArrayHelper::get($this->nullOperatorMap, get_class($node))) {
            throw new VisitorException(sprintf('Unsupported node "%s"', get_class($node)));
        }

        $pathToField = $node->getField();
        $expr = $this->qb->expr()->$method($this->pathToAlias($pathToField));

        return $expr;
    }

    /**
     * @param Node\SortNode $node
     */
    protected function visitSort(Node\SortNode $node): void
    {
        foreach ($node->getFields() as $field => $order) {
            $this->qb->orderBy($this->pathToAlias($field), $order === 1 ? 'ASC' : 'DESC');
        }
    }

    /**
     * @param Node\LimitNode $node
     */
    protected function visitLimit(Node\LimitNode $node): void
    {
        $this->qb
            ->setMaxResults($node->getLimit())
            ->setFirstResult($node->getOffset());
    }

    protected function addParameter($value): string
    {
        if ($value instanceof Glob) {
            $value = $value->toLike();
        }

        $parameterName = ':_rql_' . ++$this->parameterCount;
        $this->qb->setParameter($parameterName, $value);

        return $parameterName;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    protected function pathToAlias(string $path): string
    {
        if ($this->autoRootAlias) {
            $path = $this->autoRootAlias . '.' . $path;
        }

        $lastPos = strrpos($path, '.');
        $field = substr($path, $lastPos + 1);
        $path = substr($path, 0, $lastPos);

        return ArrayHelper::get($this->aliasMap, $path) . '.' . $field;
    }
}
