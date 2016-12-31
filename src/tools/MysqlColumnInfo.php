<?php

namespace jugger\db\tools;

class MysqlColumnInfo implements ColumnInfoInterface
{
    public $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getName(): string
    {
        return $this->data['Field'];
    }

    public function getType(): string
    {
        $type = $this->data['Type'];

        if (preg_match('/^int\(.*/', $type)) {
            return self::TYPE_INT;
        }
        if (preg_match('/^char\(.*/', $type)) {
            return self::TYPE_TEXT;
        }
        if (preg_match('/^varchar\(.*/', $type)) {
            return self::TYPE_TEXT;
        }

        switch ($type) {
            case 'text':
                return self::TYPE_TEXT;
            case 'blob':
                return self::TYPE_BLOB;
            case 'value':
                return self::TYPE_FLOAT;
            case 'date':
            case 'timestamp':
                return self::TYPE_DATETIME;
            default:
                return self::TYPE_TEXT;
        }
    }

    public function getSize(): int
    {
        $type = $this->data['Type'];
        $size = 0;
        if (preg_match('/.+\((\d+).+$/', $type, $m)) {
            $size = (int) $m[1];
        }

        return $size;
    }

    public function getKey(): int
    {
        switch ($this->data['Key']) {
            case 'PRI':
                return self::KEY_PRIMARY;
            case 'UNI':
                return self::KEY_UNIQUE;
            default:
                return -1;
        }
    }

    public function getIsNull(): bool
    {
        return $this->data['Null'] == "YES";
    }

    public function getDefault(): string
    {
        return (string) $this->data['Default'];
    }

    public function getOther(): string
    {
        return $this->data["Extra"];
    }
}
