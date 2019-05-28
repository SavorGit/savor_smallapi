<?php
namespace app\small\model;

use think\Model;

class Box extends Model
{
    protected $table = 'savor_box';

    public function getBoxlist($where){
        return $this->where($where)->limit(0,10)->select();
    }
    public function getOne($fields,$where){
        $data = $this->field($fields)->where($where)->find();
    }
}