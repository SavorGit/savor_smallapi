<?php
namespace app\small\model;

use think\Model;

class SysConfig extends Base
{
    protected $table = 'savor_sys_config';
    public function getSysInfo($fields,$where){
        $data = $this->field($fields)->where($where)->select();
        return $data;
    }
}