#doctrine-rql
Parses and applies RQL scripts to doctrine query builders.

##Installation
```shell
composer require andreas-glaser/doctrine-rql
```

##Usage
```php
<?php

namespace TestApp;

use AndreasGlaser\DoctrineRql\Factory\ORMVisitorFactory;

$rqlString = $_GET['rql']; // ?rql=and(eq(cart.id,123),eq(cart.archived,0))

/** @var QueryBuilder $qb */
$qb = $entityManager->createQueryBuilder();

$qb
    ->select('cart')
    ->from('TestApp\Entity\Cart', 'cart');

// append filters
ORMVisitorFactory::appendFiltersOnly($queryBuilder, $rqlString, false);

/** @var TestApp\Entity\Cart[] $cartEntities */
$cartEntities = $qb
    ->getQuery()
    ->execute();
```