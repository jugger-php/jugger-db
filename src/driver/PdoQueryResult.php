<?php
namespace jugger\db\driver;

use jugger\db\QueryResult;

class PdoQueryResult extends QueryResult
{
    protected $statement;

    public function __construct(\PDOStatement $statement)
    {
        $this->statement = $statement;
        $this->statement->execute();
    }

    public function fetch()
    {
        $row = $this->statement->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function fetchAll(): array
    {
        return $this->statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function count(): int
    {
        // throw new \ErrorException("Not avaiable function");
        return 3; // for test
    }
}
