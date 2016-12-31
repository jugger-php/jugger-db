<?php

use PHPUnit\Framework\TestCase;
use jugger\db\Query;
use jugger\db\Command;
use jugger\db\ConnectionPool;

class InsertUpdateDeleteTest extends TestCase
{
    public function db()
    {
        return Di::$pool['default'];
    }

    public static function setUpBeforeClass()
    {
        $sql = "CREATE TABLE `t1` ( `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, `name` TEXT, `content` TEXT, `update_time` INT )";
        Di::$pool['default']->execute($sql);
    }

    public static function tearDownAfterClass()
    {
        Di::$pool['default']->execute("DROP TABLE `t1`");
    }

    public function testInsert()
    {
        $db = $this->db();
        $values = [
            'name' => 'name_val',
            'content' => 'content_val',
            'update_time' => 1400000000,
        ];
        /*
         * test SQL
         */
        $command = (new Command($db))->insert("t1", $values);
        $this->assertEquals(
            $command->getSql(),
            "INSERT INTO `t1`(`name`,`content`,`update_time`) VALUES('name_val','content_val','1400000000')"
        );
        $this->assertEquals($command->execute(), 1);
        /*
         * test fetch values
         */
        $row = (new Query($db))->from('t1')->one();
        foreach ($values as $column => $value) {
            $this->assertEquals($row[$column], $value);
        }
    }

    /**
     * @depends testInsert
     */
    public function testUpdate()
    {
        $db = $this->db();
        $values = [
            'name' => 'new name',
            'content' => 'new content',
        ];
        $where = [
            '!id' => null,
        ];
        $row = (new Query($db))->from('t1')
            ->where($where)
            ->one();
        /*
         * test SQL
         */
        $command = (new Command($db))->update("t1", $values, $where);
        $this->assertEquals(
            $command->getSql(),
            "UPDATE `t1` SET `name` = 'new name', `content` = 'new content'  WHERE `id` IS NOT NULL"
        );
        $this->assertEquals($command->execute(), 1);
        /*
         * test fetch
         */
        $row = (new Query($db))->from('t1')
            ->where($where)
            ->one();
        foreach ($values as $column => $value) {
            $this->assertEquals($row[$column], $value);
        }
    }

    /**
     * @depends testUpdate
     */
    public function testDelete()
    {
        $db = $this->db();

        $row = (new Query($db))->from('t1')->one();
        $rowId = $row['id'];
        $where = ['id' => $rowId];
        /*
         * test SQL
         */
        $command = (new Command($db))->delete("t1", $where);
        $this->assertEquals(
            $command->getSql(),
            "DELETE FROM `t1`  WHERE `id` = '{$rowId}'"
        );
        $this->assertEquals($command->execute(), 1);
        /*
         * test working delete
         */
        $row = (new Query($db))->from('t1')
            ->where($where)
            ->one();
        $this->assertEmpty($row);
    }
}
