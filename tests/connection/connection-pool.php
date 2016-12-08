<?php

use PHPUnit\Framework\TestCase;
use jugger\db\ConnectionPool;

class ConnectionPoolTest extends TestCase
{
    public function testInit()
    {
        $connections = [
            'default' => [
                'class' => 'jugger\db\driver\PdoConnection',
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

        ConnectionPool::getInstance()->init($connections);

        $connectionList = ConnectionPool::getInstance()->getConnections();
        foreach ($connections as $name => $data) {
            $this->assertEmpty(array_diff($data, $connectionList[$name]));
        }
    }

    /**
     * @depends testInit
     */
    public function testGetter()
    {
        ConnectionPool::getInstance()->init([
            'con1' => [
                'class' => 'jugger\db\driver\PdoConnection',
                'dsn' => 'sqlite::memory:',
            ],
            'connection2' => [
                'class' => 'jugger\db\driver\PdoConnection',
                'dsn' => 'mysql:localhost;dbname=test',
                'username' => 'root',
                'password' => '',
            ],
        ]);

        $obj1 = ConnectionPool::get('con1');
        $obj2 = ConnectionPool::getInstance()['con1'];

        $this->assertNotNull($obj1);
        $this->assertNotNull($obj2);

        $this->assertEquals($obj1, $obj2);

        $obj3 = ConnectionPool::get('connection2');
        $obj4 = ConnectionPool::get('not found connection');

        $this->assertNotEquals($obj1, $obj3);
        $this->assertNull($obj4);
    }
}
