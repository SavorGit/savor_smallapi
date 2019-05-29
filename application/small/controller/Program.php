<?php
namespace app\small\controller;

use app\common\controller\Base;

class Program extends Base{

    function _init_() {
        switch($this->action) {
            case 'getmenu':
                $this->is_verify = 0;
                $this->method = 'get';
                break;
        }
        parent::_init_();
    }
    public function getmenu(){
        $box_mac = $this->params['boxMac'];
        $m_box = new \app\small\model\Box();
        $fields = 'a.state box_state,a.flag box_flag,hotel.area_id,a.id box_id,a.mac box_mac,a.name box_name,
                   a.switch_time,a.volum,hotel.id hotel_id,hotel.name hotel_name,hotel.addr address,hotel.contractor linkman,
                   hotel.tel,ext.server_location server,ext.mac_addr mac,hotel.level,hotel.iskey key_point,
                   hotel.install_date,hotel.state hotel_state,hotel.state_change_reason state_reason,hotel.remark,hotel.create_time,
                   hotel.update_time,hotel.flag hotel_flag,hotel.hotel_box_type,
                   room.id room_id,room.name room_name,room.type room_type,room.probe,room.flag room_flag,room.state room_state';
        $where = array();
        $where['mac'] = $box_mac;
        $where['a.flag']= 0;
        $where['a.state'] = 1;
        $where['hotel.flag'] = 0;
        $where['hotel.state']= 1;
        $result = $m_box->getHotelBoxInfo($fields, $where);
        
        if(empty($result)){
            $this->to_back(10100);
        }
        //获取系统设置电视设置
        $m_sys_config = new \app\small\model\SysConfig();
        $fields = 'config_key,config_value';
        $where = array();
        $where['config_key'] = array('system_switch_time','system_ad_volume');
        $where['status'] = 1;
        $sys_info = $m_sys_config->getSysInfo($fields, $where);
        $system_ad_volume = $system_switch_time = '';
        
        foreach($sys_info as $key=>$v){
            if($v['config_key']=='system_ad_volume' && is_numeric($v['config_value']) && $v['config_value']>=0){
                $system_ad_volume = intval($v['config_value']);
            }
            if($v['config_key']=='system_switch_time' && is_numeric($v['config_value']) && $v['config_value']>=0 ){
                $system_switch_time = intval($v['config_value']);
            }
            
        }
        $data = array();
        $data['state']   = intval($result['box_state']);
        $data['flag']    = intval($result['box_flag']);
        $data['area_id'] = intval($result['area_id']);
        $data['box_id']  = intval($result['box_id']);
        $data['box_mac'] = $result['box_mac'];
        $data['switch_time'] = !empty($system_switch_time) ? $system_switch_time: intval($result['switch_time']);
        $data['volume']  = !empty($system_ad_volume) ? $system_ad_volume : intval($result['volum']);
        //酒楼节点
        $data['boite']['hotel_id'] = intval($result['hotel_id']);
        $data['boite']['hotel_name'] = $result['hotel_name'];
        $data['boite']['address']  = $result['address'];
        $data['boite']['area_id']  = intval($data['area_id']);
        $data['boite']['linkman']  = $result['linkman'];
        $data['boite']['tel']      = $result['tel'];
        $data['boite']['server']   = $result['server'];
        $data['boite']['mac']      = $result['mac'];
        $data['boite']['level']    = $result['level'];
        $data['boite']['key_point']= intval($result['key_point']);
        $data['boite']['install_date'] = $result['install_date'];
        $data['boite']['room_count'] = 0;
        $data['boite']['tv_count']   = 0;
        $data['boite']['hall_count'] = 0;
        $data['boite']['box_count']  = 0;
        $data['boite']['waiting_screen_count'] = 0;
        $data['boite']['state']      = intval($result['hotel_state']);
        $data['boite']['state_reason'] = intval($result['state_reason']);
        $data['boite']['remark']     = $result['remark'];
        $data['boite']['create_time']= $result['create_time'];
        $data['boite']['update_time']= $result['update_time'];
        $data['boite']['flag']       = intval($result['hotel_flag']);
        $data['boite']['hotel_box_type'] = intval($result['hotel_box_type']);
        //包间节点
        $data['room']['room_id'] = intval($result['room_id']);
        $data['room']['room_name'] = $result['room_name'];
        $data['room']['hotel_id']  = intval($result['hotel_id']);
        $room_type_arr = config('room_type_arr');
        $data['room']['room_type'] = $room_type_arr[$result['room_type']];   //需要些配置文件
        $data['room']['flag']      = intval($result['room_flag']);
        $data['room']['state']     = intval($result['room_state']);
        $data['room']['probe']     = $result['probe'];
        $data['box_name'] = $result['box_name'];
        //节目单节点
        
        //获取最新一期节目单
        $m_new_menu_hotel = new \app\small\Model\ProgramMenuHotel();
        $hotel_id = $result['hotel_id'];
        $menu_info = $m_new_menu_hotel->getLatestMenuid($hotel_id);   //获取最新的一期节目单
        print_r($menu_info);exit;
        if(empty($menu_info)){//该酒楼未设置节目单
            $this->to_back(10101);
        }
        
    }
}