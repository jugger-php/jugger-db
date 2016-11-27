# Query builder

Для удобства создания и выполнения запросов, можно использовать класс `Query`. Данный класс реализует все SQL конструкции в качестве методов класса.

## Создание объекта

Во всех примерах ниже, переменная `$q = new Query()`. Вызывать методы можно двумя способами:
```php
// 'обычный' способ
$q = new Query();
$q->select('*');
$q->from('tableName');

// 'цепной' способ
$q = (new Query())
    ->select('*')
    ->from('tableName');
```

Вызывать методы можно в любом порядке, **но лучше писать в порядке следования в SQL запросе**, построение запроса происходит в момент вызова методов `build`, `one`, `all`:

```php
$q = (new Query())
    ->from('t')
    ->orderBy([
        'id' => 'ASC'
    ])
    ->select('*');

// возвращает конечный SQL запрос
$sql = $q->build();

// возвращает первую подходящую запись
$row = $q->one();

// возвращает все подходящие записи
$rows = $q->all();
```

## SELECT

```php
// по умолчанию, значение равно '*'
// SELECT * FROM t
$q->from('t');

// SELECT col1, column2 AS col2
$q->select('col1, column2 AS col2')

// при использовании массива, названия столбцов оборачиваются в quote's
// эквивалентно вызову $connection::quote('col1')
// SELECT `col1`, `col2`
$q->select(["col1", "col2"]);

// SELECT `col1` AS `c1`
$q->select(["c1" => "col1"]);

// SELECT (SELECT * FROM t2) AS `c1`
$q->select([
    "c1" => (new Query())->from('t2')
]);
```

Повторное использование метода, переписывает предыдущее значение:
```php
$q->select('col1');
$q->select('col2');

// SELECT col2
$q->build();
```

## FROM

Синтаксис идентичен `SELECT`.

```php
// FROM t1
$q->from("t1");

// FROM `t1`, `t2`
$q->from(["t1", "t2"]);

// FROM `table1` AS `t1`
$q->from(["t1" => "table1"]);

// FROM (SELECT * FROM t2) AS `t1`
$q->from([
    "t1" => (new Query())->from('t2')
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
// join($type, $tableName, $onClause)
$q->from('t1')->join('INNER', 't2', 't1.id_t2 = t2.id');
```

Для удобства, типы связи вынесены в отдельные методы:
```php
$q->leftJoin('t2', '...'); // $q->join('LEFT', 't2', '...');
$q->rightJoin('t2', '...'); // $q->join('RIGHT', 't2', '...');
$q->innerJoin('t2', '...'); // $q->join('INNER', 't2', '...');
```

Переменная `$tableName` может принимать значения аналогичные для оператора `FROM`:
```php
// INNER JOIN table ON ...
$q->innerJoin('table', '...');

// INNER JOIN `table` ON ...
$q->innerJoin(['table'], '...');

// INNER JOIN `table` AS `t1` ON ...
$q->innerJoin(['t1' => 'table'], '...');

// INNER JOIN (SELECT * FROM table) AS `t1` ON ...
$table = [
    't1' => (new Query())->from('table')
];
$q->innerJoin($table, '...');
```

Допускается использовать одновременно несколько join'ов:
```php
$q = (new Query())->from('t');
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

Для указания типа операции, необходимо указать соответсвующий перфикс в имени столбца.
```php
// в общем виде конструкция следующая:
// '{оператор}{столбец}' => {значение}

// WHERE `id` = 1
$q->where(['=id' => 1]);

// WHERE `id` IN (1,2,3)
$q->where([
    '@id' => [1,2,3]
]);
```

Если функционала класса `Query` не хватает, вы можете написать условия строкой в свободной форме:
```php
$q->where("id = 1 AND column <> 123");
```

Полный список операторов:
- `=` - равенство
- `@` - оператор `IN`
- `><` - оператор `BETWEEN`
- `%` - оператор `LIKE`

Операторы сравнения:
- `>` - больше
- `<` - меньше
- `>=` - больше либо равно
- `<=` - меньше либо равно

Обратите внимание на **смысл** операторов сравнения:
```php
// Правильно: WHERE `id` > 100
// Неправильно: WHERE 100 > `id`
$q->where(['>id' => 100]);
```

И операторы отрицания:
- `!` - не равно
- `!=` - не равно (эквивалент)
- `!@` - оператор `NOT IN`
- `!%` - оператор `NOT LIKE`
- `>!<` - оператор `NOT BETWEEN`

Также можно указывать логические операторы:
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
$q->where(['col1' => 123]);
$q->andWhere(['col2' => 123]);
$q->orWhere(['col3' => 123]);

//  WHERE
//  (
//      (`col1` = '123') AND (`col2` = '123')
//  )
//  OR
//  (`col3` = '123')
$q->build();
```

Ниже представлен список всевозможных операторов.

### Равенство

Эквивалентные операторы:
- ` ` - при отсутствии оператора, по умолчанию используется оператор равенства
- `=`

Примеры значений:
```php
// WHERE `col1` = 123
$q->where(['col1' => 123]);

// WHERE `col2` IS NULL
$q->where(['col2' => null]);

// WHERE `col3` IS TRUE
$q->where(['col3' => true]);

// WHERE `col4` IN (1,2,3)
$q->where([
    'col4' => [1,2,3]
]);

// WHERE `col5` IN (SELECT * FROM t2)
$q->where([
    'col5' => (new Query())->from('t2')
]);
```

