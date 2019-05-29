<?php
namespace app\small\model;

class Box extends Base
{
    protected $table = 'savor_box';

    public function getHotelBoxInfo($fields,$where,$type = 1){
        if($type==1){
            $data = $this->alias('a')
                         ->leftJoin('savor_room room','a.room_id=room.id')
                         ->leftJoin('savor_hotel hotel','room.hotel_id=hotel.id')
                         ->leftJoin('savor_hotel_ext ext','ext.hotel_id=hotel.id')
                         ->field($fields)
                         ->where($where)
                         ->find();
        }else {
           $data = $this->alias('a')
                         ->leftJoin('savor_room room','a.room_id=room.id')
                         ->leftJoin('savor_hotel hotel','room.hotel_id=hotel.id')
                         ->leftJoin('savor_hotel_ext ext','ext.hotel_id=hotel.id')
                         ->field($fields)
                         ->where($where)
                         ->select();
        }
        return $data;
    }
}