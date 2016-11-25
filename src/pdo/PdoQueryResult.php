<?php
namespace jugger\db\pdo;

use PDO;
use PDOStatement;
use Exception;
use jugger\db\QueryResult;

class PdoQueryResult extends QueryResult
{
    protected $statement;

    public function __construct(PDOStatement $statement)
    {
        $this->statement = $statement;
        $this->statement->execute();
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
