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

    public function init(array $configList)
    {
        foreach ($configList as $name => $data) {
            $class = $data['class'];
            unset($data['class']);
            $object = new $class();
            Configurator::setValues($object, $data);

            $this->connectionList[$name] = $object;
        }
    }

    public function __get($name)
    {
        return $this->connectionList[$name] ?? null;
    }

    public static function get($name)
    {
        return self::getInstance()[$name];
    }
}
