<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/25
 * Time: 14:02
 */

namespace SimpleEs\lib;


class Dsl
{
    protected static $instance = null;
    protected $index = null;
    protected $type = '_doc';
    protected $curd = null;

    protected $options = [];

    protected $params = [];


    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (!(self::$instance instanceof self)){
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function setOption($key,$value)
    {
        $this->options[$key][] = $value;
    }

    public function build($curd)
    {
        $this->options['index'] = $this->index;
        $this->options['type'] = $this->type;
        $this->curd = $curd;
        $this->params = Build::run($this->options,$curd);
        $this->options = [];
        return $this->params;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function setIndex($index)
    {
        $this->index = $index;
    }
}