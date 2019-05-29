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
        $data = $this->field($fields)->where($where)->find()->toArray();
        return $data;
    }
    public function getHotelBoxInfo($fields,$where,$type = 1){
        if($type==1){
            $data = $this->alias('a')
                         ->leftJoin('savor_room room','a.room_id=room.id')
                         ->leftJoin('savor_hotel hotel','room.hotel_id=hotel.id')
                         ->leftJoin('savor_hotel_ext ext','ext.hotel_id=hotel.id')
                         ->field($fields)
                         ->where($where)
                         ->find()
                         ->toArray();
        }else {
           $data = $this->alias('a')
                         ->leftJoin('savor_room room','a.room_id=room.id')
                         ->leftJoin('savor_hotel hotel','room.hotel_id=hotel.id')
                         ->leftJoin('savor_hotel_ext ext','ext.hotel_id=hotel.id')
                         ->field($fields)
                         ->where($where)
                         ->select()
                         ->toArray();
        }
        return $data;
        
    }
}