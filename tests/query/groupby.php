<?php

use PHPUnit\Framework\TestCase;
use jugger\db\Query;

class GroupByTest extends TestCase
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
        $q = (new Query($this->db()))->from('t1')->groupBy($params);
        $this->assertEquals($sql, $q->build());
    }

    public function dataProvider()
    {
        return [
            [
                "SELECT * FROM t1 GROUP BY col1, col2, col3",
                "col1, col2, col3"
            ],
            [
                "SELECT * FROM t1 GROUP BY `col1`, `col2`, `col3`",
                ["col1", "col2", "col3"]
            ],
        ];
    }
}
