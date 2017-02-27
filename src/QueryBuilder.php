<?php

namespace jugger\db;

class QueryBuilder
{
	protected $db;

	public function __construct(ConnectionInterface $db)
	{
		$this->db = $db;
	}

	public function build(Query $query): string
	{
		$sql  = "SELECT ". $this->buildSelect($query->select, $query->distinct);
		$sql .= " FROM ". $this->buildFrom($query->from);
		if ($query->join) {
			$sql .= $this->buildJoin($query->join);
		}
		if ($query->where) {
			$sql .= $this->buildWhere($query->where);
		}
		if ($query->groupBy) {
			$sql .= $this->buildGroupBy($query->groupBy);
		}
		if ($query->having) {
			$sql .= $this->buildHaving($query->having);
		}
		if ($query->orderBy) {
			$sql .= $this->buildOrderBy($query->orderBy);
		}
		if ($query->limit && $query->offset) {
			$sql .= $this->buildLimitOffset($query->limit, $query->offset);
		}
		elseif ($query->limit) {
			$sql .= $this->buildLimitOffset($query->limit);
		}

		return $sql;
	}

	public function buildLimitOffset(int $limit, int $offset = 0): string
	{
		if ($limit < 1) {
			return "";
		}
		elseif ($offset) {
			return " LIMIT {$offset}, {$limit}";
		}
		else {
			return " LIMIT {$limit}";
		}
	}

	public function buildOrderBy($orderBy): string
	{
		$sql = " ORDER BY ";

		if (empty($orderBy)) {
			return "";
		}
		elseif (is_string($orderBy)) {
			$sql .= $orderBy;
		}
		elseif (is_array($orderBy)) {
			foreach ($orderBy as $column => $sort) {
				if (is_integer($column)) {
					$sql .= " {$sort}, ";
				}
				else {
					$column = $this->db->quote($column);
					$sql .= " {$column} {$sort}, ";
				}
			}
			$sql = substr($sql, 0, -2);
		}
		return $sql;
	}

	public function buildGroupBy($groupBy): string
	{
		$sql = " GROUP BY ";

		if (empty($groupBy)) {
			return "";
		}
		elseif (is_string($groupBy)) {
			$sql .= $groupBy;
		}
		elseif (is_array($groupBy)) {
			foreach ($groupBy as & $item) {
				$item = $this->db->quote($item);
			}
			$sql .= join(", ", $groupBy);
		}
		return $sql;
	}

	public function buildHaving(string $having): string
	{
		if (empty($having)) {
			return "";
		}
		return " HAVING ".$having;
	}

	public function buildSelect($select, $distinct): string
	{
		$sql = null;
		if ($distinct) {
			$sql .= "DISTINCT ";
		}

		if (empty($select)) {
			$sql .= "*";
		}
		elseif (is_string($select)) {
			$sql .= $select;
		}
		elseif (is_array($select)) {
			foreach ($select as $alias => $column) {
				if (is_integer($alias)) {
					$sql .= $column instanceof Expression ? $column->getValue() : $this->db->quote($column);
				}
				elseif ($column instanceof Query) {
					$sql .= "({$column->build()}) AS ".$this->db->quote($alias);
				}
				elseif ($column instanceof Expression) {
					$sql .= "{$column} AS ".$this->db->quote($alias);
				}
				else {
					$sql .= $this->db->quote($column) ." AS ". $this->db->quote($alias);
				}
				$sql .= ", ";
			}
			$sql = substr($sql, 0, -2);
		}
		return $sql;
	}

	public function buildFrom($from): string
	{
		$sql = null;

		if (is_string($from)) {
			$sql .= $from;
		}
		elseif (is_array($from)) {
			foreach ($from as $alias => $table) {
				if (is_integer($alias)) {
					$sql .= $this->db->quote($table);
				}
				elseif ($table instanceof Query) {
					$sql .= "({$table->build()}) AS ".$this->db->quote($alias);
				}
				elseif ($table instanceof Expression) {
					$sql .= "{$table} AS ".$this->db->quote($alias);
				}
				else {
					$sql .= $this->db->quote($table) ." AS ".$this->db->quote($alias);
				}
				$sql .= ", ";
			}
			$sql = substr($sql, 0, -2);
		}
		return $sql;
	}

	public function buildJoin($join): string
	{
		$sql = "";

		if (empty($join)) {
			// pass
		}
		elseif (is_string($join)) {
			$sql = $join;
		}
		elseif (is_array($join)) {
			foreach ($join as $data) {
				list($type, $table, $on) = $data;
				if (is_array($table)) {
					if (is_integer(key($table))) {
						$table = $this->db->quote(current($table));
					}
					else {
						$alias = key($table);
						$table = current($table);

						if ($table instanceof Query) {
							$table = "({$table->build()})";
						}
						else {
							$table = $this->db->quote($table);
						}

						$table .= ' AS '. $this->db->quote($alias);
					}
				}

				$sql .= " {$type} JOIN {$table} ON {$on} ";
			}
		}

		return $sql;
	}

