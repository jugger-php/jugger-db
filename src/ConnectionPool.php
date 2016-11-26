<?php

namespace jugger\db;

use ArrayAccess;
use jugger\base\Singleton;
use jugger\base\Configurator;
use jugger\base\ArrayAccessTrait;

class ConnectionPool extends Singleton implements ArrayAccess
{
    use ArrayAccessTrait;

    protected $connectionList = [];

    public function init(array $connectionList)
    {
        $this->connectionList = $connectionList;
    }

    public function __get($name)
    {
        $data = $this->connectionList[$name] ?? null;
        if (empty($data)) {
            return null;
        }
        elseif (is_array($data)) {
            $class = $data['class'];
            unset($data['class']);
            $object = new $class();
            Configurator::setValues($object, $data);

            $this->connectionList[$name] = $object;
        }
        return $this->connectionList[$name];
    }

    public static function get($name)
    {
        return self::getInstance()[$name];
    }
}
