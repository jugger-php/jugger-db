<?php
namespace jugger\db\driver;

use jugger\db\QueryResult;

class MysqliQueryResult extends QueryResult
{
    protected $result;

    public function __construct(\mysqli_result $result)
    {
        $this->result = $result;
    }

    public function fetch()
    {
        return $this->result->fetch_assoc();
    }

    public function fetchAll(): array
    {
        return $this->result->fetch_all(\MYSQLI_ASSOC);
    }

    public function count(): int
    {
        return $this->result->num_rows;
    }
}
