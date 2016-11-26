<?php

use PHPUnit\Framework\TestCase;
use jugger\db\Query;

class GroupByTest extends TestCase
{

    /**
     *
     * @dataProvider dataProvider
     */
    public function test($sql, $params)
    {
        $q = (new Query())->from('t1')->groupBy($params);
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
