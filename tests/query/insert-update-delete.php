<?php

use PHPUnit\Framework\TestCase;
use jugger\db\Query;
use jugger\db\QueryBuilder;
use jugger\db\ConnectionPool;

class InsertUpdateDeleteTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        $sql = "CREATE TABLE `t1` ( `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, `name` TEXT, `content` TEXT, `update_time` INT )";
        ConnectionPool::get('default')->execute($sql);
    }

    public static function tearDownAfterClass()
    {
        ConnectionPool::get('default')->execute("DROP TABLE `t1`");
    }

    public function testInsert()
    {
        $values = [
            'name' => 'name_val',
            'content' => 'content_val',
            'update_time' => 1400000000,
        ];
        /*
         * test SQL
         */
        $command = QueryBuilder::insert("t1", $values);
        $this->assertEquals(
            $command->getSql(),
            "INSERT INTO `t1`(`name`,`content`,`update_time`) VALUES('name_val','content_val','1400000000')"
        );
        $this->assertEquals($command->execute(), 1);
        /*
         * test fetch values
         */
        $row = (new Query())->from('t1')->one();
        foreach ($values as $column => $value) {
            $this->assertEquals($row[$column], $value);
        }
    }

    /**
     * @depends testInsert
     */
    public function testUpdate()
    {
        $values = [
            'name' => 'new name',
            'content' => 'new content',
        ];
        $where = [
            '!id' => null,
        ];
        $row = (new Query())->from('t1')
            ->where($where)
            ->one();
        /*
         * test SQL
         */
        $command = QueryBuilder::update("t1", $values, $where);
        $this->assertEquals(
            $command->getSql(),
            "UPDATE `t1` SET `name` = 'new name', `content` = 'new content'  WHERE `id` IS NOT NULL"
        );
        $this->assertEquals($command->execute(), 1);
        /*
         * test fetch
         */
        $row = (new Query())->from('t1')
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
        $row = (new Query())->from('t1')->one();
        $rowId = $row['id'];
        $where = ['id' => $rowId];
        /*
         * test SQL
         */
        $command = QueryBuilder::delete("t1", $where);
        $this->assertEquals(
            $command->getSql(),
            "DELETE FROM `t1`  WHERE `id` = '{$rowId}'"
        );
        $this->assertEquals($command->execute(), 1);
        /*
         * test working delete
         */
        $row = (new Query())->from('t1')
            ->where($where)
            ->one();
        $this->assertEmpty($row);
    }
}
