<?php

namespace jugger\db;

class Command
{
    protected $db;
    protected $sql;

    public function __construct(string $sql, ConnectionInterface $db)
    {
        $this->db = $db;
        $this->sql = $sql;
    }

    public function getSql()
    {
        return $this->sql;
    }

    public function execute()
    {
        return $this->db->execute($this->sql);
    }
}
