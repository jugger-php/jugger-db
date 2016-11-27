<?php

use PHPUnit\Framework\TestCase;
use jugger\db\ConnectionPool;
use jugger\db\Query;
use jugger\db\QueryResult;

class ConnectionTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        ConnectionPool::getInstance()->init([
            'default' => [
                'class' => 'jugger\db\pdo\PdoConnection',
                'dsn' => 'sqlite::memory:',
            ]
        ]);

        $sql = "CREATE TABLE `t2` (`id` INTEGER, `name` TEXT)";
        ConnectionPool::get('default')->execute($sql);
    }

    public static function tearDownAfterClass()
    {
        ConnectionPool::get('default')->execute("DROP TABLE `t2`");
    }

    public function testExecute()
    {
        $db = ConnectionPool::get('default');
        $sql = "INSERT INTO `t2` VALUES(1, 'value')";

        $this->assertEquals($db->execute($sql), 1);
    }

    /**
     * @depends testExecute
     */
    public function testQuery()
    {
        $result = ConnectionPool::get('default')->query("SELECT id, name FROM t2");
        $this->assertInstanceOf(QueryResult::class, $result);

        $row = $result->fetch();
        $this->assertEquals($row['id'], 1);
        $this->assertEquals($row['name'], 'value');
    }

    public function testQuote()
    {
        $db = ConnectionPool::get('default');
        $data = [
            "keyword" => "`keyword`",
            "table_name.column_name" => "`table_name`.`column_name`",
        ];
        foreach ($data as $value => $etalon) {
            $this->assertEquals($etalon, $db->quote($value));
        }
    }
}
