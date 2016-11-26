<?php

use PHPUnit\Framework\TestCase;
use jugger\db\Query;

class JoinTest extends TestCase
{
    /**
     * Тест эквивалентности различных типов связи
     */
    public function testTypes()
    {
        $t2 = 't2';
        $on = 't.id = t2.id';

        $q11 = (new Query())->from('t')->join('LEFT', $t2, $on);
        $q12 = (new Query())->from('t')->leftJoin($t2, $on);
        $this->assertEquals(
            $q11->build(),
            $q12->build(),
            "SELECT * FROM t LEFT JOIN t2 ON {$on}"
        );

        $q21 = (new Query())->from('t')->join('RIGHT', $t2, $on);
        $q22 = (new Query())->from('t')->rightJoin($t2, $on);
        $this->assertEquals(
            $q21->build(),
            $q22->build(),
            "SELECT * FROM t RIGHT JOIN t2 ON {$on}"
        );

        $q31 = (new Query())->from('t')->join('INNER', $t2, $on);
        $q32 = (new Query())->from('t')->innerJoin($t2, $on);
        $this->assertEquals(
            $q31->build(),
            $q32->build(),
            "SELECT * FROM t INNER JOIN t2 ON {$on}"
        );

        return true;
    }

    /**
     * Тест множественного использования
     */
    public function testMany()
    {
        $query = (new Query())->from('t');
        $query->leftJoin('t2', 't.id = t2.tid');
        $query->rightJoin('t3', 't.id = t3.tid');
        $query->innerJoin('t4', 't.id = t4.tid');

        $this->assertEquals(
            $query->build(),
            implode(" ", [
                "SELECT *",
                "FROM t",
                "LEFT JOIN t2 ON t.id = t2.tid ",
                "RIGHT JOIN t3 ON t.id = t3.tid ",
                "INNER JOIN t4 ON t.id = t4.tid ",
            ])
        );
    }

    /**
     * Тест различных вариантов формирования аргумента 'table' (аналог FROM)
     * @depends testTypes
     * @dataProvider dataProvider
     */
    public function testJoin($table, $on, $sql)
    {
        $q = (new Query())->from('t')->innerJoin($table, $on);

        $this->assertEquals(
            trim($q->build()),
            "SELECT * FROM t INNER JOIN {$sql}"
        );
    }

    public function dataProvider()
    {
        $on = "t.id = t2.id";
        return [
            [
                't2',
                $on,
                "t2 ON {$on}"
            ],
            [
                ['t2'],
                $on,
                "`t2` ON {$on}"
            ],
            [
                ['t2' => 'table2'],
                $on,
                "`table2` AS `t2` ON {$on}"
            ],
            [
                ['t2' => (new Query())->from('t3')],
                $on,
                "(SELECT * FROM t3) AS `t2` ON {$on}"
            ],
        ];
    }
}
