<?php
namespace app\small\controller;
use app\common\controller\Base;
class Demand extends Base{
    function _init_(){
        switch($this->action){
            case 'getdemand'://获取酒楼手机点播
                $this->is_verify = 0;
                $this->valid_fields = array('boxMac'=>1001);
                $this->method = 'get';
                break;
        }
        parent::_init_();
    }
    /**
     * @desc 获取酒楼手机点播内容
     */
    public function getdemand(){
        $box_mac = $this->params['boxMac'];
        
        $m_box = new \app\small\model\Box();
        $fields = 'a.state box_state,a.flag box_flag,hotel.area_id,a.id box_id,a.mac box_mac,a.name box_name,
                   a.switch_time,a.volum,hotel.id hotel_id,hotel.name hotel_name,hotel.addr address,hotel.contractor linkman,
                   hotel.tel,ext.server_location server,ext.mac_addr mac,hotel.level,hotel.iskey key_point,
                   hotel.install_date,hotel.state hotel_state,hotel.state_change_reason state_reason,hotel.remark,hotel.create_time,
                   hotel.update_time,hotel.flag hotel_flag,hotel.hotel_box_type,
                   room.id room_id,room.name room_name,room.type room_type,room.probe,room.flag room_flag,room.state room_state';
        $where = array();
        $where['a.mac'] = $box_mac;
        $where['a.flag']= 0;
        $where['a.state'] = 1;
        $where['hotel.flag'] = 0;
        $where['hotel.state']= 1;
        $result = $m_box->getHotelBoxInfo($fields, $where);
        
        if(empty($result)){
            $this->to_back(10100);
        }
        //print_r($result);
        //获取系统设置电视设置
        $m_sys_config = new \app\small\model\SysConfig();
        $sys_info = $m_sys_config->getAllconfig();
        $system_ad_volume   = $sys_info['system_ad_volume'];
        $system_switch_time = !empty($sys_info['system_switch_time']) ?$sys_info['system_switch_time']:'';
        
        if(is_numeric($system_ad_volume) && $system_ad_volume>=0){
            $system_ad_volume = intval($system_ad_volume);
        }
        if(is_numeric($system_switch_time) && $system_switch_time>=0){
            $system_switch_time = intval($system_switch_time);
        }
        
        $redis = \SavorRedis::getInstance();
        $redis->select(10);
        $cache_key = config('cache.prefix').'vod:'.$result['hotel_id'].":".$box_mac;
        $redis_result = $redis->get($cache_key);
        if(empty($redis_result)){
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
            
            
            $m_mb_period = new \app\small\model\MbPeriod();
            $m_mb_home  = new \app\small\model\MbHome();
            //获取点播期号
            $field = " period,update_time ";
            $order = 'update_time desc';
            $where = [];
            $vod_period_result = $m_mb_period->getOne($field, $where,$order);
            $vod_period = $vod_period_result['period'];
            $pub_time   = $vod_period_result['update_time'];
            $vod_result = $m_mb_home->getvodInfo();
            $vod_tmp = array();
            foreach($vod_result as $key=>$v){
                $vod_tmp[$key]['mac'] = $result['box_mac'];
                $vod_tmp[$key]['hotelId'] = intval($result['hotel_id']);
                $vod_tmp[$key]['id']      = '';
                $vod_tmp[$key]['vid']     = intval($v['id']);
                $vod_tmp[$key]['name']    = $v['name'];
                $vod_tmp[$key]['chinese_name'] = $v['chinese_name'];
                $vod_tmp[$key]['period']  = $vod_period;
                $vod_tmp[$key]['type']    = 'pro';
                $vod_tmp[$key]['md5']     = $v['md5'];
                $vod_tmp[$key]['duration']= intval($v['duration']);
                $vod_tmp[$key]['suffix']  = $v['suffix'];
                $vod_tmp[$key]['url']     = '';
                $vod_tmp[$key]['oss_path']= $v['oss_path'];
                $vod_tmp[$key]['order']   = $v['sortNum'];
                $vod_tmp[$key]['pub_time']= $pub_time;
                $vod_tmp[$key]['room_id'] = $result['room_id'];
                $vod_tmp[$key]['is_sapp_qrcode'] = 0;
            
            }
            $vod_list['version']['label'] = '点播期号';
            $vod_list['version']['type']  = 'vod';
            $vod_list['version']['version'] = $vod_period;
            $vod_list['media_lib'] = $vod_tmp;
            
            $data['playbill_list'] = $vod_list;
            $data['pub_time']      = $pub_time;
            $redis->set($cache_key, json_encode($data),$this->expire); 
            $this->to_back($data);
        }else {
            $data = json_decode($redis_result,true);
            $this->to_back($data);
        }
         
    }
    
}