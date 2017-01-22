<?php

use jugger\db\ConnectionPool;

// composer vendor autoload
include __DIR__ .'/../../../autoload.php';

class Di
{
    public static $pool;
}

Di::$pool = new ConnectionPool([
    'default' => [
        'class' => 'jugger\db\driver\PdoConnection',
        'dsn' => 'sqlite::memory:',
    ],
    'mysql' => [
        'class' => 'jugger\db\driver\MysqliConnection',
        'host' => 'localhost',
        'dbname' => 'test',
        'username' => 'root',
        'password' => '',
    ],
]);
