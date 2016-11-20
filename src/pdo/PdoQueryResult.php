<?php
namespace jugger\db\pdo;

use PDO;
use jugger\db\QueryResult;

class PdoQueryResult extends QueryResult
{
    protected $statement;

    public function __construct(PDOStatement $statement)
    {
        $this->statement = $statement;
    }

    public function fetch()
    {
        return $this->statement->fetch(PDO::FETCH_ASSOC);
    }

    public function fetchAll()
    {
        return $this->statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
