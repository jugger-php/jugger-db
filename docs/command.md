# Command

Для создания `INSERT`, `UPDATE`, `DELETE` запросов используется класс `Command`. Данный класс является конструктором запросов и содержит сформированный SQL.

## INSERT

Для оператора `INSERT` нужно в параметрах передать ассоциативный массив со значениями столбцов. Данные автоматически экранируются, поэтому можно передать не подготовленные данные.
```php
use jugger\db\Command;

$values = [
    'name' => 'name_val',
    'content' => "' and \'1' = 1",
    'update_time' => 1400000000,
];

// INSERT INTO `t1`(`name`,`content`,`update_time`) VALUES('name_val','\' AND \\\\\'1\\\" = 1','1400000000')
$command = (new Command($db))->insert("t1", $values);
```

## UPDATE

Для оператора `UPDATE` нужно в параметрах передать ассоциативный массив с новыми значениями столбцов и условия для обновления. Данные автоматически экранируются, поэтому можно передать не подготовленные данные. Параметр условия формируется также, как и конструкция `WHERE`.
```php
use jugger\db\Command;

$values = [
    'name' => 'new name',
    'content' => 'new content',
];
$where = [
    '!id' => null,
];

// UPDATE `t1` SET `name` = 'new name', `content` = 'new content'  WHERE `id` IS NOT NULL
$command = (new Command($db))->update("t1", $values, $where);
```

## DELETE

Для оператора `DELETE` нужно в параметрах передать список условий для удаления. Параметр условия формируется также, как и конструкция `WHERE`.

```php
use jugger\db\Command;

$where = [
    '!id' => null,
];

// DELETE FROM `t1`  WHERE `id` IS NOT NULL
$command = (new Command($db))->delete("t1", $where);
```

## Execute

Для выполнения команды используется метод `execute`, который возвращает количество затронутых строк:
```php
$command->execute();
```

## Get SQL

Для получения уже сформированного SQL запроса, можно использовать метод `getSql`:
```php
$sql = $command->getSql();
```
