<?php

namespace Mashvp;

abstract class SingletonClass
{
    final public static function instance()
    {
        static $instance = null;

        if ($instance === null) {
            $instance = new static();
        }

        return $instance;
    }

    protected function __construct()
    {
    }

    final protected function __clone()
    {
    }
}
