<?php

use PHPUnit\Framework\TestCase;
use jugger\db\ConnectionPool;

class ConnectionPoolTest extends TestCase
{
    /**
     * @depends testInit
     */
    public function testGetter()
    {
        $pool = new ConnectionPool([
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

        $obj1 = $pool['con1'];
        $obj2 = $pool->con1;

        $this->assertNotNull($obj1);
        $this->assertEquals($obj1, $obj2);

        $obj3 = $pool->connection2;
        $obj4 = $pool['not found connection'];

        $this->assertNotEquals($obj1, $obj3);
        $this->assertNull($obj4);
    }
}
