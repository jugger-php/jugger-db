# Connection

Объект соединения с базой данных, является прослойкой между кодом и базой. Каждый класс соединения реализует интерфейс `ConnectionInterface`, поэтому в своих модулях, вы должны ссылаться именно на этот интерфейс.

Ниже представлен сам интерфейс:
```php
namespace jugger\db;

interface ConnectionInterface
{
    public function query(string $sql): QueryResult;

    public function execute(string $sql);

    public function escape($value);

    public function quote(string $value);
}
```

Класс реализующий данный интерфейс, должен проходить тест `tests/connection/connection.php`.

## query

Данные метод выполняет запросы возвращающие данные, типа `SELECT`, `SHOW` и т.д., и возвращает объект `QueryResult`, с помощью которого уже происходит чтение данных.

```php
$result = $conn->query("SELECT * FROM 'table'");

// получить все строки сразу
$rows = $result->fetchAll();

// получить все строки последовательным чтением
$rows = [];
while ($row = $result->fetch()) {
    $rows[] = $row;
}

```

## execute

Данный метод выполняет запросы не возвращающие данные, типа `UPDATE`, `INSERT`, `DELETE`, `CREATE` и т.д. В качестве возвращаемого значение, отправляется количество затронутых строк.

```php
$countRows = $conn->execute("INSERT INTO `users` VALUES('login', 'password')");
```

## escape

Данный метод экранирует строку, тем самым защищая базу от SQL-инъекций.

```php
$username = $_POST['no-safe-data'];     // "' OR ''='"
$username = $conn->escape($username);   // "\' OR \'\'=\'"

$sql = "SELECT * FROM users WHERE username = '{$content}'"; // SELECT * FROM users WHERE username = '\' OR \'\'=\''
```

## quote

Данный метод заключает ключевые слова (названия столбцов, таблиц, баз данных) в специальные символы (зависит от СУБД).
Результат исполнения следующего кода будет различаться в зависимости от разных реализаций соединений:

**ALERT**: не путать с методом `PDO::quote`, который заключает строку в кавычки и экранирует символы в ней.

```php
$conn->quote('keyword'); // MySQL: `keyword`
$conn->quote('keyword'); // MS SQL: [keyword]
```
