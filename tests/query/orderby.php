<?php

use PHPUnit\Framework\TestCase;
use jugger\db\Query;

class OrderByTest extends TestCase
{
    public function db()
    {
        return Di::$pool['default'];
    }

    /**
     * @dataProvider dataProvider
     */
    public function test($sql, $params)
    {
        $q = (new Query($this->db()))->from('t1')->orderBy($params);
        $this->assertEquals($sql, $q->build());
    }

    public function dataProvider()
    {
        return [
            [
                "SELECT * FROM t1 ORDER BY id ASC, name DESC",
                "id ASC, name DESC"
            ],
            [
                "SELECT * FROM t1 ORDER BY  `id` ASC,  `name` DESC",
                [
                    'id' => 'ASC',
                    'name' => 'DESC',
                ]
            ],
            [
                "SELECT * FROM t1 ORDER BY  id ASC,  RAND()",
                [
                    'id ASC',
                    'RAND()'
                ]
            ],
        ];
    }
}
