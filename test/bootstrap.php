<?php

namespace jugger\db;

// autoload src
spl_autoload_register(function($class) {
    if (strpos($class, __NAMESPACE__) !== 0) {
        return;
    }
    $class = substr($class, strlen(__NAMESPACE__) + 1);

    var_dump($class);

    $file = __DIR__ ."/../src/{$class}.php";
    if (file_exists($file)) {
        require $file;
    }
});
