<?php
namespace jugger\db\driver;

use jugger\db\QueryResult;
use jugger\db\ConnectionInterface;

class MysqliConnection implements ConnectionInterface
{
    private $driver;

    public $host;
    public $dbname;
    public $username;
    public $password;

    public function getDriver()
    {
        if (!$this->driver) {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            $this->driver = new \mysqli(
                $this->host,
                $this->username,
                $this->password,
                $this->dbname
            );
        }
        return $this->driver;
    }

    public function query(string $sql): QueryResult
    {
        $result = $this->getDriver()->query($sql);
        return new MysqliQueryResult($result);
    }

    public function execute(string $sql): int
    {
        $this->getDriver()->query($sql);
        return $this->getDriver()->affected_rows;
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

    public function getLastInsertId(): string
    {
        return $this->getDriver()->insert_id;
    }
}
