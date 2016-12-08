<?php
namespace jugger\db\driver;

use PDO;
use jugger\db\QueryResult;
use jugger\db\ConnectionInterface;

class PdoConnection implements ConnectionInterface
{
    public $dsn;
    public $username;
    public $password;
    public $options;

    public function getDriver()
    {
        static $driver = null;
        if (!$driver) {
            $driver = new PDO($this->dsn, $this->username, $this->password, $this->options);
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

    public function execute(string $sql): int
    {
        $db = $this->getDriver();
        return $db->exec($sql);
    }

    public function escape($value): string
    {
        if (ctype_digit($value)) {
            return (string) intval($value);
        }
        else {
            // protection against SQL injection
            $value  = mb_convert_encoding($value, "UTF-8");
            return addslashes($value);
        }
    }

    public function quote(string $value): string
    {
        $ret = [];
        $parts = explode(".", $value);
        foreach ($parts as $p) {
            $ret[] = "`{$p}`";
        }
        return implode(".", $ret);
    }

    public function beginTransaction()
    {
        $this->getDriver()->beginTransaction();
    }

    public function commit()
    {
        $this->getDriver()->commit();
    }

    public function rollBack()
    {
        $this->getDriver()->rollBack();
    }

    public function getLastInsertId($tableName = null): int
    {
        return $this->getDriver()->lastInsertId($tableName);
    }
}
