<?php

use PHPUnit\Framework\TestCase;
use jugger\db\ConnectionPool;
use jugger\db\Query;

class PdoConnectionTest extends TestCase
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
        $row = (new Query())->from('t2')->where(['id' => 1])->one();

        $this->assertEquals($row['id'], 1);
        $this->assertEquals($row['name'], 'value');
    }
}
