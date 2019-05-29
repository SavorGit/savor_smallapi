<?php
namespace app\small\model;

use think\Model;

class Base extends Model{
    public function getDataList($fields,$where,$orderby,$groupby='',$start=0,$size=0){
        if($start >= 0 && $size){
            $list = $this->field($fields)->where($where)->group($groupby)->order($orderby)->limit($start,$size)->select();
            $count = $this->countNum($where);
            $data = array('list'=>$list,'total'=>$count);
        }else{
            $data = $this->field($fields)->where($where)->group($groupby)->order($orderby)->select();
        }
        return $data;
    }

    public function countNum($where){
        $nums = $this->where($where)->count();
        return $nums;
    }

    public function getInfo($where,$fields="*"){
        $result = $this->field($fields)->where($where)->find();
        return $result;
    }

    public function updateInfo($data,$where){
        $res = parent::update($data,$where);
        return $res;
    }

    public function addData($data){
        $res = parent::create($data);
        return $res;
    }

    public function delData($where){
        $result = $this->where($where)->delete();
        return  $result;
    }
}