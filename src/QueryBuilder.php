<?php

namespace jugger\db;

class QueryBuilder
{
	public $query;
	public $connection;

	public function __construct(Query $query) {
		$this->query = $query;
		$this->connection = ConnectionPool::get('default');
	}

	public function insert($table, $values) {
		$tmp = [];
		$columns = [];
		foreach ($values as $column => $value) {
			$tmp[] = $value instanceof SqlExpression
				? $value
				: $this->connection->escape($value);
			$columns[] = $this->connection->quote($column);
		}
		$table = $this->connection->quote($table);
		$columns = join(", ", $columns);
		$values = join(", ", $tmp);
		$sql = "INSERT INTO {$table}({$columns}) VALUES({$values})";
		return $sql;
	}

	public function update($table, $values, $where = null) {
		$sets = [];
		foreach ($values as $column => $value) {
			$column = $this->connection->quote($column);
			$value = $value instanceof SqlExpression
				? $value
				: $this->connection->escape($value);
			$sets[] = "{$column} = {$value}";
		}
		$sets = join(", ", $sets);
		$table = $this->connection->quote($table);
		$sql = "UPDATE {$table} SET {$sets}";
		if (empty($where)) {
			// pass
		}
		elseif (is_string($where)) {
			$sql .= " WHERE ". $where;
		}
		elseif (is_array($where)) {
			$sql .= " WHERE ". $this->buildWhereComplex($where);
		}
		return $sql;
	}

	public function build() {
		return $this->buildSelect($this->query->select) .
			$this->buildFrom($this->query->from) .
			$this->buildJoin($this->query->join) .
			$this->buildWhere($this->query->where) .
			$this->buildGroupBy($this->query->groupBy) .
			$this->buildHaving($this->query->having) .
			$this->buildOrderBy($this->query->orderBy) .
			$this->buildLimitOffset($this->query->limit, $this->query->offset);
	}

	public function buildLimitOffset($limit, $offset = 0) {
		$limit = (int) $limit;
		$offset = (int) $offset;
		if ($offset) {
			return " LIMIT {$offset}, {$limit}";
		}
		elseif ($limit > 0) {
			return " LIMIT {$limit}";
		}
	}

	public function buildOrderBy($orderBy) {
		$sql = " ORDER BY ";
		if (empty($orderBy)) {
			return "";
		}
		elseif (is_string($orderBy)) {
			$sql .= $orderBy;
		}
		elseif (is_array($orderBy)) {
			foreach ($orderBy as $column => $sort) {
				$sql .= " {$column} {$sort}, ";
			}
			$sql = substr($sql, 0, -2);
		}
		return $sql;
	}

	public function buildGroupBy($groupBy) {
		$sql = " GROUP BY ";
		if (empty($groupBy)) {
			return "";
		}
		elseif (is_string($groupBy)) {
			$sql .= $groupBy;
		}
		elseif (is_array($groupBy)) {
			$groupBy = array_map(function($item){
				return $this->connection->quote($item);
			}, $groupBy);
			$sql .= join(", ", $groupBy);
		}
		return $sql;
	}

	public function buildHaving($having) {
		if (empty($having)) {
			return "";
		}
		return " HAVING ".$having;
	}

	public function buildSelect($select) {
		$sql = "SELECT ";
		if (empty($select)) {
			$sql .= "*";
		}
		elseif (is_string($select)) {
			$sql .= $select;
		}
		elseif (is_array($select)) {
			foreach ($select as $alias => $column) {
				if (is_integer($alias)) {
					$sql .= $this->connection->quote($column);
				}
				elseif ($column instanceof Query) {
					$sql .= "({$column->build()}) AS ".$this->connection->quote($alias);
				}
				else {
					$sql .= $this->connection->quote($column) ." AS ".$this->connection->quote($alias);
				}
				$sql .= ", ";
			}
			$sql = substr($sql, 0, -2);
		}
		return $sql;
	}

