<?php
namespace jugger\db\pdo;

use PDO;
use jugger\db\QueryResult;
use jugger\db\ConnectionInterface;

class PdoConnection implements ConnectionInterface
{
    public $dsn;

    public function getDriver()
    {
        static $driver = null;
        if (!$driver) {
            $driver = new PDO($this->dsn);
            $driver->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $driver;
    }

    public function query(string $sql): QueryResult
    {
        $db = $this->getDriver();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return new PdoQueryResult($stmt);
    }

    public function execute(string $sql)
    {
        $db = $this->getDriver();
        return $db->exec($sql);
    }

    public function escape($value)
    {
        if (ctype_digit($value)) {
            return (int) $value;
        }
        else {
            // protection against SQL injection
            $value  = mb_convert_encoding($value, "UTF-8");
            return addslashes($value);
        }
    }

    public function quote(string $value)
    {
        $db = $this->getDriver();
        return $db->quote($value);
    }
}
