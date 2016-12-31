<?php

use PHPUnit\Framework\TestCase;
use jugger\db\ConnectionPool;
use jugger\db\Query;
use jugger\db\QueryResult;

class ConnectionTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        $sql = "CREATE TABLE `t2` (`id` INTEGER, `name` TEXT)";
        Di::$pool['default']->execute($sql);
    }

    public static function tearDownAfterClass()
    {
        Di::$pool['default']->execute("DROP TABLE `t2`");
    }

    public function testExecuteDb()
    {
        $db = Di::$pool['default'];
        $sql = "INSERT INTO `t2` VALUES(1, 'value')";
        $this->assertTrue($db->execute($sql) == 1);
    }

    /**
     * @depends testExecuteDb
     */
    public function testQuery()
    {
        $result = Di::$pool['default']->query("SELECT id, name FROM t2");
        $this->assertInstanceOf(QueryResult::class, $result);

        $row = $result->fetch();
        $this->assertEquals($row['id'], 1);
        $this->assertEquals($row['name'], 'value');
    }

    public function testQuote()
    {
        $db = Di::$pool['default'];
        $data = [
            "keyword" => "`keyword`",
            "table_name.column_name" => "`table_name`.`column_name`",
        ];
        foreach ($data as $value => $etalon) {
            $this->assertEquals($etalon, $db->quote($value));
        }
    }
}
