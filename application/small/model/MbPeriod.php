<?php
namespace app\small\model;

class MbPeriod extends Base
{
    public function getOne($fields,$where,$order){
        $data = $this->field($fields)->where($where)->order($order)->find();
        return $data;
    }
}