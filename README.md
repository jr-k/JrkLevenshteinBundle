Getting started with JrkLevenshteinBundle
======================================

Setup
-----
JrkLevenshteinBundle requires Symfony and Doctrine


- Using composer

Add jrk/levenshtein-bundle as a dependency in your project's composer.json file:

```
{
    "require": {
        "jrk/levenshtein-bundle": "dev-master"
    }
}
```
Update composer
```
php composer update
or 
php composer.phar update
```

- Add JrkLevenshteinBundle to your application kernel

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Jrk\LevenshteinBundle\JrkLevenshteinBundle(),
    );
}
```


- Yml configuration

``` yml
# app/config/config.yml
doctrine:
    orm:
        dql:
            numeric_functions:
                levenshtein: Jrk\LevenshteinBundle\ORM\Doctrine\DQL\LevenshteinFunction
                levenshtein_ratio: Jrk\LevenshteinBundle\ORM\Doctrine\DQL\LevenshteinRatioFunction
```

- Console usage 

> Install functions
``` 
php app/console jrk:levenshtein:install
```

Usage
-----


 - Using QueryBuilder

``` php
<?php
    public function getUserByFirstname($tolerance = 3) {
        $queryBuilder = $this->_em->createQueryBuilder()
           ->select('user')
           ->from('FooBundle:User','user')
           ->where('LEVENSHTEIN(user.firstname,:searchString) <= :tolerance')
           ->setParameter('searchString',$searchString)
           ->setParameter('tolerance',$tolerance)
        ;

        return $queryBuilder->getQuery()->getResult();
    }
?>
```

 - Using DQL

``` php
<?php
    public function getUserByFirstname($tolerance = 3) {

        $dqlString = '
            SELECT user
            FROM FooBundle:User user
            WHERE LEVENSHTEIN(user.firstname,:searchString) <= :tolerance)
        ';

        $query = $this->_em->createQuery($dqlString)
           ->setParameter('searchString',$searchString)
           ->setParameter('tolerance',$tolerance)
        ;

        return $query->getResult();
    }
?>
```
