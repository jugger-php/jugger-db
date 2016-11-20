# Data Base

## Connection Pool

Для работы с базой данных используется пул соединений, который обеспечивает возможность создания нескольких соединений, для конкретных задач.

Создания пула необходимо выполнить следующий код:
```
use jugger\db\ConnectionPool;

ConnectionPool::getInstance()->init([
    'default' => [
        'class' => 'jugger\db\pdo\PdoConnection',
        'host' => 'localhost',
        'username' => 'root',
        'password' => 'root',
        'database' => 'root',
    ],
    'имя соединения' => [
        'class' => 'имя класса',
        // параметры класса
    ],
]);
```

## Connection

Каждый класс соединения, должен реализовывать интерфейс `ConnectionInterface`, содержащий методы:
```
// получить объект Connection
$conn = ConnectionPool::get('default');

// запросы
$conn->query('SQL'); // выполнить запрос SELECT и получить результаты в виде `QueryResult`
$conn->execute('SQL'); // выполнить запрос INSERT, UPDATE, DELETE и получить количество затронутых строк

// подготовка запроса
$conn->quote('table or column name');       // добавляет кавычки (в зависимости от СУБД это могут быть разные символы. Например, в MySQL это " ` ", в SQLite это " ' ")
$conn->escape('string with SQL injection'); // обрабатывает строку для защиты от SQL инъекий

```

## Query Builder

Для удобства и большей безопасности можно использовать построитель запросов - `Query`.
Данный класс позволяет вам писать SQL запросы, используя сущности PHP:
```
// объект запроса
$query = new Query();

// доступны 2 варианта работы с объектом:
// в объектном стиле
$query->select('...');
$query->from('...');
$query->where('...');

// в функциональном стиле
$query->select('...')
    ->from('...')
    ->where('...');

//
// Допустимые конструкции
//

// select - по умолчанию равен '*'
$query->select('id, name');
$query->select(['id', 'name']);

// from
$query->from('table');
$query->from([
    'table1',
    'table2' => 't2', // эквивалент: `table2` AS `t2`
]);

// также можно обращаться к псевдонимам таблиц в блоке 'select'
$query->select(['t2.id', 't1.name'])
    ->from([
        'table1' => 't1',
        'table2' => 't2',
    ]);

// join
$query->join('LEFT', 'table3', 'table1.id = table3.id_t1');

// также для удобства, можно вызывать метод с нужным типов связки
$query->leftJoin('table3', 'table1.id = table3.id_t1');
$query->rightJoin('table4', 'table1.id = table4.id_t1');

// также можно использовать конструкцию 'AS' для связной таблицы
$query->innerJoin(
    ['table3' => 't3'],
    'table1.id = t3.id_t1'
);

// where
$query->where('t1.id = t2.id_t2');

