# Query

Для удобства создания и выполнения запросов, можно использовать класс `Query`. Данный класс реализует все SQL конструкции в качестве методов класса.

## Создание объекта

Для создания объекта запроса, необходимо в консутрктор передать объект соединения `ConnectionInterface`.

```php
use jugger\db\Query;
use jugger\db\driver\MysqliConnection;

$db = new MysqliConnection();
$q = new Query($db);
```

Вызывать методы можно двумя способами:

```php
// 'обычный' способ
$q = new Query($db);
$q->select('*');
$q->from('tableName');

// 'цепной' способ
$q = (new Query($db))
    ->select('*')
    ->from('tableName');
```

Вызывать методы можно в любом порядке, **но лучше писать в порядке следования в SQL запросе**, построение запроса происходит в момент вызова методов `build`, `one`, `all`:

```php
$q = (new Query($db))
    ->from('t')
    ->orderBy([
        'id' => 'ASC'
    ])
    ->select('*');

// возвращает конечный SQL запрос
$sql = $q->build();

// возвращает первую подходящую запись
$row = $q->one();
$row = $db->query($sql)->fetch();

// возвращает все подходящие записи
$rows = $q->all();
$rows = $db->query($sql)->fetchAll();
```

## SELECT

```php
// SELECT * FROM ...
$q->select("*");

// SELECT col1, col2 FROM ...
$q->select("col1, col2");

// SELECT `col1`, `col2` FROM ...
$q->select([
    "col1", "col2"
]);

// SELECT `col1` AS `c1` FROM ...
$q->select([
    "c1" => "col1"
]);

// SELECT (SELECT * FROM t2) AS `c1` FROM ...
$q->select([
    "c1" => (new Query($db))->from('t2')
]);
```

## FROM

```php
// SELECT * FROM t1
$q->from("t1");

// SELECT * FROM `t1`, `t2`
$q->from(["t1", "t2"]);

// SELECT * FROM `table1` AS `t1`
$q->from([
    "t1" => "table1"
]);

// SELECT * FROM (SELECT * FROM t2) AS `t1`
$q->from([
    "t1" => (new Query($db))->from('t2')
]);
```

Повторное использование метода, переписывает предыдущее значение:
```php
$q->from('t1');
$q->from('t2');

// FROM t2
$q->build();
```

## JOIN

В общем виде конструкция `JOIN` выглядит так:
```php
// join($type, $tableName, $on)
$q->join('INNER', 't2', 't1.id_t2 = t2.id');
```

Для удобства, типы связи вынесены в отдельные методы:
```php
// $q->join('LEFT', 't2', '...');
$q->leftJoin('t2', '...');

// $q->join('RIGHT', 't2', '...');
$q->rightJoin('t2', '...');

// $q->join('INNER', 't2', '...');
$q->innerJoin('t2', '...');
```

Переменная `tableName` может принимать значения аналогичные, как для оператора `FROM`:
```php
// INNER JOIN table ON ...
$q->innerJoin('table', '...');

// INNER JOIN `table` ON ...
$q->innerJoin(['table'], '...');

// INNER JOIN `table` AS `t1` ON ...
$q->innerJoin([
    't1' => 'table'
], '...');

// INNER JOIN (SELECT * FROM table) AS `t1` ON ...
$q->innerJoin([
    't1' => (new Query($db))->from('table')
], '...');
```

Допускается использовать одновременно несколько join'ов:
```php
$q = (new Query($db))->from('t');
$q->innerJoin('t2', 't.id = t2.tid');
$q->innerJoin('t3', 't.id = t3.tid');
$q->innerJoin('t4', 't.id = t4.tid');

// SELECT *
// FROM t
// INNER JOIN t2 ON t.id = t2.tid
// INNER JOIN t3 ON t.id = t3.tid
// INNER JOIN t4 ON t.id = t4.tid
$q->build();
```

## WHERE

Для указания типа операции, необходимо указать соответсвующий перфикс в имени столбца. В общем виде конструкция следующая `'{оператор}{столбец}' => {значение}`:
```php
// WHERE `id` = 1
$q->where([
    '=id' => 1
]);

// WHERE `id` IN (1,2,3)
$q->where([
    '@id' => [1,2,3]
]);
```

Если функционала класса `Query` не хватает, вы можете написать условия строкой в свободной форме:
```php
$q->where("id = 1 AND column <> 123");
```

Cписок операторов:
- `=` - равенство
- `@` - оператор `IN`
- `%` - оператор `LIKE`
- `><` - оператор `BETWEEN`
- `>` - больше
- `<` - меньше
- `>=` - больше либо равно
- `<=` - меньше либо равно

Операторы отрицания:
- `!` - не равно
- `!=` - не равно (эквивалент)
- `!@` - оператор `NOT IN`
- `!%` - оператор `NOT LIKE`
- `>!<` - оператор `NOT BETWEEN`

Логические операторы:
```php
// WHERE ((`col1` = '123' AND `col2` = '123') OR (`col3` = '123'))
$q->where([
    'or',
    [
        'and',
        'col1' => 123,
        'col2' => 123,
    ],
    [
        'col3' => 123,
    ],
]);
```

