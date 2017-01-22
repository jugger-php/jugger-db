<?php

use PHPUnit\Framework\TestCase;
use jugger\db\ConnectionPool;
use jugger\db\Query;
use jugger\db\QueryResult;

class ConnectionTest extends TestCase
{
    public function dataProvider()
    {
        $sqlite = Di::$pool['default'];
        $sqlite->execute("DROP TABLE IF EXISTS `t2`");
        $sqlite->execute("CREATE TABLE IF NOT EXISTS `t2` (`id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, `name` TEXT)");

        $mysql = Di::$pool['mysql'];
        $mysql->execute("DROP TABLE IF EXISTS `t2`");
        $mysql->execute("CREATE  TABLE IF NOT EXISTS `t2` (`id` INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT, `name` TEXT)");

        return [
            [$sqlite],
            [$mysql],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testExecuteDb($db)
    {
        $sql = "INSERT INTO `t2` VALUES(1, 'value')";
        $this->assertTrue($db->execute($sql) == 1);

        $db->execute("INSERT INTO `t2`(name) VALUES('value2')");
        $db->execute("INSERT INTO `t2`(name) VALUES('value3')");
        $db->execute("INSERT INTO `t2`(name) VALUES('value4')");
        $db->execute("INSERT INTO `t2`(name) VALUES('value5')");

        $sql = "DELETE FROM t2 WHERE id > 3";
        $rows = $db->execute($sql);
        $this->assertTrue($rows == 2);
    }

    /**
     * @dataProvider dataProvider
     * @depends testExecuteDb
     */
    public function testQuery($db)
    {
        $result = $db->query("SELECT id, name FROM t2");
        $this->assertInstanceOf(QueryResult::class, $result);

        $row = $result->fetch();
        $this->assertEquals($row['id'], 1);
        $this->assertEquals($row['name'], 'value');

        $result = $db->query("SELECT id, name FROM t2");
        $rows = $result->fetchAll();
        $this->assertEquals($result->count(), 3);
        $this->assertEquals(count($rows), 3);
    }

    /**
     * @dataProvider dataProvider
     * @depends testQuery
     */
    public function testQuote($db)
    {
        $data = [
            "keyword" => "`keyword`",
            "table_name.column_name" => "`table_name`.`column_name`",
        ];
        foreach ($data as $value => $etalon) {
            $this->assertEquals($etalon, $db->quote($value));
        }
    }

    /**
     * @dataProvider dataProvider
     * @depends testQuote
     */
    public function testEscape($db)
    {
        $this->assertEquals(
            "LIKE '%\' AND id LIKE \'1%'",
            "LIKE '%". $db->escape("' AND id LIKE '1") ."%'"
        );
    }

    /**
     * @dataProvider dataProvider
     * @depends testEscape
     */
    public function testAcid($db)
    {
        // commit
        $db->beginTransaction();
        $db->execute("INSERT INTO t2(name) VALUES('value2')");

        $rowId = $db->getLastInsertId();
        $db->commit();

        $this->assertEquals($rowId, 6);

        $row = $db->query("SELECT id, name FROM t2 WHERE id = {$rowId}")->fetch();
        $this->assertEquals($row['name'], 'value2');

        // rollBack
        $db->beginTransaction();
        $db->execute("INSERT INTO t2(name) VALUES('value3')");

        $rowId = $db->getLastInsertId();
        $db->rollBack();

        $this->assertEquals($rowId, 7);

        $row = $db->query("SELECT id, name FROM t2 WHERE id = {$rowId}")->fetch();
        $this->assertNull($row);

        $row = $db->query("SELECT id, name FROM t2 ORDER BY id DESC")->fetch();
        $this->assertEquals($row['id'], 6);
    }

    /**
     * @dataProvider dataProvider
     * @depends testAcid
     */
    public function testDrop($db)
    {
        $db->execute("DROP TABLE IF EXISTS `t2`");
    }
}
