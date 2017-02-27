<?php

use PHPUnit\Framework\TestCase;
use jugger\db\Query;
use jugger\db\Expression;

class SelectFromTest extends TestCase
{
    public function db()
    {
        return Di::$pool['default'];
    }

    public function testDistinct()
    {
        $q = (new Query($this->db()))->distinct()->from('t');

        $this->assertEquals(
            $q->build(),
            'SELECT DISTINCT * FROM t'
        );
    }

    /**
     * Проверка генерации блока SELECT
     * @dataProvider selectProvider
     */
    public function testSelect($select, $sql)
    {
        $q = (new Query($this->db()))->select($select)->from('t');
        $this->assertEquals(
            $q->build(),
            'SELECT '. $sql .' FROM t'
        );
    }

    public function selectProvider()
    {
        return [
            [
                "*",
                "*",
            ],
            [
                "col1, col2",
                "col1, col2",
            ],
            [
                ["col1", "col2"],
                "`col1`, `col2`",
            ],
            [
                ["col1", new Expression("`col2`")],
                "`col1`, `col2`",
            ],
            [
                ["c1" => "col1"],
                "`col1` AS `c1`",
            ],
            [
                ["c1" => new Expression("(SELECT * FROM t2)")],
                "(SELECT * FROM t2) AS `c1`",
            ],
            [
                ["c1" => (new Query($this->db()))->from('t2')],
                "(SELECT * FROM t2) AS `c1`",
            ],
        ];
    }

    /**
     * Проверка генерации блока FROM
     * @dataProvider fromProvider
     */
    public function testFrom($from, $sql)
    {
        $q = (new Query($this->db()))->from($from);
        $this->assertEquals(
            $q->build(),
            'SELECT * FROM '.$sql
        );
    }

    public function fromProvider()
    {
        return [
            [
                "t1",
                "t1",
            ],
            [
                ["t1", "t2"],
                "`t1`, `t2`",
            ],
            [
                ["t1" => "table1"],
                "`table1` AS `t1`",
            ],
            [
                ["t1" => new Expression("(SELECT * FROM t2)")],
                "(SELECT * FROM t2) AS `t1`",
            ],
            [
                ["t1" => (new Query($this->db()))->from('t2')],
                "(SELECT * FROM t2) AS `t1`",
            ],
        ];
    }
}
