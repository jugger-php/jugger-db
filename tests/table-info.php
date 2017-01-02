<?php

use PHPUnit\Framework\TestCase;
use jugger\db\tools\MysqlTableInfo;
use jugger\db\driver\MysqliConnection;
use jugger\db\tools\ColumnInfoInterface;

class TableInfoTest extends TestCase
{
    public function db()
    {
        static $db;
        if (!$db) {
            $db = new MysqliConnection();
            $db->host = "localhost";
            $db->dbname = "test";
            $db->username = "root";
            $db->password = "";
        }
        return $db;
    }

    public function setUp()
    {
        $sql = "
        CREATE TABLE `test_table_info` (
            `id` INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
            `name` VARCHAR(200) NOT NULL UNIQUE,
            `content` TEXT,
            `image` BLOB,
            `date` DATE DEFAULT NULL,
            `datetime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
        ";
        $this->db()->execute($sql);
    }

    public function tearDown()
    {
        $this->db()->execute("DROP TABLE `test_table_info`");
    }

    public function testBase()
    {
        $db = $this->db();
        $tableInfo = new MysqlTableInfo('test_table_info', $db);
        $columns = $tableInfo->getColumns();

        $this->assertEquals($columns['id']->getSize(), 11);
        $this->assertEquals($columns['id']->getType(), ColumnInfoInterface::TYPE_INT);
        $this->assertEquals($columns['id']->getKey(), ColumnInfoInterface::KEY_PRIMARY);
        $this->assertEquals($columns['id']->getOther(), 'auto_increment');
        $this->assertTrue($columns['id']->getIsNull() === false);

        $this->assertEquals($columns['name']->getSize(), 200);
        $this->assertEquals($columns['name']->getType(), ColumnInfoInterface::TYPE_TEXT);
        $this->assertEquals($columns['name']->getKey(), ColumnInfoInterface::KEY_UNIQUE);
        $this->assertTrue($columns['name']->getIsNull() === false);

        $this->assertEquals($columns['content']->getType(), ColumnInfoInterface::TYPE_TEXT);
        $this->assertTrue($columns['content']->getIsNull() === true);

        $this->assertEquals($columns['image']->getType(), ColumnInfoInterface::TYPE_BLOB);
        $this->assertTrue($columns['image']->getIsNull() === true);

        $this->assertEquals($columns['date']->getType(), ColumnInfoInterface::TYPE_DATETIME);
        $this->assertTrue($columns['date']->getIsNull() === true);

        $this->assertEquals($columns['datetime']->getType(), ColumnInfoInterface::TYPE_DATETIME);
        $this->assertEquals($columns['datetime']->getDefault(), 'CURRENT_TIMESTAMP');
        $this->assertTrue($columns['datetime']->getIsNull() === false);

        /*
         all test
         */

        $this->assertEquals($columns['content']->getType(), ColumnInfoInterface::TYPE_TEXT);
        $this->assertEquals($columns['content']->getSize(), 0);
        $this->assertEquals($columns['content']->getKey(), -1);
        $this->assertEquals($columns['content']->getDefault(), "");
        $this->assertEquals($columns['content']->getOther(), "");
        $this->assertTrue($columns['content']->getIsNull() === true);
    }
}