	public function buildFrom($from) {
		$sql = " FROM ";
		if (is_string($from)) {
			$sql .= $from;
		}
		elseif (is_array($from)) {
			foreach ($from as $alias => $table) {
				if (is_integer($alias)) {
					$sql .= $this->connection->quote($table);
				}
				elseif ($table instanceof Query) {
					$sql .= "({$table->build()}) AS ".$this->connection->quote($alias);
				}
				else {
					$sql .= $this->connection->quote($table) ." AS ".$this->connection->quote($alias);
				}
				$sql .= ", ";
			}
			$sql = substr($sql, 0, -2);
		}
		return $sql;
	}

	public function buildJoin($join) {
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
						$table = $this->connection->quote(current($table));
					}
					else {
						$alias = key($table);
						$table = current($table);

						if ($table instanceof Query) {
							$table = "({$table->build()})";
						}
						else {
							$table = $this->connection->quote($table);
						}

						$table .= ' AS '. $this->connection->quote($alias);
					}
				}

				$sql .= " {$type} JOIN {$table} ON {$on} ";
			}
		}
		return $sql;
	}

	public function buildWhere($where) {
		if (empty($where)) {
			return "";
		}

		$sql = " WHERE ";
		if (is_string($where)) {
			$sql .= $where;
		}
		elseif (is_array($where)) {
			$sql .= $this->buildWhereComplex($where);
		}
		return $sql;
	}

	public function buildWhereComplex(array $columns) {
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
				$parts[] = "({$this->buildWhereComplex($value)})";
			}
			elseif (is_integer($key)) {
				$parts[] = $value;
			}
			elseif (is_string($key)) {
				$operator = $this->parseOperator($key);
				$parts[] = $this->buildWhereSimple($key, $operator, $value);
			}
			else {
				$params = var_export(compact('key', 'value'), true);
				throw new \Exception("Invalide params: ". $params);
			}
		}

		return join(" {$logic} ", $parts);
	}

	public function parseOperator(& $key) {
		$re = '/^([!@%><=]*)(.*)$/';
		preg_match($re, $key, $m);
		$op = empty($m[1]) ? '=' : $m[1];
		$key = $m[2];
		return $op;
	}

	public function buildWhereSimple($column, $operator, $value) {
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
				$column = $this->connection->quote($column);
				if ($value instanceof Query) {
					$value = "({$value->build()})";
				}
				else {
					$value = "'". $this->connection->escape($value) ."'";
				}
				return $column ." LIKE {$value}";
			case '!%':
				$column = $this->connection->quote($column);
				if ($value instanceof Query) {
					$value = "({$value->build()})";
				}
				else {
					$value = "'". $this->connection->escape($value) ."'";
				}
				return $column ." NOT LIKE {$value}";
			// other
			case '>':
			case '>=':
			case '<':
			case '<=':
				$column = $this->connection->quote($column);
                $value = "'". $this->connection->escape($value) ."'";
				return $column . $operator . $value;
			default:
				 throw new \Excpection("Not found operator '{$operator}'");
		}
	}

	public function equalOperator($column, $value, $isNot = false) {
		$not = $isNot ? "NOT" : "";

		if (is_null($value)) {
			$sql = " IS {$not} NULL";
		}
		elseif (is_bool($value)) {
			$sql = " IS {$not} ".($value ? "TRUE" : "FALSE");
		}
		elseif (is_scalar($value)) {
			$op = $isNot ? "<>" : "=";
            $value = "'". $this->connection->escape($value) ."'";
			$sql = " {$op} ".$value;
		}
		elseif (is_array($value) || $value instanceof Query) {
			return $this->inOperator($column, $value, $isNot);
		}

		$column = $this->connection->quote($column);
		return $column . $sql;
	}

	public function inOperator($column, $value, $isNot = false) {
		$sql = $this->connection->quote($column) . ($isNot ? " NOT IN ": " IN ");
		if (is_string($value)) {
			$sql .= "($value)";
		}
		elseif ($value instanceof Query) {
			$sql .= "({$value->build()})";
		}
		elseif (is_array($value)) {
			$value = array_map(function($item) {
				return $this->connection->escape($item);
			}, $value);
			$sql .= "(". join(",", $value). ")";
		}
		return $sql;
	}

	public function betweenOperator($column, $value, $isNot = false) {
		$sql = $isNot ? " NOT BETWEEN " : " BETWEEN ";
		$min = (int) $value[0];
		$max = (int) $value[1];
		return $sql ." {$min} AND {$max} ";
	}
}
