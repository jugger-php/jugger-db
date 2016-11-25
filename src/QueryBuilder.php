<?php

namespace jugger\db;

use Exception;

class QueryBuilder
{
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
		return new Command($sql, $db);
	}

	public static function update(string $tableName, array $columns, $where)
	{
		$db = ConnectionPool::get('default');
		$tableName = $db->quote($tableName);

		$valuesStr = [];
		foreach ($columns as $name => $value) {
			$name = $db->quote($name);
			$value = $db->escape($value);
			$valuesStr[] = "{$name} = '{$value}'";
		}
		$valuesStr = implode(', ', $valuesStr);
		$whereStr = self::buildWhere($where);
		$sql = "UPDATE {$tableName} SET {$valuesStr} {$whereStr}";

		return new Command($sql, $db);
	}

	public static function delete(string $tableName, $where)
	{
		$db = ConnectionPool::get('default');
		$tableName = $db->quote($tableName);
		$whereStr = self::buildWhere($where);
		$sql = "DELETE FROM {$tableName} {$whereStr}";

		return new Command($sql, $db);
	}

	public static function build(Query $query)
	{
		return self::buildSelect($query->select) .
			self::buildFrom($query->from) .
			self::buildJoin($query->join) .
			self::buildWhere($query->where) .
			self::buildGroupBy($query->groupBy) .
			self::buildHaving($query->having) .
			self::buildOrderBy($query->orderBy) .
			self::buildLimitOffset($query->limit, $query->offset);
	}

	public static function buildLimitOffset($limit, $offset = 0)
	{
		$limit = (int) $limit;
		$offset = (int) $offset;

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

	public static function buildOrderBy($orderBy)
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
				$sql .= " {$column} {$sort}, ";
			}
			$sql = substr($sql, 0, -2);
		}

		return $sql;
	}

	public static function buildGroupBy($groupBy)
	{
		$sql = " GROUP BY ";
		$db = ConnectionPool::get('default');

		if (empty($groupBy)) {
			return "";
		}
		elseif (is_string($groupBy)) {
			$sql .= $groupBy;
		}
		elseif (is_array($groupBy)) {
			foreach ($groupBy as & $item) {
				$item = $db->quote($item);
			}
			$sql .= join(", ", $groupBy);
		}

		return $sql;
	}

	public static function buildHaving($having)
	{
		if (empty($having)) {
			return "";
		}

		return " HAVING ".$having;
	}

	public static function buildSelect($select)
	{
		$sql = "SELECT ";
		$db = ConnectionPool::get('default');

		if (empty($select)) {
			$sql .= "*";
		}
		elseif (is_string($select)) {
			$sql .= $select;
		}
		elseif (is_array($select)) {
			foreach ($select as $alias => $column) {
				if (is_integer($alias)) {
					$sql .= $db->quote($column);
				}
				elseif ($column instanceof Query) {
					$sql .= "({$column->build()}) AS ".$db->quote($alias);
				}
				else {
					$sql .= $db->quote($column) ." AS ".$db->quote($alias);
				}
				$sql .= ", ";
			}
			$sql = substr($sql, 0, -2);
		}

		return $sql;
	}

	public static function buildFrom($from)
	{
		$sql = " FROM ";
		$db = ConnectionPool::get('default');

		if (is_string($from)) {
			$sql .= $from;
		}
		elseif (is_array($from)) {
			foreach ($from as $alias => $table) {
				if (is_integer($alias)) {
					$sql .= $db->quote($table);
				}
				elseif ($table instanceof Query) {
					$sql .= "({$table->build()}) AS ".$db->quote($alias);
				}
				else {
					$sql .= $db->quote($table) ." AS ".$db->quote($alias);
				}
				$sql .= ", ";
			}
			$sql = substr($sql, 0, -2);
		}

		return $sql;
	}

	public static function buildJoin($join)
	{
		$sql = "";
		$db = ConnectionPool::get('default');

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
						$table = $db->quote(current($table));
					}
					else {
						$alias = key($table);
						$table = current($table);

						if ($table instanceof Query) {
							$table = "({$table->build()})";
						}
						else {
							$table = $db->quote($table);
						}

						$table .= ' AS '. $db->quote($alias);
					}
				}

				$sql .= " {$type} JOIN {$table} ON {$on} ";
			}
		}

		return $sql;
	}

	public static function buildWhere($where)
	{
		if (empty($where)) {
			return "";
		}

		$sql = " WHERE ";
		if (is_string($where)) {
			$sql .= $where;
		}
		elseif (is_array($where)) {
			$sql .= self::buildWhereComplex($where);
		}
		else {
			return "";
		}

		return $sql;
	}

	public static function buildWhereComplex(array $columns)
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
				$parts[] = '('. self::buildWhereComplex($value) .')';
			}
			elseif (is_integer($key)) {
				$parts[] = $value;
			}
			elseif (is_string($key)) {
				list($operator, $column) = self::parseOperator($key);
				$parts[] = self::buildWhereSimple($column, $operator, $value);
			}
			else {
				$params = var_export(compact('key', 'value'), true);
				throw new Exception("Invalide params: ". $params);
			}
		}

		return implode(" {$logic} ", $parts);
	}

	public static function parseOperator(string $key)
	{
		$re = '/^([!@%><=]*)(.*)$/';
		preg_match($re, $key, $m);
		$op = empty($m[1]) ? '=' : $m[1];
		$key = $m[2];
		return [$op, $key];
	}

	public static function buildWhereSimple(string $column, string $operator, $value)
	{
		$db = ConnectionPool::get('default');
		switch ($operator) {
			// EQUAL
			case '=':
				return self::equalOperator($column, $value);
			case '!':
			case '!=':
			case '<>':
				return self::equalOperator($column, $value, true);
			// IN
			case '@':
				return self::inOperator($column, $value);
			case '!@':
				return self::inOperator($column, $value, true);
			// BETWEEN
			case '><':
				return self::betweenOperator($column, $value);
			case '>!<':
				return self::betweenOperator($column, $value, true);
			// LIKE
			case '%':
				$column = $db->quote($column);
				if ($value instanceof Query) {
					$value = "({$value->build()})";
				}
				else {
					$value = "'". $db->escape($value) ."'";
				}
				return $column ." LIKE {$value}";
			case '!%':
				$column = $db->quote($column);
				if ($value instanceof Query) {
					$value = "({$value->build()})";
				}
				else {
					$value = "'". $db->escape($value) ."'";
				}
				return $column ." NOT LIKE {$value}";
			// other
			case '>':
			case '>=':
			case '<':
			case '<=':
				$column = $db->quote($column);
                $value = "'". $db->escape($value) ."'";
				return $column . $operator . $value;
			default:
				 throw new \Excpection("Not found operator '{$operator}'");
		}
	}

	public static function equalOperator(string $column, $value, bool $isNot = false)
	{
		$not = $isNot ? "NOT" : "";
		$db = ConnectionPool::get('default');

		if (is_null($value)) {
			$sql = " IS {$not} NULL";
		}
		elseif (is_bool($value)) {
			$sql = " IS {$not} ".($value ? "TRUE" : "FALSE");
		}
		elseif (is_scalar($value)) {
			$op = $isNot ? "<>" : "=";
            $value = "'". $db->escape($value) ."'";
			$sql = " {$op} ".$value;
		}
		elseif (is_array($value) || $value instanceof Query) {
			return self::inOperator($column, $value, $isNot);
		}
		$column = $db->quote($column);

		return $column . $sql;
	}

	public static function inOperator(string $column, $value, bool $isNot = false)
	{
		$db = ConnectionPool::get('default');
		$sql = $db->quote($column) . ($isNot ? " NOT IN ": " IN ");

		if (is_string($value)) {
			$sql .= "($value)";
		}
		elseif ($value instanceof Query) {
			$sql .= "({$value->build()})";
		}
		elseif (is_array($value)) {
			foreach ($value as $item) {
				$item = $db->escape($item);
			}
			$sql .= "(". join(",", $value). ")";
		}

		return $sql;
	}

	public static function betweenOperator(string $column, array $value, bool $isNot = false)
	{
		$operator = $isNot ? "NOT BETWEEN" : "BETWEEN";
		$db = ConnectionPool::get('default');
		$column = $db->quote($column);
		$min = (int) $value[0];
		$max = (int) $value[1];

		return " {$column} {$operator} {$min} AND {$max} ";
	}
}
