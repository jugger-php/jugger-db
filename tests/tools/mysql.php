<?php

use PHPUnit\Framework\TestCase;
use jugger\db\Query;
use jugger\db\ConnectionPool;

class MysqlTest extends TestCase
{
    public static $db;

    public function setUpBeforeClass()
    {
        $sql = "
        CREATE TABLE `post` (
            `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            `title` VARCHAR(100) NOT NULL,
            `content` TEXT
        )
        ";

        self::$db = ConnectionPool::get('mysql');
        self::$db->execute($sql);
    }

    public function testQuery()
    {
        
    }
}
