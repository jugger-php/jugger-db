<?php

namespace jugger\db;

interface ConnectionInterface
{
    /**
     * Выполняет запросы, которые возвращают значение (SHOW, SELECT, ...)
     * @param  string      $sql запрос
     * @return QueryResult      объект запроса (даже если ничего не найдено)
     */
    public function query(string $sql): QueryResult;
    /**
     * Выполняет запросы, которые не возвращают данные (INSERT, UPDATE, DELETE, ...)
     * @param  string  $sql запрос
     * @return integer      количество измененых (добавленых) строк
     */
    public function execute(string $sql): int;
    /**
     * Подготавливает значение
     * @param  mixed $value значение, которое необходимо подготовить
     * @return string       значение, защищенное от SQL инъекции
     */
    public function escape($value): string;
    /**
     * Оборачивает значение в кавычки (квоты)
     * @param  string    $value имя столбца, таблицы, базы
     * @return string           значение обернутое в кавычки (квоты)
     */
    public function quote(string $value): string;
    /**
     * Начало транзакции
     */
    public function beginTransaction();
    /**
     * Фиксация транзации
     */
    public function commit();
    /**
     * Отказ изменений транзакции
     */
    public function rollBack();
    /**
     * ID последней добавленной записи
     * Возвращает строку, т.к. ID может превышать PHP_INT_MAX
     * @return string ID записи
     */
    public function getLastInsertId(): string;
}
