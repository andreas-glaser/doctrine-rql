# doctrine-rql
Parses and applies RQL/FIQL scripts to doctrine orm query builders.

## Installation
```shell
composer require andreas-glaser/doctrine-rql 0.3.*
```

## Usage Test
```php
<?php

require 'vendor/autoload.php';

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

A full list of available RQL/FIQL operators can be found at:

[https://github.com/libgraviton/rql-parser/#operators](https://github.com/libgraviton/rql-parser/#operators)

### Additional Operators ###

 - NULL Comparison
    - `isnull(fieldName)`
    - `isnotnull(fieldName)`