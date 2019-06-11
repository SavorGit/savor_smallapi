<?php
namespace app\small\model;

class Hotel extends Base
{
    public function getHotelInfo($box_mac){
        $m_box = new \app\small\model\Box();
        $where = array();
        $where['mac']  = $box_mac;
        $where['flag'] = 0;
        $where['state']= 1;
        $box_result = $m_box->getInfo($where,'id box_id');
        $redis  = \SavorRedis::getInstance();
        $redis->select(15);
        $cache_key = 'savor_box_'.$box_result['box_id'];
        $redis_box_info = $redis->get($cache_key);
        $box_info = json_decode($redis_box_info,true);
        $result = [];
        if(empty($box_info)){
            $fields = 'a.state box_state,a.flag box_flag,hotel.area_id,a.id box_id,a.mac box_mac,a.name box_name,
                   a.switch_time,a.volum,hotel.id hotel_id,hotel.name hotel_name,hotel.addr address,hotel.contractor linkman,
                   hotel.tel,ext.server_location server,ext.mac_addr mac,hotel.level,hotel.iskey key_point,a.tpmedia_id,
                   hotel.install_date,hotel.state hotel_state,hotel.state_change_reason state_reason,hotel.remark,hotel.create_time,
                   hotel.update_time,hotel.flag hotel_flag,hotel.hotel_box_type,hotel.media_id hotel_media_id,
                   room.id room_id,room.name room_name,room.type room_type,room.probe,room.flag room_flag,room.state room_state';
            $where = array();
            $where['mac'] = $box_mac;
            $where['a.flag']= 0;
            $where['a.state'] = 1;
            $where['hotel.flag'] = 0;
            $where['hotel.state']= 1;
            $result = $m_box->getHotelBoxInfo($fields, $where);
        }else {
            $cache_key = 'savor_room_' . $box_info['room_id'];
            $redis_room_info = $redis->get($cache_key);
            $room_info = json_decode($redis_room_info, true);
            $cache_key = 'savor_hotel_' . $room_info['hotel_id'];
            $redis_hotel_info = $redis->get($cache_key);
            $hotel_info = json_decode($redis_hotel_info, true);
            $cache_key = 'savor_hotel_ext_' . $room_info['hotel_id'];
            $redis_hotel_ext_info = $redis->get($cache_key);
            $hotel_ext_info = json_decode($redis_hotel_ext_info, true);

            $result['box_state'] = $box_info['state'];
            $result['box_flag'] = $box_info['flag'];
            $result['area_id'] = $hotel_info['area_id'];

            $result['box_id'] = $box_result['box_id'];
            $result['box_mac'] = $box_mac;
            $result['box_name'] = $box_info['name'];
            $result['switch_time'] = $box_info['switch_time'];
            $result['volum'] = $box_info['volum'];
            $result['hotel_id'] = $room_info['hotel_id'];
            $result['hotel_name'] = $hotel_info['name'];
            $result['hotel_media_id'] = $hotel_info['media_id'];
            $result['address'] = $hotel_info['addr'];
            $result['linkman'] = $hotel_info['contractor'];
            $result['tel'] = $hotel_info['tel'];
            $result['server'] = $hotel_ext_info['server_location'];
            $result['mac'] = $hotel_ext_info['mac_addr'];
            $result['level'] = $hotel_info['level'];
            $result['key_point'] = $hotel_info['iskey'];
            $result['tpmedia_id'] = !empty($box_info['tpmedia_id']) ? $box_info['tpmedia_id'] :'';
            $result['install_date'] = $hotel_info['install_date'];
            $result['hotel_state'] = $hotel_info['state'];
            $result['state_reason'] = $hotel_info['state_change_reason'];
            $result['remark'] = $hotel_info['remark'];
            $result['create_time'] = $hotel_info['create_time'];
            $result['update_time'] = $hotel_info['update_time'];
            $result['hotel_flag'] = $hotel_info['flag'];
            $result['hotel_box_type'] = $hotel_info['hotel_box_type'];
            $result['room_id'] = $box_info['room_id'];
            $result['room_name'] = $room_info['name'];
            $result['room_type'] = $room_info['type'];
            $result['probe'] = !empty($room_info['probe']) ? $room_info['probe'] : '';
            $result['room_flag'] = $room_info['flag'];
            $result['room_state'] = $room_info['state'];
        }
        if(empty($result)){
            return array('code'=>10100);
        }
        return $result;
    } 
}


