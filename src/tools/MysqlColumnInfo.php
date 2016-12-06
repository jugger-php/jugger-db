<?php

namespace jugger\db\tools;

class MysqlColumnInfo implements ColumnInfoInterface
{
    public $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getName(): string
    {

    }

    public function getType(): array
    {

    }

    public function getKey(): int
    {

    }

    public function getIsNull(): bool
    {

    }

    public function getDefault(): string
    {

    }

    public function getOther(): array
    {

    }
}
