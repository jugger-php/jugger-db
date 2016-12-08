<?php

use PHPUnit\Framework\TestCase;
use jugger\db\ConnectionPool;
use jugger\db\tools\MysqlTableInfo;

class TableInfoMysqlTest extends TestCase
{
    public static $db;

    public static function setUpBeforeClass()
    {
        $sql = "
        CREATE TABLE `post` (
            `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            `title` VARCHAR(100) NOT NULL,
            `content` TEXT
        )
        ";

        ConnectionPool::getInstance()->init([
            'default' => [
                'class' => 'jugger\db\driver\PdoConnection',
                'dsn' => 'sqlite::memory:',
            ],
            'mysql' => [
                'class' => 'jugger\db\driver\PdoConnection',
                'dsn' => 'mysql:localhost;dbname=test',
                'username' => 'root',
                'password' => '',
            ],
        ]);

        self::$db = ConnectionPool::get('mysql');
        self::$db->execute($sql);
    }

    public function testTableInfo()
    {
        $tableInfo = new MysqlTableInfo('post', self::$db);

        var_dump($tableInfo->getColumns());
    }
}
