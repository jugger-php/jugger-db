<?php

use PHPUnit\Framework\TestCase;
use jugger\db\Query;

class SelectFromTest extends TestCase
{
    /**
     * @dataProvider selectProvider
     */
    public function testSelect($select, $sql)
    {
        $q = (new Query())->select($select)->from('t');
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
                "'col1', 'col2'",
            ],
            [
                ["c1" => "col1"],
                "'col1' AS 'c1'",
            ],
            [
                ["c1" => (new Query())->from('t2')],
                "(SELECT * FROM t2) AS 'c1'",
            ],
        ];
    }

    /**
     * @dataProvider fromProvider
     */
    public function testFrom($from, $sql)
    {
        $q = (new Query())->from($from);
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
                "'t1', 't2'",
            ],
            [
                ["t1" => "table1"],
                "'table1' AS 't1'",
            ],
            [
                ["t1" => (new Query())->from('t2')],
                "(SELECT * FROM t2) AS 't1'",
            ],
        ];
    }
}
