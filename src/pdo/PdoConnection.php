<?php
namespace jugger\db\pdo;

use jugger\db\QueryResult;
use jugger\db\ConnectionInterface;

class PdoConnection implements ConnectionInterface
{
    public $host;
    public $username;
    public $password;
    public $database;

    protected function getDriver()
    {
        static $driver = null;
        if (!$driver) {
            $driver = new mysqli(
                $this->host,
                $this->username,
                $this->password,
                $this->database
            );
        }
        return $driver;
    }

    public function query(string $sql): QueryResult
    {
        $db = $this->getDriver();
        return new PdoQueryResult($db->query($sql));
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
