<?php

namespace jugger\db;

abstract class QueryResult
{
    /**
     * Возвращает строку на которой в данный момент находиться указатель
     * @return array ключи - имена столбцов, значения - значения
     */
    abstract public function fetch();

    /**
     * Возвращает список всех строк
     * @return array
     */
    public function fetchAll(): array
    {
        $rows = [];
        while ($row = $this->fetch()) {
            $rows[] = $row;
        }
        return $rows;
    }
    /**
     * Количество строк в результирующем наборе
     * @return int
     */
    abstract public function count(): int;
}
