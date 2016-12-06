<?php

use jugger\db\ConnectionPool;

// composer vendor autoload
include __DIR__ .'/../../../autoload.php';

ConnectionPool::getInstance()->init([
    'default' => [
        'class' => 'jugger\db\pdo\PdoConnection',
        'dsn' => 'sqlite::memory:',
    ],
    'mysql' => [
        'class' => 'jugger\db\pdo\PdoConnection',
        'dsn' => 'mysql:localhost;dbname=test',
        'username' => 'root',
        'password' => '',
    ],
]);
