<?php

namespace jugger\db\tools;

interface TableInfoInterface
{
    public function getColumns(): array;
}
