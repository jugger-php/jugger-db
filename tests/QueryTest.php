<?php

use PHPUnit\Framework\TestCase;
use jugger\db\Query;

class QueryTest extends TestCase
{
    public function testFirst()
    {
        $q = new Query();
        $sql = $q->select('*')->from('t')->build();

        $this->assertEquals("SELECT * FROM t", $sql);
    }
}
