<?php

use PHPUnit\Framework\TestCase;
use jugger\db\Query;

include_once __DIR__ .'/bootstrap.php';

class QueryTest extends TestCase
{
    public function testFirst()
    {
        $q = new Query();
        $sql = $q->select('*')->from('t')->build();

        $this->assertEquals("SELECT * FROM t", $sql);
    }
}
