# Connection

Объект соединения с базой данных, является прослойкой между кодом и базой. Каждый класс соединения реализует интерфейс `jugger\db\ConnectionInterface`, поэтому в своих модулях, вы должны ссылаться именно на этот интерфейс, а не на конкретную реализацию соединения.

Ниже представлен сам интерфейс:
```php
namespace jugger\db;

interface ConnectionInterface
{
    public function query(string $sql): QueryResult;

    public function execute(string $sql): int;

    public function escape($value): string;

    public function quote(string $value): string;

    public function beginTransaction();

    public function commit();

    public function rollBack();

    public function getLastInsertId(): int;
}
```

## Создание соединения

Для создания соединения необходимо создать экземпляр нужного класса:
```php
use jugger\db\driver\PdoConnection;
use jugger\db\driver\MysqliConnection;

$db = new PdoConnection();
$db->dsn = 'sqlite::memory:';

$db = new MysqliConnection();
$db->host = 'localhost';
$db->dbname = 'test';
$db->username = 'root';
$db->password = '';
```

Если вы используете пул соединений, то создание соединений будет выглядеть так:
```php
$pool = new ConnectionPool([
    'sqlite' => [
        'class' => 'jugger\db\driver\PdoConnection',
        'dsn' => 'sqlite::memory:',
    ],
    'mysql' => [
        'class' => 'jugger\db\driver\MysqliConnection',
        'host' => 'localhost',
        'dbname' => 'test',
        'username' => 'root',
        'password' => '',
    ],
]);
```

[Подробнее про `ConnectionPool`](connection-pool.md).

## Query

Данный метод выполняет запросы возвращающие данные (`SELECT`, `SHOW`, ...), и возвращает объект `QueryResult`, с помощью которого уже происходит чтение и обработка данных.

```php
// получить все строки сразу
$result = $db->query("SELECT id, name FROM t2");
$rows = $result->fetchAll();

// получить все строки последовательным чтением
$result = $db->query("SELECT id, name FROM t2");
$rows = [];
while ($row = $result->fetch()) {
    $rows[] = $row;
}

// получить количество строк в выборке
$result = $db->query("SELECT id, name FROM t2");
$count = $result->count();
```

[Подробнее про `QueryResult`](query-result.md).

## Execute

Данный метод выполняет запросы не возвращающие данные (`UPDATE`, `INSERT`, `DELETE`, `CREATE`, ...). В качестве возвращаемого значение, отправляется количество затронутых строк.

```php
$db->execute("INSERT INTO `t2` VALUES(1, 'value1')");
$db->execute("INSERT INTO `t2` VALUES(2, 'value2')");
$db->execute("INSERT INTO `t2` VALUES(3, 'value3')");
$db->execute("INSERT INTO `t2` VALUES(4, 'value4')");
$db->execute("INSERT INTO `t2` VALUES(5, 'value5')");

// $rows === 4
$rows = $db->execute("DELETE FROM t2 WHERE id > 1");
```

## Quote

Данный метод заключает строку в "рамки" (зависит от СУБД).
Результат исполнения следующего кода будет различаться в зависимости от разных реализаций соединений:

```php
$db->quote('keyword'); // MySQL: `keyword`
$db->quote('keyword'); // MS SQL: [keyword]

// также работает с цепочкой слов
$db->quote('table_name.column_name'); // `table_name`.`column_name`
```

**ВНИМАНИЕ**: не путать с методом `PDO::quote`, который заключает строку в кавычки и экранирует символы в ней.

## Escape

Данный метод экранирует строку, тем самым защищая запрос от SQL-инъекций.

```php
$username = $_POST['no-safe-data'];     // "' OR ''='"
$usernameEscaped = $db->escape($username);   // "\' OR \'\'=\'"

// SELECT * FROM users WHERE username = '' OR ''=''
$sql = "SELECT * FROM users WHERE username = '{$username}'";

// SELECT * FROM users WHERE username = '\' OR \'\'=\''
$sql = "SELECT * FROM users WHERE username = '{$usernameEscaped}'";
```

## ACID

Каждая соединение (если конечно сам движок это позволяет), должна обеспечивать инструмент транзакций.

```php
$db->execute("INSERT INTO t2 VALUES(1, 'value1')");
// откат
$db->beginTransaction();
$db->execute("INSERT INTO t2 VALUES(2, 'value2')");
$db->rollBack();
// фиксация
$db->beginTransaction();
$db->execute("INSERT INTO t2 VALUES(3, 'value3')");
$db->commit();
// результрующий набор
// [1, 'value1']
// [3, 'value3']
$rows = $db->query('SELECT * FROM t2')->fetchAll();
```
