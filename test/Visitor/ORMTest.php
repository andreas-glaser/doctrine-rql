<?php

namespace AndreasGlaser\DoctrineRql\Visitor;

use AndreasGlaser\DoctrineRql\Fixtures;
use AndreasGlaser\DoctrineRql\Helper;
use AndreasGlaser\DoctrineRql\Visitor as Visitor;
use Doctrine\Common\DataFixtures;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM;
use Doctrine\ORM\EntityManager;

/**
 * Class ORMVisitor
 *
 * @package AndreasGlaser\DoctrineRql
 * @author  Andreas Glaser
 */
class ORMTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\Tools\ToolsException
     * @author Andreas Glaser
     */
    protected function setUp()
    {
        $conn = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'dbname' => ':memory:',
            'memory' => true,
        ]);

        $config = ORM\Tools\Setup::createAnnotationMetadataConfiguration([__DIR__ . '/../Entity'], true, null, null, false);
        $this->em = EntityManager::create($conn, $config);

        $schemaTools = new ORM\Tools\SchemaTool($this->em);
        $schemaTools->createSchema($this->em->getMetadataFactory()->getAllMetadata());

        $loader = new DataFixtures\Loader();
        $loader->addFixture(new Fixtures\ORM());

        $executor = new DataFixtures\Executor\ORMExecutor($this->em, new DataFixtures\Purger\ORMPurger());
        $executor->execute($loader->getFixtures());
    }

    public function test()
    {
        $rql = 'eq(name,123)sort(+name)'; // user input

        $rqlQuery = Helper\Parser::parseFilterOnly($rql);

        $qb = $this->em->createQueryBuilder();

        $qb
            ->select('product')
            ->from('AndreasGlaser\DoctrineRql\Entity\Product', 'product');

        $ormVisitor = new Visitor\ORMVisitor();
        $ormVisitor->append($qb, $rqlQuery);

        print_r($qb->getQuery()->getSQL());
        exit;
    }
}