// при указании условий в виде массива,
// тип операции указывается перед название столбца
$query->where([
    // EQUAL
    // '={column}' => {value}
    // по умолчанию '=' можно не указывать
    'id' => '123',  // id = 123
    'id' => null,   // id IS NULL
    'id' => true,   // id IS TRUE
    // при указании массива, запись воспринимается как оператор IN
    'id' => [1,2,3],    // id IN (1,2,3)

    // NOT
    // '!{column}' => {value}
    // '!={column}' => {value}
    // '<>{column}' => {value}
    // все записи эквиваленты, для удобства используем '!'
    '!id' => '123',  // id <> 123
    '!id' => null,   // id IS NOT NULL
    '!id' => true,   // id IS NOT TRUE
    '!id' => [1,2,3],    // id NOT IN (1,2,3)

    // IN
    // '@{column}' => {value}
    '@id' => [1,2,3],   // `id` IN (1,2,3)
    // `id` IN (SELECT `id` FROM `table`) - оба запроса ниже
    '@id' => (new Query())->select('id')->from('table'),
    '@id' => 'SELECT `id` FROM `table`',

    // NOT IN
    // '!@{column}' => {value}
    '!@id' => [1,2,3], // `id` NOT IN (1,2,3)
    // `id` NOT IN (SELECT `id` FROM `table`) - оба запроса ниже
    '!@id' => (new Query())->select('id')->from('table'),
    '!@id' => 'SELECT `id` FROM `table`',

    // BETWEEN
    // '><{column}' => {value}
    // не путать с оператором NOT '<>'
    '><id' => [1,100], // `id` BETWEEN 1 AND 100

    // NOT BETWEEN
    // '>!<{column}' => {value}
    '>!<id' => [1,100], // `id` NOT BETWEEN 1 AND 100

    // LIKE
    // '%{column}' => {value}
    '%id' => 'str',     // `id` LIKE 'str'
    '%id' => '%str',    // `id` LIKE '%str'
    '%id' => 'str%',    // `id` LIKE 'str%'
    '%id' => '%str%',   // `id` LIKE '%str%'

    // NOT LIKE
    // '!%{column}' => {value}
    '!%id' => '%str%',     // `id` NOT LIKE '%str%'

    // COMPARE
    // обратите внимание на то, как формируется условие:
    // '>id' => 123 - эквивалент 'id > 123', а не '123 > id'
    '>id' => '123',     // `id` > 123
    '>=id' => '123',    // `id` >= 123
    '<id' => '123',     // `id` < 123
    '<=id' => '123',    // `id` <= 123
]);

// также можно указывать вложенные условия и указывать логику сравнения
// вложенность не ограничена
$query->where([
    'and',
    [
        'or',
        'id' => 123
        'id' => 456
    ],
    [
        'or',
        'id' => 789
        'id' => 012
    ],
    [
        'or',
        'id' => 345
        [
            'and',
            'id' => 678,
            '!name' => null,
            [
                // ...
            ],
        ]
    ],
]);

// условия можно указать вызвав соответсутющий метод
$query->where([
        'id' => 123
    ])
    ->andWhere([
        'id' => 456
    ])
    ->orWhere([
        'id' => 789
    ]);

// на выходе будет условие:
// WHERE
// (
//    (`id` => 123)
//    AND
//    (`id` => 456)
// )
// OR
// (`id` => 789)

// group by
$query->groupBy('id, name');
$query->groupBy(['id', 'name']);

// order by
$query->orderBy('id ASC, name DESC');
$query->orderBy([
    'id' => 'ASC',
    'name' => 'DESC',
]);

// having
$query->having('COUNT(*) > 3'); // указывается только строкой

// limit
$query->limit(10);      // LIMIT 10
$query->limit(10, 100); // LIMIT 100, 10 - в данном случае '100' это отступ `offset`

// insert
Query::insert('tableName', [
    'column1' => 'value1',
    'column2' => 'value2',
    'column3' => 'value3',
]);

// update
Query::update(
    'tableName',
    [
        'column1' => 'new-value1',
        'column2' => 'new-value2',
        'column3' => 'new-value3',
    ],
    [
        'id' => 123, // условие формируется также как и WHERE
    ]
);

// delete
Query::delete('tableName', [
    'id' => 123, // условие формируется также как и WHERE
]);
```

После того как запрос сформирован, его необходимо выполнить:
```
// получаем объект QueryResult
$query->query();

// получаем первую запись запроса
$query->one();

// получаем все записи запроса
$query->all();

// получаем исходный SQL
$query->build();
```

## Query Result

Для обработки результатов запроса, используется класс `QueryResult`.
Реализует данный класс всего 2 метода:
```
// получаем объект результата запроса
$result = $query->query();

// получить одну запись (ассоциативный массив)
$result->fetch();

// получить все записи
$result->fetchAll();

// последняя запись эквивалентна
$rows = [];
while($row = $result->fetch()) {
    $rows[] = $row;
}
```
