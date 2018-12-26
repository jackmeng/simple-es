<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/24
 * Time: 10:55
 */
error_reporting(-1);
ini_set('display_errors', 1);


require __DIR__.'/../vendor/autoload.php';


$config = include 'config.php';
require 'common.php';

echo "<pre>";

// 连接es
$es = es('dp_articles');
// 下面这行代码等同上一行
// $es = \SimpleEs\Es::getInstance(config('elasticsearch'),'indexName');
// 或
//$es = \SimpleEs\Es::getInstance(config('elasticsearch'));
//$es->setIndex('indexName');

// 设置新的index
// $es->setIndex('indexName');

// 获取es官方库对象
// $es->getClient();


$data = [
    "tag"=>"养生,健身,运动",
    "title"=>"通过运动健身可以达到养生的效果",
    'update_time'=>1545712577
];
// 新增一条数据(有id的情况下为覆盖写入)
//$add_res = $es->fetchDsl(false)->insert($data);
//
//var_dump($add_res);

$datas = [
    [
        'id'=>1,
        "tag"=>"育儿,保健",
        "title"=>"还没准备好就怀孕了？你了解这些育儿知识吗？",
        'update_time'=>1545712577
    ],
    [
        'id'=>2,
        "tag"=>"科技，大咖",
        "title"=>"这些科技大咖你了解过吗？",
        'update_time'=>1545712577
    ],
    [
        "tag"=>"美容，养颜",
        "title"=>"美容养颜，看着一片就够了",
        'update_time'=>1545712577
    ]
];
// 插入多条数据(插入的数据有id参数则为覆盖写入)
//$addAll_res = $es->fetchDsl(false)->insertAll($datas);
//var_dump($addAll_res);

// 根据id获取一条数据
//$id = 3;
//$row = $es->fetchDsl(false)->get($id);
//var_dump($row);
// 根据条件搜索数据
//$res = $es->fetchDsl(false)->query('match','title','titles')->from(0)->size(10)->sort('update_time','asc')->search();
//echo json_encode($res);


$update_data = [
    'id'=>1,
    'tag'=>'生活，健康,长寿',
    'title'=>'怎样才算健康的生活',
    'update_time'=>1545713579
];
// 基于id更新一条数据
//$update_res = $es->update($update_data);
//var_dump($update_res);
// 基于查询更新数据
//$update_res = $es->query('match','title','title')->update(['title'=>'titles']);
//var_dump($update_res);

$id = 'gWJr6GcBUBfJ4traAMbD';
// 按id删除数据
//$deleteById_res = $es->delete($id);
//var_dump($deleteById_res);

// 按条件删除数据
//$deleteById_res = $es->fetchDsl(false)->query('match','title','titles')->delete();


// EsModel 示例

class Dp_articles extends \SimpleEs\EsModel
{

}

$row = Dp_articles::get(1);

var_dump($row);


