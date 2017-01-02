<?php
namespace jugger\db\driver;

use jugger\db\QueryResult;
use jugger\db\ConnectionInterface;

class MysqliConnection implements ConnectionInterface
{
    public $host;
    public $dbname;
    public $username;
    public $password;

    public function getDriver()
    {
        static $driver;
        if (!$driver) {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            $driver = new \mysqli(
                $this->host,
                $this->username,
                $this->password,
                $this->dbname
            );
        }
        return $driver;
    }

    public function query(string $sql): QueryResult
    {
        $result = $this->getDriver()->query($sql);
        return new MysqliQueryResult($result);
    }

    public function execute(string $sql): int
    {
        return $this->getDriver()->query($sql);
    }

    public function escape($value, string $charset = 'utf8'): string
    {
        if (ctype_digit($value)) {
            return (string) intval($value);
        }
        else {
            $this->getDriver()->set_charset($charset);
            return $this->getDriver()->real_escape_string($value);
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
        $this->getDriver()->begin_transaction();
    }

    public function commit()
    {
        $this->getDriver()->commit();
    }

    public function rollBack()
    {
        $this->getDriver()->rollback();
    }

    public function getLastInsertId($tableName = null): string
    {
        return $this->getDriver()->insert_id;
    }
}
