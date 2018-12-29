<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/21
 * Time: 10:04
 */

function config($key)
{
    global $config;
    return $config[$key]??false;
}

/**
 * @param string $index
 * @return \SimpleEs\Es
 */
function es($index='0')
{
    return \SimpleEs\Es::getInstance(config('elasticsearch'),$index);
}

function dump($data)
{
    echo "<pre>";
    var_dump($data);
    echo "<pre>";
}