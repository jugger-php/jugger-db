<?php
namespace jugger\db\driver;

use jugger\db\QueryResult;
use jugger\db\ConnectionInterface;

class MysqliConnection implements ConnectionInterface
{
    protected $db;

    public function __construct(string $host, string $username, string $password, string $dbname)
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $this->db = new \mysqli($host, $username, $password, $dbname);
    }

    public function query(string $sql): QueryResult
    {
        $result = $this->db->query($sql);
        return new MysqliQueryResult($result);
    }

    public function execute(string $sql): int
    {
        return $this->db->query($sql);
    }

    public function escape($value, string $charset = 'utf8'): string
    {
        if (ctype_digit($value)) {
            return (string) intval($value);
        }
        else {
            $this->db->set_charset($charset);
            return $this->db->real_escape_string($value);
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
        $this->db->begin_transaction();
    }

    public function commit()
    {
        $this->db->commit();
    }

    public function rollBack()
    {
        $this->db->rollback();
    }

    public function getLastInsertId($tableName = null): string
    {
        return $this->db->insert_id;
    }
}
