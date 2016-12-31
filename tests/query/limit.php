<?php

use PHPUnit\Framework\TestCase;
use jugger\db\Query;

class LimitTest extends TestCase
{
    public function db()
    {
        return Di::$pool['default'];
    }

    /**
     * @dataProvider dataProvider
     */
    public function test($sql, array $params)
    {
        list($limit, $offset) = $params;
        $q = (new Query($this->db()))->from('t1');
        if ($offset) {
            $q->limit($limit, $offset);
        }
        else {
            $q->limit($limit);
        }

        $this->assertEquals($sql, $q->build());
    }

    public function dataProvider()
    {
        return [
            [
                "SELECT * FROM t1 LIMIT 1",
                [1, null]
            ],
            [
                "SELECT * FROM t1 LIMIT 100, 1",
                [1,100]
            ],
        ];
    }
}
