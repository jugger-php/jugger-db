<?php

namespace jugger\db;

interface ConnectionInterface
{
    /**
     * Выполняет запрос SELECT и другие, которые возвращают значения
     * @param  string      $sql запрос
     * @return QueryResult      объект запроса (даже если ничего не найдено)
     */
    public function query(string $sql): QueryResult;
    /**
     * Выполняет запросы INSERT, UPDATE, DELETE и другие, которые не возвращают данные
     * @param  string  $sql запрос
     * @return integer      количество измененых (добавленыъ) строк
     */
    public function execute(string $sql): int;
    /**
     * Подготавливает значение
     * @param  mixed $value значение, которое необходимо подготовить
     * @return string       значение, защищенное от SQL инъекции
     */
    public function escape($value): string;
    /**
     * Оборачивает значение в кавычки
     * @param  string    $value имя столбца, таблицы, базы
     * @return string           значение обернутое в кавычки
     */
    public function quote(string $value): string;

    public function beginTransaction();

    public function commit();

    public function rollBack();

    public function getLastInsertId($tableName = null): int;
}
