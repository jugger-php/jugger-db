<?php

namespace jugger\db;

class Command
{
    protected $db;
    protected $sql;

    public function __construct(ConnectionInterface $db)
    {
        $this->db = $db;
    }

    public function setSql(string $sql)
    {
        $this->sql = $sql;
    }

    public function getSql(): string
    {
        return $this->sql;
    }

    public function execute()
    {
        return $this->db->execute($this->sql);
    }

    public function insert(string $tableName, array $values): Command
	{
		$tableName = $this->db->quote($tableName);

		$columnsStr = [];
		$valuesStr = [];
		foreach ($values as $column => $value) {
			$columnsStr[] = $this->db->quote($column);
			$valuesStr[] = "'". $this->db->escape($value) ."'";
		}

		$columnsStr = implode(",", $columnsStr);
		$valuesStr = implode(",", $valuesStr);

		$this->sql = "INSERT INTO {$tableName}({$columnsStr}) VALUES({$valuesStr})";
        return $this;
	}

	public function update(string $tableName, array $columns, $where): Command
	{
		$tableName = $this->db->quote($tableName);

		$values = [];
		foreach ($columns as $name => $value) {
			$name = $this->db->quote($name);
			$value = $this->db->escape($value);
			$values[] = "{$name} = '{$value}'";
		}

		$values = implode(', ', $values);
		$whereStr = (new QueryBuilder($this->db))->buildWhere($where);
		$this->sql = "UPDATE {$tableName} SET {$values} {$whereStr}";
        return $this;
	}

	public function delete(string $tableName, $where)
	{
		$tableName = $this->db->quote($tableName);
		$whereStr = (new QueryBuilder($this->db))->buildWhere($where);
		$this->sql = "DELETE FROM {$tableName} {$whereStr}";
		return $this;
	}
}
