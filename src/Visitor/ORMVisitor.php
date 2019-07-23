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
class ORMVisitor implements ORMVisitorInterface
{
    /**
     * @var string[]
     */
    protected const SCALAR_MAP = [
        \Graviton\RqlParser\Node\Query\ScalarOperator\EqNode::class => 'eq',
        \Graviton\RqlParser\Node\Query\ScalarOperator\NeNode::class => 'neq',
        \Graviton\RqlParser\Node\Query\ScalarOperator\LtNode::class => 'lt',
        \Graviton\RqlParser\Node\Query\ScalarOperator\GtNode::class => 'gt',
        \Graviton\RqlParser\Node\Query\ScalarOperator\LeNode::class => 'lte',
        \Graviton\RqlParser\Node\Query\ScalarOperator\GeNode::class => 'gte',
        \Graviton\RqlParser\Node\Query\ScalarOperator\LikeNode::class => 'like',
    ];

    /**
     * @var string[]
     */
    protected const NULL_OPERATOR_MAP = [
        \AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Node\Query\NullOperator\IsNullNode::class => 'isNull',
        \AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Node\Query\NullOperator\IsNotNullNode::class => 'isNotNull',
    ];

    /**
     * @var string[]
     */
    protected const ARRAY_MAP = [
        \Graviton\RqlParser\Node\Query\ArrayOperator\InNode::class => 'in',
        \Graviton\RqlParser\Node\Query\ArrayOperator\OutNode::class => 'notIn',
    ];

    /**
     * @var string[]
     */
    protected const LOGIC_MAP = [
        \Graviton\RqlParser\Node\Query\LogicalOperator\AndNode::class => \Doctrine\ORM\Query\Expr\Andx::class,
        \Graviton\RqlParser\Node\Query\LogicalOperator\OrNode::class => \Doctrine\ORM\Query\Expr\Orx::class,
        \Graviton\RqlParser\Node\Query\LogicalOperator\NotNode::class => \AndreasGlaser\DoctrineRql\Extension\Doctrine\ORM\Query\Expr\Notx::class,
    ];

    /**
     * @var QueryBuilder
     */
    protected $qb;

    /**
     * @var bool
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
     * @var string
     */
    protected $rootAlias;

    /**
     * @var ExpressionBuilder
     */
    private $expressionBuilder;

    /**
     * @param QueryBuilder $qb
     * @param bool         $autoRootAlias true to prepend the root alias of the
     *     query builder to the field names from the RQL query, false otherwise.
     */
    function __construct(QueryBuilder $qb, bool $autoRootAlias = true)
    {
        $this->qb = $qb;
        $this->autoRootAlias = $autoRootAlias;
        $this->buildPathToAliasMap();
    }

    protected function buildPathToAliasMap(): void
    {
        $this->rootAlias = ArrayHelper::getFirstValue($this->qb->getRootAliases());
        $this->aliasMap[$this->rootAlias] = $this->rootAlias;

        if (array_key_exists($this->rootAlias, $this->qb->getDQLParts()['join'])) {
            /** @var Expr\Join $part */
            foreach ($this->qb->getDQLParts()['join'][$this->rootAlias] AS $part) {
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
     * @inheritdoc
     */
    public function visitQuery(RqlQuery $query): void
    {
        // if ($query->getSelect()) {
            // todo: Implement this
        // }

        if ($query->getQuery()) {
            $this->qb->andWhere($this->walkNodes($query->getQuery()));
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
        if (!$method = ArrayHelper::get(static::SCALAR_MAP, get_class($node))) {
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
        if (!$method = ArrayHelper::get(static::ARRAY_MAP, get_class($node))) {
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
        if (!$class = ArrayHelper::get(static::LOGIC_MAP, get_class($node))) {
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
        if (!$method = ArrayHelper::get(static::NULL_OPERATOR_MAP, get_class($node))) {
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
            $this->qb->addOrderBy($this->pathToAlias($field), $order === 1 ? 'ASC' : 'DESC');
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

    /**
     * @param mixed $value
     *
     * @return string
     */
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
            $path = $this->rootAlias . '.' . $path;
        }

        $lastPos = strrpos($path, '.');
        if (false === $lastPos) {
            throw new VisitorException(sprintf('Incomplete field path "%s"', $path));
        }

        $field = substr($path, $lastPos + 1);
        $entityPath = substr($path, 0, $lastPos);

        $entityAlias = ArrayHelper::get($this->aliasMap, $entityPath);
        if (null === $entityAlias) {
            throw new VisitorException(sprintf('Unknown field path "%s"', $path));
        }

        return $entityAlias . '.' . $field;
    }
}
