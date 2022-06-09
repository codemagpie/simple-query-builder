# Simple Query Builder
## Introduction
A simple and general query builder,You can use this builder is transformed into other query-builder, such as sql, mongodb, elasticsearch...
## Installation
```shell
composer require codemagpie/simple-query-builder
```
## Usage
Only need to extend the class \CodeMagpie\SimpleQueryBuilder\AbstractSimpleQuery :
```php
use CodeMagpie\SimpleQueryBuilder\AbstractSimpleQuery;

class UserQuery extends AbstractSimpleQuery
{
    /**
     * @var string[] allow the query field, ['*'] is allow all
     */
    protected array $fields = ['id', 'name', 'age'];
}
```
To use:
```php

 $query = UserQuery::build()
            ->whereIn('id', [1, 2])
            ->whereEqual('name', 'test')
            ->addNestedOrWhere(function ($query) {
                $query->whereGreat('age', 10)->orWhereLess('age', 8);
            })
            ->orderByDesc('id')
            ->forPage(1, 10);
// This is similar to "where id in (1, 2) and name = 'test' or (age > 10 or age < 8) order by id desc limit 10"
```
## Supports
1. support convert [ruflin/elastica](https://packagist.org/packages/ruflin/elastica) query, use:
```php
$query = UserQuery::build()->whereEqual('name', 'test')->bindElasticaQueryBuilder(new \Elastica\Query());
```
2. support convert [hyperf/database](https://packagist.org/packages/hyperf/database) query, use:
```php
$builder =  Model::query(); // hyperf Model, such as UserModel
$query = UserQuery::build()->whereEqual('name', 'test')->bindHyperfQueryBuilder($builder)
```