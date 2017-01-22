<?php

use PHPUnit\Framework\TestCase;
use jugger\db\Query;

class WhereTest extends TestCase
{
    public function db()
    {
        return Di::$pool['default'];
    }

    /**
     * Проверяет равнозначность различных вариантов записи
     * @dataProvider equivalentProvider
     */
    public function testEquivalent(... $values)
    {
        $sqls = [];
        foreach ($values as $value) {
            $sqls[] = (new Query($this->db()))->from('t')
                ->where($value)
                ->build();
        }

        for ($i = 0; isset($sqls[$i+1]); $i++) {
            $this->assertEquals(
                $sqls[$i],
                $sqls[$i+1]
            );
        }
    }

    public function equivalentProvider()
    {
        $number = 123;
        $array = [1,2,3];
        $string = "SELECT * FROM t";
        $query = (new Query($this->db()))->from('t');

        return [
            [
                ['id' => $number],
                ['=id' => $number]
            ],
            [
                ['id' => $array],
                ['=id' => $array],
                ['@id' => $array],
            ],
            [
                ['id' => $query],
                ['=id' => $query],
                ['@id' => $query],
            ],
            [
                ['@id' => $string],
                ['@id' => $query],
            ],
            [
                ['!id' => $number],
                ['!=id' => $number],
                ['<>id' => $number],
            ],
            [
                ['!id' => $query],
                ['!=id' => $query],
                ['<>id' => $query],
                ['!@id' => $query],
            ],
        ];
    }

    /**
     * Проверяет все доспустимые операторы
     * @dataProvider dataProvider
     */
    public function testOperators($value, $sql)
    {
        $q = (new Query($this->db()))->from('t')->where($value);

        $this->assertEquals(
            $q->build(),
            "SELECT * FROM t WHERE {$sql}"
        );
    }

    public function dataProvider()
    {
        return [
            [
                't.id = t2.id',
                't.id = t2.id'
            ],
            [
                [
                    ['id' => '123'],
                    ['id' => null],
                    ['id' => true],
                ],
                "((`id` = '123') AND (`id` IS  NULL) AND (`id` IS  TRUE))"
            ],
            [
                [
                    '=col1' => 123,
                    '=col2' => null,
                    '=col3' => true,
                    '=col4' => [1,'test',3.14],
                    '=col5' => (new Query($this->db()))->from('t2'),
                ],
                "(`col1` = '123' AND `col2` IS  NULL AND `col3` IS  TRUE AND `col4` IN ('1', 'test', '3.14') AND `col5` IN (SELECT * FROM t2))"
            ],
            [
                [
                    '!col1' => 123,
                    '!=col2' => null,
                    '<>col3' => [1,'test',3.14],
                ],
                "(`col1` <> '123' AND `col2` IS NOT NULL AND `col3` NOT IN ('1', 'test', '3.14'))"
            ],
            [
                [
                    'col1' => [1,2,3],
                    '=col2' => [4,5,6],
                    '@col3' => (new Query($this->db()))->from('t2'),
                ],
                "(`col1` IN ('1', '2', '3') AND `col2` IN ('4', '5', '6') AND `col3` IN (SELECT * FROM t2))"
            ],
            [
                [
                    '!col1' => [1,2,3],
                    '!=col2' => [4,5,6],
                    '!@col3' => (new Query($this->db()))->from('t2'),
                ],
                "(`col1` NOT IN ('1', '2', '3') AND `col2` NOT IN ('4', '5', '6') AND `col3` NOT IN (SELECT * FROM t2))"
            ],
            [
                [
                    '><col1' => [1,50],
                    '>!<col2' => [50,100],
                ],
                "( `col1` BETWEEN 1 AND 50  AND  `col2` NOT BETWEEN 50 AND 100 )"
            ],
            [
                [
                    '%col1' => "str",
                    '%col2' => "%str",
                    '%col3' => "str%",
                    '%col4' => "%str%",
                    '%col5' => (new Query($this->db()))->from('t'),
                ],
                "(`col1` LIKE 'str' AND `col2` LIKE '%str' AND `col3` LIKE 'str%' AND `col4` LIKE '%str%' AND `col5` LIKE (SELECT * FROM t))"
            ],
            [
                [
                    '!%col1' => "str",
                    '!%col2' => "%str",
                    '!%col3' => "str%",
                    '!%col4' => "%str%",
                    '!%col5' => (new Query($this->db()))->from('t'),
                ],
                "(`col1` NOT LIKE 'str' AND `col2` NOT LIKE '%str' AND `col3` NOT LIKE 'str%' AND `col4` NOT LIKE '%str%' AND `col5` NOT LIKE (SELECT * FROM t))"
            ],
            [
                [
                    '>col1' => 1,
                    '>=col2' => 2,
                    '<col3' => 3,
                    '<=col4' => 4,
                ],
                "(`col1`>'1' AND `col2`>='2' AND `col3`<'3' AND `col4`<='4')"
            ],
        ];
    }

    /**
     * Проверяет логические конструкции и их вложенность
     */
    public function testLogic()
    {
        $q1 = (new Query($this->db()))->from('t');
        $q1->where([
            'or',
            [
                'and',
                'col1' => 123,
                'col2' => 123,
            ],
            [
                'col3' => 123,
            ],
        ]);
        $this->assertEquals(
            $q1->build(),
            "SELECT * FROM t WHERE ((`col1` = '123' AND `col2` = '123') OR (`col3` = '123'))"
        );


        $q2 = (new Query($this->db()))->from('t');
        $q2->where(['col1' => 123]);
        $q2->andWhere(['col2' => 123]);
        $q2->orWhere(['col3' => 123]);
        $this->assertEquals(
            $q2->build(),
            "SELECT * FROM t WHERE (((`col1` = '123')) AND (`col2` = '123')) OR (`col3` = '123')"
        );


        $q3 = (new Query($this->db()))->from('t');
        $q3->where([
            'col1' => 1,
            [
                'or',
                'col2' => 2,
                [
                    'and',
                    'col3' => 3,
                    [
                        'or',
                        'col4' => 4,
                        [
                            'col5' => 5,
                            'col6' => 6,
                        ],
                        'col7' => 7,
                    ],
                    'col8' => 8,
                ],
                'col9' => 9,
            ]
        ]);

        $this->assertEquals(
            $q3->build(),
            "SELECT * FROM t WHERE (`col1` = '1' AND (`col2` = '2' OR (`col3` = '3' AND (`col4` = '4' OR (`col5` = '5' AND `col6` = '6') OR `col7` = '7') AND `col8` = '8') OR `col9` = '9'))"
        );
    }
}
