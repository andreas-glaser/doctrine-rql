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
 * @author  Andreas Glaser
 * @author  Dominic Tubach <dominic.tubach@to.com>
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
        'Graviton\RqlParser\Node\Query\ScalarOperator\EqNode'   => 'eq',
        'Graviton\RqlParser\Node\Query\ScalarOperator\NeNode'   => 'neq',
        'Graviton\RqlParser\Node\Query\ScalarOperator\LtNode'   => 'lt',
        'Graviton\RqlParser\Node\Query\ScalarOperator\GtNode'   => 'gt',
        'Graviton\RqlParser\Node\Query\ScalarOperator\LeNode'   => 'lte',
        'Graviton\RqlParser\Node\Query\ScalarOperator\GeNode'   => 'gte',
        'Graviton\RqlParser\Node\Query\ScalarOperator\LikeNode' => 'like',
    ];

    /**
     * @var array
     */
    protected $nullOperatorMap = [
        'AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Node\Query\NullOperator\IsNullNode'    => 'isNull',
        'AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Node\Query\NullOperator\IsNotNullNode' => 'isNotNull',
    ];

    /**
     * @var array
     */
    protected $arrayMap = [
        'Graviton\RqlParser\Node\Query\ArrayOperator\InNode'  => 'in',
        'Graviton\RqlParser\Node\Query\ArrayOperator\OutNode' => 'notIn',
    ];

    /**
     * @var array
     */
    protected $logicMap = [
        'Graviton\RqlParser\Node\Query\LogicalOperator\AndNode' => '\Doctrine\ORM\Query\Expr\Andx',
        'Graviton\RqlParser\Node\Query\LogicalOperator\OrNode'  => '\Doctrine\ORM\Query\Expr\Orx',
        'Graviton\RqlParser\Node\Query\LogicalOperator\NotNode' => '\AndreasGlaser\DoctrineRql\Extension\Doctrine\ORM\Query\Expr\Notx',
    ];

    /**
     * @var QueryBuilder
     */
    protected $qb;

    /**
     * @var string
     */
    protected $autoRootAlias;

    /**
     * @var array
     */
    protected $aliasMap = [];

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param \Graviton\RqlParser\Query     $query
     * @param bool                       $autoRootAlias
     *
     * @author Andreas Glaser
     */
    public function append(QueryBuilder $qb, RqlQuery $query, $autoRootAlias = true)
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
     * @author Andreas Glaser
     */
    public function reset()
    {
        $this->qb = null;
        $this->autoRootAlias = null;
        $this->aliasMap = [];
    }

    /**
     * @author Andreas Glaser
     */
    protected function buildPathToAliasMap()
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
     *
     * @author Dominic Tubach <dominic.tubach@to.com>
     */
    protected function getExpressionBuilder()
    {
        if (null === $this->expressionBuilder) {
            $this->expressionBuilder = new ExpressionBuilder();
        }

        return $this->expressionBuilder;
    }

    /**
     * @param \Graviton\RqlParser\Node\AbstractQueryNode $node
     *
     * @return mixed
     * @throws \AndreasGlaser\DoctrineRql\Visitor\VisitorException
     * @author Andreas Glaser
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
     * @param \Graviton\RqlParser\Query $query
     *
     * @throws \AndreasGlaser\DoctrineRql\Visitor\VisitorException
     * @author Andreas Glaser
     */
    protected function visitQuery(RqlQuery $query)
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
     * @param \Graviton\RqlParser\Node\Query\AbstractScalarOperatorNode $node
     *
     * @return mixed
     * @throws \AndreasGlaser\DoctrineRql\Visitor\VisitorException
     * @author Andreas Glaser
     * @author Dominic Tubach <dominic.tubach@to.com>
     */
    protected function visitScalar(AbstractScalarOperatorNode $node)
    {
        if (!$method = ArrayHelper::get($this->scalarMap, get_class($node))) {
            throw new VisitorException(sprintf('Unsupported node "%s"', get_class($node)));
        }

        $parameterName = ':param_' . uniqid();
        $pathToField = $node->getField();
        $expr = $this->getExpressionBuilder()->$method($this->pathToAlias($pathToField), $parameterName);
        $parameter = $node->getValue();

        if ($parameter instanceof Glob) {
            $parameter = $parameter->toLike();
        }

        $this->qb->setParameter($parameterName, $parameter);

        return $expr;
    }

    /**
     * @param \Graviton\RqlParser\Node\Query\AbstractArrayOperatorNode $node
     *
     * @return mixed
     * @throws \AndreasGlaser\DoctrineRql\Visitor\VisitorException
     * @author Andreas Glaser
     */
    protected function visitArray(AbstractArrayOperatorNode $node)
    {
        if (!$method = ArrayHelper::get($this->arrayMap, get_class($node))) {
            throw new VisitorException(sprintf('Unsupported node "%s"', get_class($node)));
        }

        $pathToField = $node->getField();
        $expr = $this->qb->expr()->$method($this->pathToAlias($pathToField), $node->getValues());

        return $expr;
    }

    /**
     * @param \Graviton\RqlParser\Node\Query\AbstractLogicalOperatorNode $node
     *
     * @return mixed
     * @throws \AndreasGlaser\DoctrineRql\Visitor\VisitorException
     * @author Andreas Glaser
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
     * @param \AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Node\Query\AbstractNullOperatorNode $node
     *
     * @return mixed
     * @throws \AndreasGlaser\DoctrineRql\Visitor\VisitorException
     * @author Andreas Glaser
     */
    protected function visitNullOperatorNode(AbstractNullOperatorNode $node)
    {
        if (!$method = ArrayHelper::get($this->nullOperatorMap, get_class($node))) {
            throw new VisitorException(sprintf('Unsupported node "%s"', get_class($node)));
        }

        $pathToField = $node->getField();
        $exp = $this->qb->expr()->$method($this->pathToAlias($pathToField));

        return $exp;
    }

    /**
     * @param \Graviton\RqlParser\Node\SortNode $node
     *
     * @author Andreas Glaser
     */
    protected function visitSort(Node\SortNode $node)
    {
        foreach ($node->getFields() as $field => $order) {
            $this->qb->orderBy($this->pathToAlias($field), $order === 1 ? 'ASC' : 'DESC');
        }
    }

    /**
     * @param \Graviton\RqlParser\Node\LimitNode $node
     *
     * @author Andreas Glaser
     */
    protected function visitLimit(Node\LimitNode $node)
    {
        $this->qb
            ->setMaxResults($node->getLimit())
            ->setFirstResult($node->getOffset());
    }

    /**
     * @param $path
     *
     * @return null
     * @author Andreas Glaser
     */
    protected function pathToAlias($path)
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