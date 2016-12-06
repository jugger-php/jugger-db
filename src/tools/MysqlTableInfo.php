<?php

namespace jugger\db\tools;

use jugger\db\Query;
use jugger\db\Connection;
use jugger\db\ConnectionPool;

class MysqlTableInfo implements TableInfoInterface
{
    public $db;
    public $tableName;

    public function __construct(string $tableName, Connection $db = null)
    {
        $this->tableName = $tableName;
        $this->db = $db ?? ConnectionPool::get('default');
    }

    public function getColumns(): array
    {
        $columns = [];
        return $columns;
    }
}
