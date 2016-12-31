<?php

use PHPUnit\Framework\TestCase;
use jugger\db\Query;

class HavingTest extends TestCase
{
    public function db()
    {
        return Di::$pool['default'];
    }

    /**
     *
     * @dataProvider dataProvider
     */
    public function test($sql, $params)
    {
        $q = (new Query($this->db()))->from('t1')->having($params);
        $this->assertEquals($sql, $q->build());
    }

    public function dataProvider()
    {
        return [
            [
                "SELECT * FROM t1 HAVING COUNT(*) > 123",
                "COUNT(*) > 123"
            ],
        ];
    }
}
