<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/24
 * Time: 17:30
 */

namespace SimpleEs\lib\build;


class Data extends Base
{

    public function c($data)
    {
        if (isset($data['id'])){
            $this->params['id'] = $data['id'];
            unset($data['id']);
        }

        $this->params['body'] = $data;

        return $this->params;
    }

    public function ca($data)
    {
        foreach($data as $val) {
            $this->params['body'][] = [
                'index'=>isset($val['id'])?['_id'=>$val['id']]:new \stdClass()
            ];
            unset($val['id']);
            $this->params['body'][] = $val;
        }
        return $this->params;
    }

    public function u($data,$isQuery)
    {
        if (isset($data['id'])){
            $this->params['id'] = $data['id'];
            unset($data['id']);
        }

        if (!$isQuery){
            $this->params['body']['doc'] = $data;
        }else{
            $script = [
                'source'=>'',
                'params'=>$data
            ];
            foreach($data as $key=>$val){
                $script['source'] .= "ctx._source.{$key}=params.{$key};";
            }
            $this->params['body']['script'] = $script;
        }

        return $this->params;
    }

}