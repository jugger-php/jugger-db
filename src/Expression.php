<?php

namespace jugger\db;

class Expression
{
    protected $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function __toString()
    {
        return $this->getValue();
    }

    public function getValue()
    {
        return $this->value;
    }
}
