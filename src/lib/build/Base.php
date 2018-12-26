<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/24
 * Time: 17:36
 */

namespace SimpleEs\lib\build;


abstract class Base
{
    private static $instance = null;

    protected $params = [];

    private function __construct(){}

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    public static function getInstance()
    {
        if (!(self::$instance instanceof self)){
            self::$instance = new static();
        }

        self::$instance->params = [];

        return self::$instance;
    }
}