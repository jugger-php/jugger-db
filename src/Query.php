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

	public function select($value = "*"): Query
	{
		$this->select = $value;
		return $this;
	}

	public function from($value): Query
	{
		$this->from = $value;
		return $this;
	}

	public function join($type, $table, $on): Query
	{
		$this->join[] = [$type, $table, $on];
		return $this;
	}

	public function innerJoin($table, $on): Query
	{
		return $this->join('INNER', $table, $on);
	}

	public function leftJoin($table, $on): Query
	{
		return $this->join('LEFT', $table, $on);
	}

	public function rightJoin($table, $on): Query
	{
		return $this->join('RIGHT', $table, $on);
	}

	public function where($value): Query
	{
		$this->where[] = $value;
		return $this;
	}

	public function andWhere($value): Query
	{
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

	public function orWhere($value): Query
	{
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

	public function groupBy($value): Query
	{
		$this->groupBy = $value;
		return $this;
	}

	public function having($value): Query
	{
		$this->having = $value;
		return $this;
	}

	public function orderBy($value): Query
	{
		$this->orderBy = $value;
		return $this;
	}

	public function limit($limit, $offset = 0): Query
	{
		$this->limit = $limit;
		$this->offset = $offset;
		return $this;
	}

	public function build(ConnectionInterface $db): Query
	{
		return (new QueryBuilder($db))->build($this);
	}

	public function query(ConnectionInterface $db): Query
	{
        $sql = $this->build($db);
		return $db->query($sql);
	}

	public function one(ConnectionInterface $db): array
    {
		$row = $this->query($db)->fetch();
		return $row ?? null;
	}

	public function all(ConnectionInterface $db): array
    {
        return $this->query($db)->fetchAll();
	}
}
