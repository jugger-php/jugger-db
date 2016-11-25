<?php

use PHPUnit\Framework\TestCase;
use jugger\db\ConnectionPool;

class PublicConnectionPool extends ConnectionPool
{
    public function getConnectionList()
    {
        return $this->connectionList;
    }
}

class ConnectionPoolTest extends TestCase
{
    public function testInit($table, $on, $sql)
    {
        $connections = [
            'default' => [
                'class' => 'jugger\db\pdo\PdoConnection',
                'dsn' => 'sqlite::memory:',
            ],
            'connection1' => [
                'class' => 'connection\class',
                'param1' => 'value',
                'param2' => 'value',
            ],
            'connection2' => [
                'class' => 'connection\class',
                'param1' => 'value',
                'param2' => 'value',
            ],
            'connection3' => [
                'class' => 'connection\class',
                'param1' => 'value',
                'param2' => 'value',
                'param3' => 'value',
            ],
        ];

        PublicConnectionPool::init($connections);

        $connectionList = PublicConnectionPool::getInstance()->getConnectionList();
        foreach ($connection as $name => $data) {
            $this->assertEmpty(array_diff($data, $connectionList[$name]));
        }
    }

    /**
     * @depends testInit
     */
    public function testGetter()
    {
        ConnectionPool::init([
            'con1' => [
                'class' => 'jugger\db\pdo\PdoConnection',
                'dsn' => 'sqlite::memory:',
            ]
        ]);

        $obj1 = ConnectionPool::get('con1');
        $obj2 = ConnectionPool::getInstance()['con1'];

        $this->assertFalse(is_null($obj1));
        $this->assertFalse(is_null($obj2));

        $this->assertEquals($obj1, $obj2);
    }
}
