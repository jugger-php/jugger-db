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
    public function fetchAll()
    {
        $rows = [];
        while ($row = $this->fetch()) {
            $rows[] = $row;
        }
        return $rows;
    }
}
