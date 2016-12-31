<?php

namespace jugger\db\tools;

interface ColumnInfoInterface
{
    const KEY_PRIMARY = 1;
    const KEY_UNIQUE = 2;
    const KEY_INDEX = 3;

    const TYPE_INT = 'int';
    const TYPE_TEXT = 'text';
    const TYPE_BLOB = 'blob';
    const TYPE_FLOAT = 'float';
    const TYPE_DATETIME = 'datetime';

    /**
     * Имя столбца
     * @return string
     */
    public function getName(): string;
    /**
     * Тип
     * @return array массив, первый элемент 'тип', второй элемент 'размер'
     */
    public function getType(): array;
    /**
     * Индекс
     * @return [type] [description]
     */
    public function getKey(): int;
    /**
     * Флаг, может ли столбец иметь значение NULL
     * @return bool
     */
    public function getIsNull(): bool;
    /**
     * Значение по умолчанию
     * @return string если значения по умолчанию нет, то вернет NULL
     */
    public function getDefault(): string;
    /**
     * Другие параметры специфичные для каждоый базы данных
     * @return array список параметров
     */
    public function getOther(): array;
}
