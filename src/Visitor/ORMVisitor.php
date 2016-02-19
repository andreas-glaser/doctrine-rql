<?php

namespace AndreasGlaser\DoctrineRql\Visitor;

use AndreasGlaser\Helpers\ArrayHelper;
use AndreasGlaser\Helpers\StringHelper;
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
     * @var QueryBuilder
     */
    protected $qb;

    /**
     * @var string
     */
    protected $autoRootAlias;

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param \Xiag\Rql\Parser\Query     $query
     * @param bool                       $autoRootAlias
     *
     * @throws \AndreasGlaser\DoctrineRql\Visitor\VisitorException
     * @author Andreas Glaser
     */
    public function append(QueryBuilder $qb, RqlQuery $query, $autoRootAlias = true)
    {
        $this->qb = $qb;

        if ($autoRootAlias) {
            $this->autoRootAlias = ArrayHelper::getFirstIndex($this->qb->getRootAliases());
        }

        $this->visitQuery($query);
    }

    /**
     * @param \Xiag\Rql\Parser\Node\AbstractQueryNode $node
     *
     * @return \Doctrine\ORM\Query\Expr|\Doctrine\ORM\Query\Expr\Andx|\Doctrine\ORM\Query\Expr\Orx
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
     * @param RqlQuery $query top level query that needs visiting
     *
     * @return void
     */
    protected function visitQuery(RqlQuery $query)
    {
        if ($selectNode = $query->getSelect()) {
            // todo: Implement this
        }

        if ($abstractQueryNode = $query->getQuery()) {
            $this->qb->andWhere(
                $this->walkNodes($abstractQueryNode)
            );
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

        $field = $node->getField();

        if ($this->autoRootAlias && !StringHelper::contains($field, '.')) {
            $field = $this->autoRootAlias . '.' . $field;
        }

        $exp = $this->qb->expr()->$method($field, $parameterName);
        $this->qb->setParameter($parameterName, $node->getValue());

        return $exp;
    }

    /**
     * @param \Xiag\Rql\Parser\Node\Query\AbstractArrayOperatorNode $node
     *
     * @return Expr
     * @throws \AndreasGlaser\DoctrineRql\Visitor\VisitorException
     * @author Andreas Glaser
     */
    protected function visitArray(AbstractArrayOperatorNode $node)
    {
        if (!$method = ArrayHelper::get($this->arrayMap, get_class($node))) {
            throw new VisitorException('Unsupported');
        }

        $field = $node->getField();

        if ($this->autoRootAlias && !StringHelper::contains($field, '.')) {
            $field = $this->autoRootAlias . '.' . $field;
        }

        $exp = $this->qb->expr()->$method($field, $node->getValues());

        return $exp;
    }

    /**
     * @param \Xiag\Rql\Parser\Node\Query\AbstractLogicOperatorNode $node
     *
     * @return \Doctrine\ORM\Query\Expr\Andx|\Doctrine\ORM\Query\Expr\Orx
     * @throws \AndreasGlaser\DoctrineRql\Visitor\VisitorException
     * @author Andreas Glaser
     */
    protected function visitLogic(AbstractLogicOperatorNode $node)
    {
        if ($node instanceof Node\Query\LogicOperator\AndNode) {
            $expr = new Expr\Andx();
        } elseif ($node instanceof Node\Query\LogicOperator\OrNode) {
            $expr = new Expr\Orx();
        } elseif ($node instanceof Node\Query\LogicOperator\NotNode) {
            $expr = new Expr\Andx();
        } else {
            throw new VisitorException('Not supported');
        }

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

            if ($this->autoRootAlias && !StringHelper::contains($field, '.')) {
                $field = $this->autoRootAlias . '.' . $field;
            }

            $this->qb->orderBy($field, $order === 1 ? 'ASC' : 'DESC');
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
}