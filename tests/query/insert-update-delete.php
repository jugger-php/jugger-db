<?php

use PHPUnit\Framework\TestCase;
use jugger\db\Query;
use jugger\db\QueryBuilder;
use jugger\db\ConnectionPool;

class InsertUpdateDeleteTest extends TestCase
{
    public $db;

    public function setUp()
    {
        $this->db = ConnectionPool::get('default');
        $this->db->execute("CREATE TABLE 't1' ( 'id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 'name' TEXT, 'content' TEXT, 'update_time' INT );");
    }

    public function tearDown()
    {
        $this->db->execute("DROP TABLE t1");
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
            "INSERT INTO 't1'('name','content','update_time') VALUES('name_val','content_val','1400000000')"
        );
        /*
         * test return value
         */
        $rowId = $command->execute();
        $row = (new Query())->from('t1')
            ->query()
            ->fetch();
        $this->assertTrue($row['id'] == $rowId);
        /*
         * test fetch values
         */
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
            '>id' => 0,
        ];
        /*
         * test SQL
         */
        $command = QueryBuilder::update("t1", $values, $where);
        $this->assertEquals(
            $command->getSql(),
            "UPDATE 't1' SET 'name' = 'new name', 'content' = 'new content'  WHERE 'id'>'0'"
        );
        /*
         * test return value
         */
        $rowId = $command->execute();
        $row = (new Query())->from('t1')
            ->where($where)
            ->query()
            ->fetch();

        $this->assertTrue($row['id'] == $rowId);
        /*
         * test fetch
         */
        foreach ($values as $column => $value) {
            $this->assertEquals($row[$column], $value);
        }
    }

    /**
     * @depends testUpdate
     */
    public function testDelete()
    {
        $row = (new Query())->from('t1')
            ->query()
            ->fetch();
        $where = ['id' => $rowId];
        /*
         * test SQL
         */
        $command = QueryBuilder::delete("t1", $where);
        $this->assertEquals(
            $command->getSql(),
            "DELETE FROM 't1' WHERE 'id' = {$rowId}"
        );
        /*
         * test ret value
         */
        $rowId = $row['id'];
        $retId = $command->execute();
        $this->assertEquals($retId, $rowId);
        /*
         * test working delete
         */
        $row = (new Query())->from('t1')
            ->where($where)
            ->query()
            ->fetch();
         $this->assertEmpty($row);
    }
}
