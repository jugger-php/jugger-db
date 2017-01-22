# Table info

Для работы с таблицами `Mysql`, существует вспомогательный класс `MysqlTableInfo`. С помощью экземпляра данного класса можно получить информацию о схеме таблицы.

## Пример

Допустим имеется таблица:
```sql
CREATE TABLE `test_table_info` (
    `id` INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(200) NOT NULL UNIQUE,
    `content` TEXT,
    `image` BLOB,
    `date` DATE DEFAULT NULL,
    `datetime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
)
```

Получение информации о таблице:
```php
use jugger\db\tools\MysqlTableInfo;
use jugger\db\tools\ColumnInfoInterface;

$tableInfo = new MysqlTableInfo('test_table_info', $db);
$columns = $tableInfo->getColumns();

$columns['id']->getSize();      // 11
$columns['id']->getType();      // ColumnInfoInterface::TYPE_INT
$columns['id']->getKey();       // ColumnInfoInterface::KEY_PRIMARY
$columns['id']->getOther();     // 'auto_increment'
$columns['id']->getIsNull();    // false

$columns['name']->getSize();    // 200
$columns['name']->getType();    // ColumnInfoInterface::TYPE_TEXT
$columns['name']->getKey();     // ColumnInfoInterface::KEY_UNIQUE
$columns['name']->getIsNull();  //  false

$columns['content']->getType();     //  ColumnInfoInterface::TYPE_TEXT
$columns['content']->getIsNull();   //  true

$columns['image']->getType();   //  ColumnInfoInterface::TYPE_BLOB
$columns['image']->getIsNull(); //  true

$columns['date']->getType();    //  ColumnInfoInterface::TYPE_DATETIME
$columns['date']->getIsNull();  //  true

$columns['datetime']->getType();    //  ColumnInfoInterface::TYPE_DATETIME
$columns['datetime']->getDefault(); //  'CURRENT_TIMESTAMP'
$columns['datetime']->getIsNull();  //  false

$columns['content']->getType();     //  ColumnInfoInterface::TYPE_TEXT
$columns['content']->getSize();     //  0
$columns['content']->getKey();      //  -1
$columns['content']->getDefault();  //  ""
$columns['content']->getOther();    //  ""
$columns['content']->getIsNull();   //  true
```

Интерфейс `ColumnInfoInterface` содержит константы типов столбцов и ключей, а также методов для получения информации о столбце:
```php
interface ColumnInfoInterface
{
    const KEY_PRIMARY = 1;
    const KEY_UNIQUE = 2;
    const KEY_INDEX = 3;

    const TYPE_INT = 'int';
    const TYPE_TEXT = 'text';
    const TYPE_BLOB = 'blob';
    const TYPE_FLOAT = 'float';
    const TYPE_DATETIME = 'datetime';

    public function getName(): string;

    public function getType(): string;

    public function getSize(): int;

    public function getKey(): int;

    public function getIsNull(): bool;

    public function getDefault(): string;

    public function getOther(): string;
}
```
