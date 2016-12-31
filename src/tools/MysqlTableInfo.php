<?php

namespace jugger\db\tools;

use jugger\db\Query;
use jugger\db\ConnectionPool;
use jugger\db\ConnectionInterface;

class MysqlTableInfo
{
    public $db;
    public $tableName;

    public function __construct(string $tableName, ConnectionInterface $db)
    {
        $this->db = $db;
        $this->tableName = $tableName;
    }

    public function getColumns(): array
    {
        $sql = "SHOW COLUMNS FROM {$this->tableName}";
        $result = $this->db->query($sql);
        $columns = [];
        while ($row = $result->fetch()) {
            $value = new MysqlColumnInfo($row);
            $columns[$value->getName()] = $value;
        }
        return $columns;
    }
}
