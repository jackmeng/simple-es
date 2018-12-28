<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/21
 * Time: 9:42
 */

namespace SimpleEs;


use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use SimpleEs\lib\Dsl;

/**
 * Class Es
 * @method $this query($type,$field,$condition)
 * @method $this must($type,$field,$condition)
 * @method $this must_not($type,$field,$condition)
 * @method $this should($type,$field,$condition)
 * @method $this filter($type,$field,$condition)
 * @package SimpleEs
 */
class Es
{

    /**
     * 按index存储单例对象
     * @var array
     */
    protected static $instances = [];

    /**
     * elasticsear 官方类库对象
     * @var null
     */
    protected static $connect = null;

    /**
     * elasticsear 官方类库对象
     * @var Client
     */
    protected $client = null;

    /**
     * Dsl 类库
     * @var Dsl
     */
    protected $dsl = null;

    /**
     * es响应数据
     * @var null
     */
    protected $response = null;

    /**
     * 是否返回Dsl数组
     * @var bool
     */
    protected $fetch = false;

    private function __construct($client)
    {
        $this->client = $client;
        $this->dsl = Dsl::getInstance();
    }

    /**
     * 获取本类单例对象
     * @param $config
     * @param string $index
     * @return Es
     */
    public static function getInstance($config,$index='0')
    {
        if (!isset(self::$instances[$index]) || !(self::$instances[$index] instanceof self)){
            $client = self::connect($config);
            self::$instances[$index] = new self($client);
            if ($index != '0'){
                self::$instances[$index]->setIndex($index);
            }
        }

        return self::$instances[$index];
    }

    /**
     * 连接 elasticsearch
     * @param null $config
     * @return \Elasticsearch\Client|null
     */
    public static function connect($config=null)
    {
        if (is_null(self::$connect) && !is_null($config)){
            self::$connect = ClientBuilder::create()           // Instantiate a new ClientBuilder
            ->setHosts($config)      // Set the hosts
            ->setRetries(1)
            ->build();              // Build the client object
        }

       return self::$connect;
    }

    /**
     * 获取 elasticsearch 官方类库对象
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * 设置index
     * @param $index
     * @return $this
     */
    public function setIndex($index)
    {
        $this->dsl->setIndex($index);
        return $this;
    }

    /**
     * 指定要查询的字段
     * @param $source
     * @return $this
     */
    public function source($source)
    {
        if (is_string($source) && strpos($source,',')){
            $source = explode(',',$source);
        }

        $this->dsl->setOption('_source',(array)$source);

        return $this;
    }

    /**
     * 统一处理查询参数设置
     * @param $name
     * @param $arguments  [0]=>$type,[1]=>$field,[2]=>$condition
     *                  name=='query' 则 type: match|match_all|term|terms|range
     * @return $this
     */
    public function __call($name, $arguments)
    {
        if ($this->isQuery($name)){
            $this->dsl->setOption($name,[
                $arguments[0]=>[$arguments[1]=>$arguments[2]]
            ]);
        }

        return $this;
    }

    /**
     * 判断是否是查询操作
     * @param $name
     * @return bool
     */
    protected function isQuery($name)
    {
        return in_array($name,[
            'query','must','must_not','should','filter'
        ]);
    }


    /**
     * 设置排序参数
     * @param $field
     * @param null $order
     * @param null $mode
     * @return $this
     */
    public function sort($field,$order=null,$mode=null)
    {
        $this->dsl->setOption('sort',[
            'field'=>$field,
            'order' => $order,
            'mode'=>$mode
        ]);

        return $this;
    }


    /**
     * 指定开始的条数
     * @param int $from
     * @return $this
     */
    public function from($from=0)
    {
        $this->dsl->setOption('from',$from);
        return $this;
    }


    /**
     * 指定查询多少条
     * @param int $size
     * @return $this
     */
    public function size($size=10)
    {
        $this->dsl->setOption('size',$size);
        return $this;
    }



    /**
     * 按页码设定分页参数
     * @param int $page 页码
     * @param int $size 每页条数
     * @return $this
     */
    public function page($page=1,$size=10)
    {
        $this->dsl->setOption('from',$size*($page-1));
        $this->dsl->setOption('size',$size);

        return $this;
    }

