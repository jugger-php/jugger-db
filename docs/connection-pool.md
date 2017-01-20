# Connection pool

Пул предназначен для работы с множеством соединений с базой данных. Множественность позволяет использовать `read-only` или `write-only` пользователей/базы данных, обеспечить большую безопасность и распределенность данных.

## Создание пула

Создание пуля выглядит так:
```php

use jugger\db\ConnectionPool;

$pool = new ConnectionPool([
    'connection1' => [
        'class' => 'jugger\db\driver\PdoConnection',
        'dsn' => 'sqlite::memory:',
    ],
    'connection2' => [
        'class' => 'jugger\db\driver\PdoConnection',
        'dsn' => 'mysql:localhost;dbname=test',
        'username' => 'root',
        'password' => '',
    ],
]);
```

Каждый элемент массива представляет из себя название класса и список свойств для создания соединения:
```php
/*
'connection' => [
    'class' => 'jugger\db\driver\PdoConnection',
    'dsn' => 'mysql:localhost;dbname=test',
    'username' => 'root',
    'password' => '',
],
 */
$connection = new \jugger\db\driver\PdoConnection();
$connection->dsn = 'mysql:localhost;dbname=test';
$connection->username = 'root';
$connection->password = '';
```

## Доступ к элементам

Получить конкретное соединение можно 2-мя способами:
```php
$obj1 = $pool['connection1'];
$obj2 = $pool->connection2;

if (is_null($pool->connection3)) {
    // при вызове несуществующих подключений, вернется NULL
}
```