	public function buildWhere($where): string
	{
		$sql = " WHERE ";

		if (empty($where)) {
			return "";
		}
		elseif (is_string($where)) {
			$sql .= $where;
		}
		elseif (is_array($where)) {
			$sql .= $this->buildWhereComplex($where);
		}
		else {
			throw new \InvalidArgumentException("Parametr `where` must be type of `string` or `array`");
		}
		return $sql;
	}

	public function buildWhereComplex(array $columns): string
	{
		$logic = "AND";
		if (isset($columns[0]) && is_scalar($columns[0])) {
			if (strtoupper(trim($columns[0])) == "AND") {
				$logic = "AND";
				unset($columns[0]);
			}
			elseif (strtoupper(trim($columns[0])) == "OR") {
				$logic = "OR";
				unset($columns[0]);
			}
		}

		$parts = [];
		foreach ($columns as $key => $value) {
			if (is_integer($key) && is_array($value)) {
				$parts[] = '('. $this->buildWhereComplex($value) .')';
			}
			elseif (is_integer($key)) {
				$parts[] = $value;
			}
			elseif (is_string($key)) {
				list($operator, $column) = $this->parseOperator($key);
				$parts[] = $this->buildWhereSimple($column, $operator, $value);
			}
			else {
				$params = var_export(compact('key', 'value'), true);
				throw new \Exception("Invalide params: ". $params);
			}
		}

		return implode(" {$logic} ", $parts);
	}

	public function parseOperator(string $key): array
	{
		$re = '/^([!@%><=]*)(.*)$/';
		preg_match($re, $key, $m);
		$op = empty($m[1]) ? '=' : $m[1];
		$key = $m[2];
		return [$op, $key];
	}

	public function buildValue($value)
	{
		if ($value instanceof Query) {
			return "({$value->build()})";
		}
		elseif ($value instanceof Expression) {
			return $value->getValue();
		}
		else {
			return "'{$this->db->escape($value)}'";
		}
	}

	public function buildWhereSimple(string $column, string $operator, $value): string
	{
		switch ($operator) {
			// EQUAL
			case '=':
				return $this->equalOperator($column, $value);
			case '!':
			case '!=':
			case '<>':
				return $this->equalOperator($column, $value, true);
			// IN
			case '@':
				return $this->inOperator($column, $value);
			case '!@':
				return $this->inOperator($column, $value, true);
			// BETWEEN
			case '><':
				return $this->betweenOperator($column, $value);
			case '>!<':
				return $this->betweenOperator($column, $value, true);
			// LIKE
			case '%':
				return $this->likeOperator($column, $value);
			case '!%':
				return $this->likeOperator($column, $value, true);
			// other
			case '>':
			case '>=':
			case '<':
			case '<=':
				$column = $this->db->quote($column);
                $value = $this->buildValue($value);
				return $column . $operator . $value;
			default:
				 throw new \Exception("Not found operator '{$operator}'");
		}
	}

	public function equalOperator(string $column, $value, bool $isNot = false)
	{
		$not = $isNot ? "NOT" : "";

		if (is_null($value)) {
			$sql = "IS {$not} NULL";
		}
		elseif (is_bool($value)) {
			$sql = "IS {$not} ".($value ? "TRUE" : "FALSE");
		}
		elseif (is_array($value) || $value instanceof Query) {
			return $this->inOperator($column, $value, $isNot);
		}
		else {
			$op = $isNot ? "<>" : "=";
			$value = $this->buildValue($value);
			$sql = "{$op} {$value}";
		}

		$column = $this->db->quote($column);
		return "{$column} {$sql}";
	}

	public function likeOperator(string $column, $value, bool $isNot = false): string
	{
		$value = $this->buildValue($value);
		$column = $this->db->quote($column);
		$operator = $isNot ? "NOT LIKE" : "LIKE";
		return "{$column} {$operator} {$value}";
	}

	public function inOperator(string $column, $value, bool $isNot = false): string
	{
		if (is_string($value)) {
			$value = "($value)";
		}
		elseif (is_array($value)) {
			$sql = "";
			foreach ($value as $item) {
				$item = $this->db->escape($item);
				$sql .= "'{$item}', ";
			}
			$sql = substr($sql, 0, -2);
			$value = "({$sql})";
		}
		elseif ($value instanceof Query) {
			$value = $this->buildValue($value);
		}
		else {
			$value = "({$this->buildValue($value)})";
		}

		$column = $this->db->quote($column);
		$operator = $isNot ? "NOT IN" : "IN";
		return "{$column} {$operator} {$value}";
	}

	public function betweenOperator(string $column, array $value, bool $isNot = false): string
	{
		$min = (int) $value[0];
		$max = (int) $value[1];

		$column = $this->db->quote($column);
		$operator = $isNot ? "NOT BETWEEN" : "BETWEEN";
		return " {$column} {$operator} {$min} AND {$max} ";
	}
}
