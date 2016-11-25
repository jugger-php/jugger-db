<?php

namespace jugger\db;

use Bitrix\Main\Application;

class Query
{
	public $select = "*";
	public $from;
	public $join;
	public $where;
	public $groupBy;
	public $having;
	public $orderBy;
	public $limit;
	public $offset;

	public function select($value = "*") {
		$this->select = $value;
		return $this;
	}

	public function from($value) {
		$this->from = $value;
		return $this;
	}

	public function join($type, $table, $on) {
		$this->join[] = [$type, $table, $on];
		return $this;
	}

	public function innerJoin($table, $on) {
		return $this->join('INNER', $table, $on);
	}

	public function leftJoin($table, $on) {
		return $this->join('LEFT', $table, $on);
	}

	public function rightJoin($table, $on) {
		return $this->join('RIGHT', $table, $on);
	}

	public function where($value) {
		$this->where[] = $value;
		return $this;
	}

	public function andWhere($value) {
		if (empty($this->where)) {
			$this->where = $value;
		}
		else {
			$this->where = [
				'AND',
				$this->where,
				$value
			];
		}

		return $this;
	}

	public function orWhere($value) {
		if (empty($this->where)) {
			$this->where = $value;
		}
		else {
			$this->where = [
				'OR',
				$this->where,
				$value
			];
		}

		return $this;
	}

	public function groupBy($value) {
		$this->groupBy = $value;
		return $this;
	}

	public function having($value) {
		$this->having = $value;
		return $this;
	}

	public function orderBy($value) {
		$this->orderBy = $value;
		return $this;
	}

	public function limit($limit, $offset = 0) {
		$this->limit = $limit;
		$this->offset = $offset;
		return $this;
	}

	public function build() {
		return (new QueryBuilder($this))->build();
	}

	public function query() {
        $sql = $this->build();
		return ConnectionPool::get('default')->query($sql);
	}

	public function one(): array
    {
		$row = $this->query()->fetch();
		return $row ?? null;
	}

	public function all(): array
    {
        return $this->query()->fetchAll();
	}

	/*
	 * C[r]UD operations
	 */

	public static function insert(string $tableName, array $values)
	{
		$db = ConnectionPool::get('default');
		$tableName = $db->quote($tableName);

		$columnsStr = [];
		$valuesStr = [];
		foreach ($values as $column => $value) {
			$columnsStr[] = $db->quote($column);
			$valuesStr[] = "'". $db->escape($value) ."'";
		}

		$columnsStr = implode(",", $columnsStr);
		$valuesStr = implode(",", $valuesStr);

		$sql = "INSERT INTO {$tableName}({$columnsStr}) VALUES({$valuesStr})";
		return $db->execute($sql);
	}

	public static function update(string $tableName, array $columns, $where)
	{

	}

	public static function delete(string $tableName, $where)
	{

	}
}