Для удобства можно использовать соответсвующие методы:
```php
// WHERE (((`col1` = '123')) AND (`col2` = '123')) OR (`col3` = '123')
$q->where(['col1' => 123]);
$q->andWhere(['col2' => 123]);
$q->orWhere(['col3' => 123]);
```

### Равенство

Эквивалентные операторы:
- ` ` - при отсутствии оператора, по умолчанию используется оператор `=`
- `=`

Примеры значений:
```php
// WHERE `col` IS  NULL
$q->where([
    'col' => null,
]);

// WHERE `col` IS  TRUE
$q->where([
    'col' => true,
]);

// WHERE `col` = '123'
$q->where([
    'col' => 123,
]);

// WHERE `col` IN ('1', 'test', '3.14')
$q->where([
    'col' => [1,'test',3.14],
]);

// WHERE `col` IN (SELECT * FROM t2)
$q->where([
    'col' => (new Query($db))->from('t2'),
]);
```

### Неравенство

Эквивалентные операторы:
- `!`
- `!=`
- `<>`

Примеры значений:
```php
// WHERE `col` <> '123'
$q->where([
    '!col' => 123,
]);

// WHERE `col` IS NOT NULL
$q->where([
    '!=col' => null,
]);

// WHERE `col` NOT IN ('1', 'test', '3.14')
$q->where([
    '<>col' => [1, 'test', 3.14],
]);
```

### IN

Эквивалентные операторы:
- ` ` - при значение равном `array` или `Query`
- `=` - при значение равном `array` или `Query`
- `@`

Примеры значений:
```php
// WHERE `col` IN (1,2,3)
$q->where([
    'col' => [1,2,3]
]);

// WHERE `col` IN (4,5,6)
$q->where([
    '=col' => [4,5,6]
]);

// WHERE `col` IN (SELECT * FROM t2)
$q->where([
    '@col' => (new Query($db))->from('t2')
]);
```

### NOT IN

Эквивалентные операторы:
- `!` - при значение равном `array` или `Query`
- `!=` - при значение равном `array` или `Query`
- `!@`

Примеры значений:
```php
// WHERE `col` NOT IN (1,2,3)
$q->where([
    '!col' => [1,2,3]
]);

// WHERE `col` NOT IN (4,5,6)
$q->where([
    '!=col' => [4,5,6]
]);

// WHERE `col` NOT IN (SELECT * FROM t2)
$q->where([
    '!@col' => (new Query())->from('t2')
]);
```

### BETWEEN

Эквивалентные операторы:
- `><`

Примеры значений:
```php
// WHERE `col1` BETWEEN 1 AND 50
$q->where([
    '><col1' => [1, 50],
]);
```

### NOT BETWEEN

Эквивалентные операторы:
- `>!<`

Примеры значений:
```php
// WHERE `col1` NOT BETWEEN 1 AND 50
$q->where([
    '>!<col1' => [1, 50],
]);
```

### LIKE

Эквивалентные операторы:
- `%`

Примеры значений:
```php
// WHERE `col` LIKE 'str'
$q->where([
    '%col' => "str",
]);

// WHERE `col` LIKE '%str'
$q->where([
    '%col' => "%str",
]);

// WHERE `col` LIKE 'str%'
$q->where([
    '%col' => "str%",
]);

// WHERE `col` LIKE '%str%'
$q->where([
    '%col' => "%str%",
]);

// WHERE `col` LIKE (SELECT * FROM t)
$q->where([
    '%col' => (new Query($db))->from('t'),
]);
```

### NOT LIKE

Эквивалентные операторы:
- `!%`

Примеры значений:
```php
// WHERE `col` NOT LIKE 'str'
$q->where([
    '!%col' => "str",
]);

// WHERE `col` NOT LIKE '%str'
$q->where([
    '!%col' => "%str",
]);

// WHERE `col` NOT LIKE 'str%'
$q->where([
    '!%col' => "str%",
]);

// WHERE `col` NOT LIKE '%str%'
$q->where([
    '!%col' => "%str%",
]);

// WHERE `col` NOT LIKE (SELECT * FROM t)
$q->where([
    '!%col' => (new Query($db))->from('t'),
]);
```

### COMPARE

Примеры операторов сравнения:
```php
// WHERE `col` > 1
$q->where([
    '>col' => 1,
]);

// WHERE `col` >= 2
$q->where([
    '>=col' => 2,
]);

// WHERE `col` < 3
$q->where([
    '<col' => 3,
]);

// WHERE `col` <= 4
$q->where([
    '<=col' => 4,
]);
```

## GROUP BY

```php
// GROUP BY col1, col2, col3
$q->groupBy("col1, col2, col3");

// GROUP BY `col1`, `col2`, `col3`
$q->groupBy(["col1", "col2", "col3"]);
```

## HAVING

```php
// HAVING COUNT(*) > 123
$q->having("COUNT(*) > 123");
```

## ORDER BY

```php
// ORDER BY id ASC, name DESC",
$q->orderBy("id ASC, name DESC");

// ORDER BY RAND()",
$q->orderBy("RAND()");

// ORDER BY  `id` ASC,  `name` DESC",
$q->orderBy([
    'id' => 'ASC',
    'name' => 'DESC',
]);
```

## INSERT / UPDATE / DELETE

Для создания `INSERT`, `UPDATE`, `DELETE` запросов используется класс `Command`. [Подробнее про `Command`](command.md).
