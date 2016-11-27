# Query builder

Для удобства создания и выполнения запросов, можно использовать класс `Query`. Данный класс реализует все SQL конструкции в качестве методов, ниже список всевозможных конструкций.

## Входные данные

Во всех примерах ниже, переменная `q = new Query()`.

## SELECT

```php
// по умолчанию, значение равно '*'
// SELECT * FROM t
$q->from('t');

// SELECT col1, column2 AS col2 ...
$q->select('col1, column2 AS col2')

// SELECT `col1`, `col2`
$q->select(["col1", "col2"]);

// SELECT `col1` AS `c1`
$q->select(["c1" => "col1"]);

// SELECT (SELECT * FROM t2) AS `c1`
$q->select([
    "c1" => (new Query())->from('t2')
]);
```

## FROM

```php
```

## JOIN

```php
```

## WHERE

```php
```

## GROUP BY

```php
```

## HAVING

```php
```

## ORDER BY

```php
```

## INSERT

```php
```

## UPDATE

```php
```

## DELETE

```php
```