### Неравенство

Эквивалентные операторы:
- `!`
- `!=`
- `<>`

Примеры значений:
```php
// WHERE `col1` <> 123
$q->where(['!col1' => 123);

// WHERE `col2` IS NOT NULL
$q->where(['!=col2' => null);

// WHERE `col3` NOT IN (1,2,3)
$q->where(['<>col3' => [1,2,3]);
```

### IN

Эквивалентные операторы:
- ` ` - при значение равном `array` или `Query`
- `=` - при значение равном `array` или `Query`
- `@`

Примеры значений:
```php
// WHERE `col1` IN (1,2,3)
$q->where([
    'col1' => [1,2,3]
]);

// WHERE `col2` IN (4,5,6)
$q->where([
    '=col2' => [4,5,6]
]);

// WHERE `col3` IN (SELECT * FROM t2)
$q->where([
    '@col3' => (new Query())->from('t2')
]);
```

### NOT IN

Эквивалентные операторы:
- `!` - при значение равном `array` или `Query`
- `!=` - при значение равном `array` или `Query`
- `!@`

Примеры значений:
```php
// WHERE `col1` NOT IN (1,2,3)
$q->where([
    '!col1' => [1,2,3]
]);

// WHERE `col2` NOT IN (4,5,6)
$q->where([
    '!=col2' => [4,5,6]
]);

// WHERE `col3` NOT IN (SELECT * FROM t2)
$q->where([
    '!@col3' => (new Query())->from('t2')
]);
```

### BETWEEN

Эквивалентные операторы:
- `><`

Примеры значений:
```php
// WHERE `col1` BETWEEN 1 AND 50
$q->where([
    '><col1' => [1,50],
]);
```

### NOT BETWEEN

Эквивалентные операторы:
- `>!<`

Примеры значений:
```php
// WHERE `col1` NOT BETWEEN 1 AND 50
$q->where([
    '>!<col1' => [1,50],
]);
```

### LIKE

Эквивалентные операторы:
- `%`

Примеры значений:
```php
// WHERE `col1` LIKE 'str'
$q->where(['%col1' => "str"]);

// WHERE `col2` LIKE '%str'
$q->where(['%col2' => "%str"]);

// WHERE `col3` LIKE 'str%'
$q->where(['%col3' => "str%"]);

// WHERE `col4` LIKE '%str%'
$q->where(['%col4' => "%str%"]);

// WHERE `col5` LIKE (SELECT * FROM t)
$q->where([
    '%col5' => (new Query())->from('t')
]);
```

### NOT LIKE

Эквивалентные операторы:
- `!%`

Примеры значений:
```php
// WHERE `col1` NOT LIKE 'str'
$q->where(['!%col1' => "str"]);

// WHERE `col2` NOT LIKE '%str'
$q->where(['!%col2' => "%str"]);

// WHERE `col3` NOT LIKE 'str%'
$q->where(['!%col3' => "str%"]);

// WHERE `col4` NOT LIKE '%str%'
$q->where(['!%col4' => "%str%"]);

// WHERE `col5` NOT LIKE (SELECT * FROM t)
$q->where([
    '!%col5' => (new Query())->from('t')
]);
```

### COMPARE

Примеры операторов сравнения:
```php
// WHERE `col1` > 1
$q->where(['>col1' => 1]);

// WHERE `col2` >= 2
$q->where(['>=col2' => 2]);

// WHERE `col3` < 3
$q->where(['<col3' => 3]);

// WHERE `col4` <= 4
$q->where(['<=col4' => 4]);
```

Обратите внимание на **смысл** операторов сравнения:
```php
// Правильно: WHERE `id` > 100
// Неправильно: WHERE 100 > `id`
$q->where(['>id' => 100]);
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

## INSERT

Для создания `INSERT`, `UPDATE`, `DELETE` запросов используется класс `QueryBuilder`. После вызовов соответствующих методов, возвращает объект `Command`, который содержит сформированный SQL.

```php
$command = QueryBuilder::insert('t1', [
    'name' => 'name_val',
    'content' => 'content_val',
    'update_time' => 1400000000,
]);

// INSERT INTO `t1`(`name`,`content`,`update_time`) VALUES('name_val','content_val','1400000000')
$sql = $command->getSql();

if ($command->execute() == 1) {
    // success
}
else {
    // fail - при неудаче, выброситься исключение на уровне драйвера базы, поэтому данный код не выполнится
}
```

## UPDATE

```php
$values = [
    'name' => 'new name',
    'content' => 'new content',
];
// структура аналогична для метода Query::where
$where = [
    '!id' => null,
];
$command = QueryBuilder::update("t1", $values, $where);

// UPDATE `t1` SET `name` = 'new name', `content` = 'new content'  WHERE `id` IS NOT NULL
$sql = $command->getSql();

// количество измененных строк
$count = $command->execute();
```

## DELETE

```php
// структура аналогична для метода Query::where
$where = ['id' => 1];
$command = QueryBuilder::delete("t1", $where);

// DELETE FROM `t1`  WHERE `id` = 1
$sql = $command->getSql();

// количество удаленных строк
$count = $command->execute();
```
