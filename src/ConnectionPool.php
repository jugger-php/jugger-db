<?php

namespace jugger\db;

use ArrayAccess;
use jugger\base\Singleton;
use jugger\base\Configurator;
use jugger\base\ArrayAccessTrait;

class ConnectionPool extends Singleton implements ArrayAccess
{
    use ArrayAccessTrait;

    protected $connections = [];
    protected $connectionsCache = [];

    public function init(array $connections)
    {
        $this->connections = $connections;
    }

    public function getConnections()
    {
        return $this->connections;
    }

    public function __get($name)
    {
        $cache = $this->connectionsCache[$name] ?? null;
        if ($cache) {
            return $cache;
        }

        $config = $this->connections[$name] ?? null;
        if (empty($config)) {
            return null;
        }
        else {
            $class = $config['class'];
            unset($config['class']);
            $object = new $class();
            Configurator::setValues($object, $config);

            $this->connectionsCache[$name] = $object;
        }
        return $this->connectionsCache[$name];
    }

    public static function get($name)
    {
        return self::getInstance()[$name];
    }
}
