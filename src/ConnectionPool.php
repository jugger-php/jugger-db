<?php

namespace jugger\db;

use jugger\base\Configurator;
use jugger\base\ArrayAccessTrait;

class ConnectionPool implements \ArrayAccess
{
    use ArrayAccessTrait;

    protected $connections = [];
    protected $connectionsCache = [];

    public function __construct(array $connections = [])
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
}
