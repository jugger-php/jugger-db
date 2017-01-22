<?php

use PHPUnit\Framework\TestCase;
use jugger\db\Query;
use jugger\db\Command;
use jugger\db\ConnectionPool;

class InsertUpdateDeleteTest extends TestCase
{
    public function dataProvider()
    {
        $sqlite = Di::$pool['default'];
        $sqlite->execute("DROP TABLE IF EXISTS `t1`");
        $sqlite->execute("CREATE TABLE IF NOT EXISTS `t1` ( `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, `name` TEXT, `content` TEXT, `update_time` INT )");

        $mysql = Di::$pool['mysql'];
        $mysql->execute("DROP TABLE IF EXISTS `t1`");
        $mysql->execute("CREATE TABLE IF NOT EXISTS `t1` ( `id` INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL, `name` TEXT, `content` TEXT, `update_time` INT )");

        return [
            // [$sqlite], SQLite not working with escaping
            [$mysql],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testInsert($db)
    {
        $values = [
            'name' => 'name_val',
            'content' => "' AND \'1\" = 1",
            'update_time' => 1400000000,
        ];
        /*
         * test SQL
         */
        $command = (new Command($db))->insert("t1", $values);
        $this->assertEquals(
            $command->getSql(),
            "INSERT INTO `t1`(`name`,`content`,`update_time`) VALUES('name_val','\\' AND \\\\\\'1\\\" = 1','1400000000')"
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
     * @dataProvider dataProvider
     * @depends testInsert
     */
    public function testUpdate($db)
    {
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
     * @dataProvider dataProvider
     * @depends testUpdate
     */
    public function testDelete($db)
    {
        $row = (new Query($db))
            ->select('id')
            ->from('t1')
            ->one();

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

    /**
     * @dataProvider dataProvider
     * @depends testDelete
     */
    public function testDrop($db)
    {
        $db->execute("DROP TABLE IF EXISTS `t1`");
    }
}