    /**
     * 新增一条数据
     * 如果有ID,则会覆盖写入一条数据
     * 成功返回 数据id  否则返回 false
     * @param $data
     * @return array|bool
     */
    public function insert($data)
    {
        $this->dsl->setOption('data',$data);

        $params = $this->dsl->build('c');
        if ($this->fetch)
            return $params;

        $this->response = $this->client->index($params);
        return $this->response['_id']??false;
    }

    /**
     * 批量新增数据 如果有id，存在则更新，不存在则新增
     * 成功 返回id列表 失败返回 false
     * @param $data
     * @return array|bool
     */
    public function insertAll($data)
    {
        $this->dsl->setOption('data',$data);

        $params = $this->dsl->build('ca');
        if ($this->fetch)
            return $params;


        $this->response = $this->client->bulk($params);
        if (isset($this->response['errors']) && is_array($this->response['items']) && false === $this->response['errors']){
            $ids = [];
            foreach ($this->response['items'] as $val){
                $ids[] =$val['index']['_id']??false;
            }
            return $ids;
        }else{
            return false;
        }
    }


    /**
     * 更新数据
     * @param $data
     * @return array|bool|int 成功返回影响的条数,可能是0条,失败返回false
     */
    public function update($data)
    {
        $this->dsl->setOption('data',$data);

        $params = $this->dsl->build('u');
        if ($this->fetch)
            return $params;

        if (isset($params['body']['query'])){
            $this->response = $this->client->updateByQuery($params);
        }else{
            $this->response = $this->client->update($params);
        }

        if (isset($this->response['result'])){
            if ($this->response['result'] === 'updated'){
                return 1;
            }elseif($this->response['result'] === 'noop'){
                return 0;
            }
        }elseif(isset($this->response['total'])){
            return $this->response['total'];
        }

        return false;
    }



    /**
     * 删除一条数据
     * @param null $id
     * @return array|int|null
     */
    public function delete($id=null)
    {
        $id && $this->dsl->setOption('id',$id);

        $params = $this->dsl->build('d');
        if ($this->fetch)
            return $params;

        if (isset($params['body']['query'])){
            $this->response = $this->client->deleteByQuery($params);
        }else{
            $this->response = $this->client->delete($params);
        }
        if (isset($this->response['result'])){
            if ($this->response['result'] === 'deleted'){
                return 1;
            }elseif($this->response['result'] === 'not_found'){
                return 0;
            }
        }elseif(isset($this->response['total'])){
            return $this->response['total'];
        }

        return $this->response;
    }


    /**
     * 根据id获取一条数据
     * @param $id
     * @return array|bool
     */
    public function get($id)
    {
        $this->dsl->setOption('id',$id);
        $params = $this->dsl->build('r');
        if ($this->fetch)
            return $params;

        $this->response = $this->client->get($params);

        if (true === $this->response['found'] && isset($this->response['_source'])){
            return array_merge($this->response['_source'],['id'=>$this->response['_id']]);
        }
        return false;
    }


    /**
     * 执行搜索
     * @return array|bool
     */
    public function search()
    {
        $params = $this->dsl->build('r');
        if ($this->fetch)
            return $params;

        $this->response = $this->client->search($params);
        if (!isset($this->response['hits'])){
            return false;
        }
        $res = [
            'total'=>$this->response['hits']['total']
        ];
        foreach ($this->response['hits']['hits'] as $val){
            $res['lists'][] = array_merge(['id'=>$val['_id']],$val['_source']);
        }
        return $res;
    }

    /**
     * 不执行查询, 获取查询dsl语句
     * @param bool $fetch
     * @return $this
     */
    public function fetchDsl($fetch=true)
    {
        $this->fetch = $fetch;
        return $this;
    }


    /**
     * 获取最后执行的Dsl
     * @return array
     */
    public function getLastDsl()
    {
        return $this->dsl->getParams();
    }


    /**
     * 获取最后的响应结果
     * @return null|array
     */
    public function getLastResult()
    {
        return $this->response;
    }

}