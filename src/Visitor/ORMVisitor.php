<?php

namespace AndreasGlaser\DoctrineRql\Visitor;

use AndreasGlaser\Helpers\ArrayHelper;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Xiag\Rql\Parser\Node;
use Xiag\Rql\Parser\Node\AbstractQueryNode;
use Xiag\Rql\Parser\Node\Query\AbstractArrayOperatorNode;
use Xiag\Rql\Parser\Node\Query\AbstractLogicOperatorNode;
use Xiag\Rql\Parser\Node\Query\AbstractScalarOperatorNode;
use Xiag\Rql\Parser\Query as RqlQuery;

/**
 * Class ORMVisitor
 *
 * @package AndreasGlaser\DoctrineRql\Visitor
 * @author  Andreas Glaser
 */
class ORMVisitor
{
    /**
     * @var array
     */
    protected $scalarMap = [
        'Xiag\Rql\Parser\Node\Query\ScalarOperator\EqNode'   => 'eq',
        'Xiag\Rql\Parser\Node\Query\ScalarOperator\NeNode'   => 'neq',
        'Xiag\Rql\Parser\Node\Query\ScalarOperator\LtNode'   => 'lt',
        'Xiag\Rql\Parser\Node\Query\ScalarOperator\GtNode'   => 'gt',
        'Xiag\Rql\Parser\Node\Query\ScalarOperator\LeNode'   => 'lte',
        'Xiag\Rql\Parser\Node\Query\ScalarOperator\GeNode'   => 'gte',
        'Xiag\Rql\Parser\Node\Query\ScalarOperator\LikeNode' => 'like',
    ];

    /**
     * @var array
     */
    protected $arrayMap = [
        'Xiag\Rql\Parser\Node\Query\ArrayOperator\InNode'  => 'in',
        'Xiag\Rql\Parser\Node\Query\ArrayOperator\OutNode' => 'notIn',
    ];

    /**
     * @var array
     */
    protected $logicMap = [
        'Xiag\Rql\Parser\Node\Query\LogicOperator\AndNode' => '\Doctrine\ORM\Query\Expr\Andx',
        'Xiag\Rql\Parser\Node\Query\LogicOperator\OrNode'  => '\Doctrine\ORM\Query\Expr\Orx',
        'Xiag\Rql\Parser\Node\Query\LogicOperator\NotNode' => '\Doctrine\ORM\Query\Expr\Not',
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
     * @param \Xiag\Rql\Parser\Query     $query
     * @param bool                       $autoRootAlias
     *
     * @author Andreas Glaser
     */
    public function append(QueryBuilder $qb, RqlQuery $query, $autoRootAlias = true)
    {
        $this->qb = $qb;

        if ($autoRootAlias) {
            $this->autoRootAlias = ArrayHelper::getFirstIndex($this->qb->getRootAliases());
        } else {
            $this->autoRootAlias = null;
        }

        $this->buildPathToAliasMap($qb);
        $this->visitQuery($query);
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb
     *
     * @author Andreas Glaser
     */
    protected function buildPathToAliasMap(QueryBuilder $qb)
    {
        $this->aliasMap = []; // reset

        $rootAlias = ArrayHelper::getFirstIndex($this->qb->getRootAliases());
        $this->aliasMap[$rootAlias] = $rootAlias;

        /** @var Expr\Join $part */
        foreach ($qb->getDQLParts()['join'][$rootAlias] AS $part) {
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

    /**
     * @param \Xiag\Rql\Parser\Node\AbstractQueryNode $node
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
        } elseif ($node instanceof AbstractLogicOperatorNode) {
            return $this->visitLogic($node);
        } else {
            throw new VisitorException('Not supported');
        }
    }

    /**
     * @param \Xiag\Rql\Parser\Query $query
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
     * @param \Xiag\Rql\Parser\Node\Query\AbstractScalarOperatorNode $node
     *
     * @return mixed
     * @throws \AndreasGlaser\DoctrineRql\Visitor\VisitorException
     * @author Andreas Glaser
     */
    protected function visitScalar(AbstractScalarOperatorNode $node)
    {
        if (!$method = ArrayHelper::get($this->scalarMap, get_class($node))) {
            throw new VisitorException('Unsupported');
        }

        $parameterName = ':param_' . uniqid();
        $pathToField = $node->getField();
        $exp = $this->qb->expr()->$method($this->pathToAlias($pathToField), $parameterName);
        $this->qb->setParameter($parameterName, $node->getValue());

        return $exp;
    }

    /**
     * @param \Xiag\Rql\Parser\Node\Query\AbstractArrayOperatorNode $node
     *
     * @return mixed
     * @throws \AndreasGlaser\DoctrineRql\Visitor\VisitorException
     * @author Andreas Glaser
     */
    protected function visitArray(AbstractArrayOperatorNode $node)
    {
        if (!$method = ArrayHelper::get($this->arrayMap, get_class($node))) {
            throw new VisitorException('Unsupported');
        }

        $pathToField = $node->getField();
        $exp = $this->qb->expr()->$method($this->pathToAlias($pathToField), $node->getValues());

        return $exp;
    }

    /**
     * @param \Xiag\Rql\Parser\Node\Query\AbstractLogicOperatorNode $node
     *
     * @return mixed
     * @throws \AndreasGlaser\DoctrineRql\Visitor\VisitorException
     * @author Andreas Glaser
     */
    protected function visitLogic(AbstractLogicOperatorNode $node)
    {
        if (!$class = ArrayHelper::get($this->logicMap, get_class($node))) {
            throw new VisitorException('Unsupported');
        }

        $expr = new $class();
        foreach ($node->getQueries() as $query) {
            $expr->add($this->walkNodes($query));
        }

        return $expr;
    }

    /**
     * @param \Xiag\Rql\Parser\Node\SortNode $node
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
     * @param \Xiag\Rql\Parser\Node\LimitNode $node
     *
     * @author Andreas Glaser
     */
    protected function visitLimit(Node\LimitNode $node)
    {
        $this->qb
            ->setMaxResults($node->getLimit())
            ->setMaxResults($node->getOffset());
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