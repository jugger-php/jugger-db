# Query result

Объект результата запроса - хранит в себе данные о запросе и возвращаемом значении. Для работы с результатами используются 2 метода: `fetch` и `fetchAll`, которые возвращают одну или все строки соотвественно.

Пример работы:
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
