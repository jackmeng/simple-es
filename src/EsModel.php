<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/21
 * Time: 9:42
 */

namespace SimpleEs;


class EsModel
{
    public static function __callStatic($name, $arguments)
    {
        $index = strtolower(array_pop(explode('\\',get_called_class())));

        if (method_exists(Es::class,$name)){
            return Es::getInstance(config('elasticsearch'),$index)->$name(...$arguments);
        }

        return false;
    }
}