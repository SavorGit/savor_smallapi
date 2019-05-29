<?php
namespace app\small\model;

class SysConfig extends Base
{
    public function getSysInfo($fields,$where){
        $data = $this->field($fields)->where($where)->select();
        return $data;
    }

    public function getAllconfig($status=1){
        $where = array();
        if($status){
            $where = array('status'=>1);
        }
        $res_config = $this->getDataList('config_key,config_value',$where,'');
        $sysconfig = array();
        foreach ($res_config as $v){
            $sysconfig[$v['config_key']] = $v['config_value'];
        }
        return $sysconfig;
    }
}