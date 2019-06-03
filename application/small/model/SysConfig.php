<?php
namespace app\small\model;

class SysConfig extends Base
{
    public function getSysInfo($fields,$where){
        $data = $this->field($fields)->where($where)->select();
        return $data;
    }

    public function getAllconfig(){
        $redis = \SavorRedis::getInstance();
        $redis->select(12);
        $cache_key = 'system_config';
        $res_config = $redis->get($cache_key);
        if(!empty($res_config)){
            $res_config = json_decode($res_config,true);
        }else{
            $where = array('status'=>1);
            $res_config = $this->getDataList('*',$where,'');
            $redis->set($cache_key,json_encode($res_config));
        }
        $sysconfig = array();
        foreach ($res_config as $v){
            $sysconfig[$v['config_key']] = $v['config_value'];
        }
        return $sysconfig;
    }
}