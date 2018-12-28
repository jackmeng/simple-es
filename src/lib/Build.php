<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/21
 * Time: 17:53
 */

namespace SimpleEs\lib;


use SimpleEs\Es;
use SimpleEs\lib\build\Data;
use SimpleEs\lib\build\Where;

class Build
{

    private static $instance = null;

    protected $params = [];

    /**
     * 操作的方式
     * @var string c|ca|u|r|d
     */
    protected $curd = null;

    /**
     * 是否有查询操作
     * @var null
     */
    public $isQuery = null;


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

    /**
     * @param $options
     * @param $curd
     * @return array
     */
    public static function run(&$options,$curd)
    {
        $instance = self::getInstance();
        $instance->curd = $curd;
        $instance->isQuery(array_keys($options));

        foreach($options as $key=>$value){
            if (method_exists($instance,$key)){
                $instance->$key($value);
            }else{
                $method = $instance->getMethod($key);
                if (is_string($method)){
                    $instance->$method($key,$value);
                }
            }
        }
        $instance->params['client'] = [
            'ignore'=>404,
            'timeout' => 10,        // ten second timeout
            'connect_timeout' => 10
        ];

        return $instance->params;
    }

    public function getMethod($key)
    {
        if ($this->isBool($key)){
            return 'bool';
        }

        return false;
    }

    protected function isBool($option)
    {
        return in_array($option,['must','must_not','should','filter']);
    }

    protected function isQuery($keys)
    {
        $this->isQuery = (bool)array_intersect(['query','must','must_not','should','filter'],$keys);
        return $this->isQuery;
    }

    public function index($value)
    {
        $this->params['index'] = $value;
    }

    public function type($value)
    {
        $this->params['type'] = $value;
    }

    public function id($value)
    {
        $this->params['id'] = $value[0];
    }

    public function _source($value)
    {
        $this->params['_source'] = $value[0];
    }


    public function data($data)
    {
        $curd = $this->curd;

        $params = Data::getInstance()->$curd($data[0],$this->isQuery);

        $this->params = array_merge_recursive($this->params,$params);
    }

    protected function query($value)
    {
        $query = [];
        foreach ($value as $item){
            $query = array_merge_recursive($query,$item);
        }
        if (isset($this->params['query'])){
            $this->params['body']['query'] = array_merge_recursive($this->params['query'],$query);
        }else{
            $this->params['body']['query'] = $query;
        }

    }


    protected function bool($key,$value)
    {
        $this->params['body']['query']['bool'][$key] = $value;
    }


    public function sort($value)
    {
        $sort = [];
        foreach($value as $item){
            if (is_null($item['order']) && is_null($item['mode'])){
                $sort[] = $item['field'];
            }elseif(is_null($item['mode'])){
                $sort[] = [
                    $item['field']=>[
                        'order'=>$item['order']
                    ]
                ];
            }else{
                $sort[] =[
                    $item['field']=> [
                        'order'=>$item['order'],
                        'mode'=>$item['mode']
                    ]
                ];
            }
        }

        $this->params['body']['sort'] = $sort;
    }

    public function from($from)
    {
        $this->params['body']['from'] = $from[0];
    }

    public function size($size)
    {
        $this->params['body']['size'] = $size[0];
    }






}