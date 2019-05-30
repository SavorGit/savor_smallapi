<?php
namespace app\small\model;

class ProgramMenuHotel extends Base
{
    protected $table = 'savor_programmenu_hotel';
    public function getLatestMenuid($hotel_id){
        $now_date = date('Y-m-d H:i:s');
        $data = $this->alias('a')
        ->leftJoin('savor_programmenu_list b',' a.menu_id=b.id')
        ->field('a.menu_id,b.menu_num,a.pub_time')
        ->where("a.hotel_id=$hotel_id and a.pub_time<='$now_date'")
        ->order('a.pub_time desc')
        ->find();
        return $data;
    }
}