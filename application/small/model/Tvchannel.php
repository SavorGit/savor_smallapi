<?php
namespace app\small\model;

class Tvchannel extends Base{

    public function getCustomList($fields,$where,$orderby){
        $data = $this->alias('a')
            ->field($fields)
            ->leftJoin('savor_tvchannel_ext ext','a.id=ext.tvchannel_id')
            ->where($where)
            ->order($orderby)
            ->select();
        return $data;
    }
}