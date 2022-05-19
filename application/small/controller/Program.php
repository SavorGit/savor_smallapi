<?php
namespace app\small\controller;

use app\common\controller\Base;

class Program extends Base{
    function _init_() {
        
        switch($this->action) {
            case 'getmenu'://主节目单
                $this->is_verify = 0;
                $this->valid_fields = array('boxMac'=>1001);
                $this->method = 'get';
                break;
            case 'getadv'://宣传片
                $this->is_verify = 0;
                $this->valid_fields = array('boxMac'=>1001);
                $this->method = 'get';
                break;
            case 'getads'://广告
                $this->is_verify = 0;
                $this->valid_fields = array('boxMac'=>1001);
                $this->method = 'get';
                break;
            case 'getpoly'://聚屏广告
                $this->is_verify = 0;
                $this->valid_fields = array('boxMac'=>1001);
                $this->method = 'get';
                break;
        }
        parent::_init_();
    }
    public function getMenu(){
        $box_mac = $this->params['boxMac'];
        $m_hotel = new \app\small\model\Hotel();
        $result = $m_hotel->getHotelInfo($box_mac);
        
        
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
        $cache_key = config('cache.prefix').'pro:'.$result['hotel_id'].":".$box_mac;
        $reids_result = $redis->get($cache_key);
        if(empty($reids_result)){
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
            $m_new_menu_hotel = new \app\small\model\ProgramMenuHotel();
            $hotel_id = $result['hotel_id'];
            $menu_info = $m_new_menu_hotel->getLatestMenuid($hotel_id);   //获取最新的一期节目单
            //print_r($menu_info);exit;
            if(empty($menu_info)){//该酒楼未设置节目单
                $this->to_back(10101);
            }
            
            $ads_list['version']['label']  = '广告占位符期号';
            $ads_list['version']['type']   = 'ads';
            $ads_list['version']['version']= $menu_info['menu_num'];
            
            $m_program_menu_item = new \app\small\model\ProgramMenuItem();
            
            $fields="";
            $where = array();
            $orderby =' sort_num asc';
            $pro_result = $m_program_menu_item->getMenuInfo($menu_info['menu_id']);
            
            $pro_list['version']['label'] = '节目期号';
            $pro_list['version']['type']  = 'pro';
            $pro_list['version']['version'] = $menu_info['menu_num'];
            $pro_tmp = array();
            foreach($pro_result as $key=>$v){
                $pro_tmp[$key]['mac'] = $result['box_mac'];
                $pro_tmp[$key]['hotelId'] = intval($result['hotel_id']);
                $pro_tmp[$key]['id']      = 0;
                $pro_tmp[$key]['vid']     = intval($v['id']);
                $pro_tmp[$key]['name']    = $v['name'];
                $pro_tmp[$key]['chinese_name'] = $v['chinese_name'];
                $pro_tmp[$key]['period']  = $menu_info['menu_num'];
                $pro_tmp[$key]['type']    = 'pro';
                $pro_tmp[$key]['md5']     = $v['md5'];
                $pro_tmp[$key]['duration']= intval($v['duration']);
                $pro_tmp[$key]['suffix']  = $v['suffix'];
                $pro_tmp[$key]['url']     = '';
                $pro_tmp[$key]['oss_path']= $v['oss_path'];
                $pro_tmp[$key]['order']   = $v['order'];
                $pro_tmp[$key]['pub_time']= $menu_info['pub_time'];
                $pro_tmp[$key]['room_id'] = $result['room_id'];
                $pro_tmp[$key]['media_type'] = $v['media_type'];
                $pro_tmp[$key]['is_sapp_qrcode'] = $v['is_sapp_qrcode'];
            }
            $pro_list['media_lib'] = $pro_tmp;
            //节目结束
            //广告、宣传片、rtb广告、聚屏广告占位符开始 1 ads 3 adv  4 rtb 5 poly
            $ads_result = $m_program_menu_item->getMenuAdsPlaceholder($menu_info['menu_id'], '1,3,4,5,6,7,8,9');
            $ads_tmp = $adv_tmp = $shopgoods_tmp = $poly_tmp = $actgoods_tmp = $selectcontent_tmp= $life_tmp = $storesale_tmp = array();
            foreach($ads_result as $key=>$v){
                $ads_arr['chinese_name'] = $v['chinese_name'];
                $ads_arr['period']       = $menu_info['menu_num'];
                $ads_arr['type']         = $v['type'];
                $ads_arr['duration']     = 0;
                $ads_arr['order']        = intval($v['order']);
                $ads_arr['pub_time']     = $menu_info['pub_time'];
                $ads_arr['location_id']  = intval($v['location_id']);
                $ads_arr['is_sapp_qrcode'] = 0;
                if($v['type']=='ads'){
                    $ads_tmp[] = $ads_arr;
                }elseif($v['type'] =='adv'){
                    $ads_arr['location_id'] = $ads_arr['order'];
                    $adv_tmp[] = $ads_arr;
                }elseif($v['type'] == 'shopgoods'){
                    $shopgoods_tmp[] = $ads_arr;
                }elseif($v['type'] == 'poly'){
                    $poly_tmp[] = $ads_arr;
                }elseif($v['type']=='actgoods'){
                    $actgoods_tmp[] = $ads_arr;
                }elseif($v['type']=='selectcontent'){
                    $selectcontent_tmp[] = $ads_arr;
                }elseif($v['type']=='life'){
                    $life_tmp[] = $ads_arr;
                }elseif($v['type']=='storesale'){
                    $storesale_tmp[] = $ads_arr;
                }
            }
            $adv_list['version']['label'] = '宣传片占位符期号';
            $adv_list['version']['type']  = 'adv';
            $adv_list['version']['version'] = $menu_info['menu_num'];
            $adv_list['media_lib'] = $adv_tmp;
            
            $ads_list['version']['label'] = '广告占位符期号';
            $ads_list['version']['type']  = 'ads';
            $ads_list['version']['version'] = $menu_info['menu_num'];
            $ads_list['media_lib'] = $ads_tmp;
            
            $rtb_list['version']['label'] = '商品广告期号';
            $rtb_list['version']['type']  = 'shopgoods';
            $rtb_list['version']['version'] = $menu_info['menu_num'];
            $rtb_list['media_lib'] = $shopgoods_tmp;
            
            $poly_list['version']['label'] = '聚屏广告占位符期号';
            $poly_list['version']['type']  = 'poly';
            $poly_list['version']['version'] = $menu_info['menu_num'];
            $poly_list['media_lib'] = $poly_tmp;
            
            $actgoods_list['version']['lable'] ='活动商品占位符期号';
            $actgoods_list['version']['type']  ='actgoods';
            $actgoods_list['version']['version'] = $menu_info['menu_num'];
            $actgoods_list['media_lib'] = $actgoods_tmp;

            $selectcontent_list['version']['lable'] ='精选内容占位符期号';
            $selectcontent_list['version']['type']  ='selectcontent';
            $selectcontent_list['version']['version'] = $menu_info['menu_num'];
            $selectcontent_list['media_lib'] = $selectcontent_tmp;

            $life_list['version']['lable'] ='本地生活占位符期号';
            $life_list['version']['type']  ='life';
            $life_list['version']['version'] = $menu_info['menu_num'];
            $life_list['media_lib'] = $life_tmp;

            $storesale_list['version']['lable'] ='本店有售占位符期号';
            $storesale_list['version']['type']  ='storesale';
            $storesale_list['version']['version'] = $menu_info['menu_num'];
            $storesale_list['media_lib'] = $storesale_tmp;

            $data['playbill_list'] = array($pro_list,$adv_list,$ads_list,$rtb_list,$poly_list,$actgoods_list,$selectcontent_list,$life_list,$storesale_list);
            $data['pub_time'] = $menu_info['pub_time'];
            $redis->set($cache_key, json_encode($data),$this->expire);
            $this->to_back($data);
        }else {
            $data = json_decode($reids_result,true);
            $this->to_back($data);
        }
    }

    /**
     * @desc 获取机顶盒酒楼宣传片
     */
    public function getadv(){
        $box_mac = $this->params['boxMac'];
        $m_hotel = new \app\small\model\Hotel();
        $result = $m_hotel->getHotelInfo($box_mac);
        
        if(empty($result)){
            $this->to_back(10100);
        }
        
        $hotel_id = $result['hotel_id'];
        
        //获取最新一期节目单
        $m_new_menu_hotel = new \app\small\model\ProgramMenuHotel();
        
        $menu_info = $m_new_menu_hotel->getLatestMenuid($hotel_id);   //获取最新的一期节目单
        
        if(empty($menu_info)){//该酒楼未设置节目单
            $this->to_back(10101);
        }
        $redis  = \SavorRedis::getInstance();
        $redis->select(10);
        $cache_key = config('cache.prefix').'adv:'.$hotel_id.":".$box_mac;
        $redis_result = $redis->get($cache_key);
        if(empty($redis_result)){
            //获取宣传片期号
            $m_ads= new \app\small\model\Ads();
            $adv_period_info = $m_ads->getInfo(array('hotel_id'=>$hotel_id,'type'=>3),'max(update_time) as max_update_time');
            $adv_period = date('YmdHis',strtotime($adv_period_info['max_update_time']));
            
            //获取酒楼宣传片
            $m_program_menu_item = new \app\small\model\ProgramMenuItem();
            $adv_result = $m_program_menu_item->getadvInfo($hotel_id, $menu_info['menu_id']);
            if(empty($adv_result)){
                
                $data['version']['label'] = '宣传片期号';
                $data['version']['type']  = 'adv';
                $data['version']['version'] = '20190101000000'.$menu_info['menu_num'];
                $data['menu_num'] =  $menu_info['menu_num'];                             
                
                $this->to_back($data);
                //$this->to_back(10102);
            }
            
            $adv_tmp = array();
            foreach($adv_result as $key=>$v){
                $adv_tmp[$key]['mac'] = $box_mac;
                $adv_tmp[$key]['hotelId'] = $hotel_id;
                $adv_tmp[$key]['id']      = 0;
                $adv_tmp[$key]['vid']     = intval($v['id']);
                $adv_tmp[$key]['name']    = $v['name'];
                $adv_tmp[$key]['chinese_name'] = $v['chinese_name'];
                $adv_tmp[$key]['period']  = $adv_period.$menu_info['menu_num'];
                $adv_tmp[$key]['type']    = 'adv';
                $adv_tmp[$key]['md5']     = $v['md5'];
                $adv_tmp[$key]['duration']= $v['duration'];
                $adv_tmp[$key]['suffix']  = $v['suffix'];
                $adv_tmp[$key]['url']     = '';
                $adv_tmp[$key]['oss_path']= $v['oss_path'];
                $adv_tmp[$key]['order']   = 0;
                $adv_tmp[$key]['pub_time']= $menu_info['pub_time'];
                $adv_tmp[$key]['room_id'] = intval($result['room_id']);
                $adv_tmp[$key]['location_id'] = intval($v['order']);
                $adv_tmp[$key]['media_type'] = $v['media_type'];
                $adv_tmp[$key]['is_sapp_qrcode'] = $v['is_sapp_qrcode'];
            }
            $adv_list['version']['label'] = '宣传片期号';
            $adv_list['version']['type']  = 'adv';
            $adv_list['version']['version'] = $adv_period.$menu_info['menu_num'];
            $data = $adv_list;
            $data['media_lib'] = $adv_tmp;
            $data['menu_num']  = $menu_info['menu_num'];
            $redis->set($cache_key, json_encode($data),$this->expire);
            $this->to_back($data);
        }else {
            $data = json_decode($redis_result,true);
            $this->to_back($data);
        }
        
        
    }
    /**
     * @desc 获取机顶盒C类广告
     */
    public function getads(){
        $box_mac = $this->params['boxMac'];
        $m_hotel = new \app\small\model\Hotel();
        $result = $m_hotel->getHotelInfo($box_mac);
        
        if(empty($result)){
            $this->to_back(10100);
        }
        $redis = \SavorRedis::getInstance();
        $redis->select(10);
        $cache_key = config('cache.prefix').'ads:'.$result['hotel_id'].":".$box_mac;
        $redis_result = $redis->get($cache_key);
        
        if(empty($redis_result)){
            $hotel_id = $result['hotel_id'];
            $box_id   = $result['box_id'];
            $m_pub_ads_box = new \app\small\model\PubAdsBox();
            $ads_result = $m_pub_ads_box->getAdsList($box_id);
            if(empty($ads_result)){
                $this->to_back(10103);
            }
            $ads_period_info = $m_pub_ads_box->getBoxPorid($box_id);
            $ads_period = date('YmdHis',strtotime($ads_period_info['create_time']));
            
            $ads_tmp = array();
            foreach($ads_result as $key=>$v){
                $ads_tmp[$key]['mac'] = $box_mac;
                $ads_tmp[$key]['hotelId'] = intval($hotel_id);
                $ads_tmp[$key]['id']      = 0;
                $ads_tmp[$key]['vid']     = intval($v['id']);
                $ads_tmp[$key]['name']    = $v['name'];
                $ads_tmp[$key]['chinese_name'] = $v['chinese_name'];
                $ads_tmp[$key]['period']  = $ads_period;
                $ads_tmp[$key]['type']    = 'ads';
                $ads_tmp[$key]['md5']     = $v['md5'];
                $ads_tmp[$key]['duration']= intval($v['duration']);
                $ads_tmp[$key]['suffix']  = $v['suffix'];
                $ads_tmp[$key]['url']     = '';
                $ads_tmp[$key]['oss_path']= $v['oss_path'];
                $ads_tmp[$key]['order']   = 0;
                $ads_tmp[$key]['pub_time']= $v['create_time'];
                $ads_tmp[$key]['room_id'] = $result['room_id'];
                $ads_tmp[$key]['start_date'] = $v['start_date'];
                $ads_tmp[$key]['end_date']= $v['end_date'];
                $ads_tmp[$key]['location_id'] = intval($v['location_id']);
                $ads_tmp[$key]['media_type'] = $v['media_type'];
                $ads_tmp[$key]['is_sapp_qrcode'] = $v['is_sapp_qrcode'];
            }
            $ads_list['version']['label'] = '广告期号';
            $ads_list['version']['type']  = 'ads';
            $ads_list['version']['version'] = $ads_period;
            $data = $ads_list;
            $data['media_lib'] = $ads_tmp;
            $data['menu_num'] = $ads_period;
            $redis->set($cache_key, json_encode($data),14400);
            $this->to_back($data);
        }else {
            $data = json_decode($redis_result,true);
            $this->to_back($data);
        }
    }
    /**
     * @desc 获取聚屏广告
     * 
     */
    public function getpoly(){
        $box_mac = $this->params['boxMac'];
        $m_hotel = new \app\small\model\Hotel();
        $result = $m_hotel->getHotelInfo($box_mac);
        
        if(empty($result)){
            $this->to_back(10100);
        }
        if(!empty($result['tpmedia_id'])){
            
            $redis = \SavorRedis::getInstance();
            $redis->select(10);
            $cache_key = config('cache.prefix').'poly:'.$result['hotel_id'].":".$box_mac; 
            $redis_result = $redis->get($cache_key);
            if(empty($redis_result)){
                $m_pub_poly_ads = new \app\small\model\PubPolyAds();
                $fields = "a.update_time,media.id,substr(media.oss_addr,16) as name,media.md5,media.type as mtype,
                       a.media_md5  tp_md5,a.type as media_type,
                      'poly' as type,media.oss_addr oss_path,media.duration,media.surfix,
                       media.name chinese_name,a.tpmedia_id,ads.is_sapp_qrcode";
                $where = array();
                $where['a.state'] = 1;
                $where['a.flag'] =0;
                $tmp = explode(',', $result['tpmedia_id']);
                $where['a.tpmedia_id'] = $tmp;
                
                $order = 'a.update_time desc ';
                $poly_result = $m_pub_poly_ads->getList($fields, $where,$order);
                $list =  $poly_result->toArray();
                if(!empty($list)){
                    
                    $update_time_arr = array_column($list,'update_time');
                    $poly_period = date('YmdHis',strtotime(max($update_time_arr)));
                    $poly_tmp = array();
                    foreach($poly_result as $key=>$v){
                        $poly_tmp[$key]['mac'] = $box_mac;
                        $poly_tmp[$key]['hotelId'] = intval($result['hotel_id']);
                        $poly_tmp[$key]['id']  = 0;
                        $poly_tmp[$key]['vid'] = intval($v['id']);
                        $poly_tmp[$key]['name']= $v['name'];
                        $poly_tmp[$key]['chinese_name'] = $v['chinese_name'];
                        $poly_tmp[$key]['period'] = $poly_period;
                        $poly_tmp[$key]['type']= 'poly';
                        $poly_tmp[$key]['md5'] = $v['md5'];
                        $poly_tmp[$key]['duration'] = intval($v['duration']);
                        $poly_tmp[$key]['url'] = '';
                        $poly_tmp[$key]['oss_path'] = $v['oss_path'];
                        $poly_tmp[$key]['order'] = 0;
                        $poly_tmp[$key]['pub_time'] = $v['update_time'];
                        $poly_tmp[$key]['media_type'] = $v['media_type'];
                        $poly_tmp[$key]['tpmedia_id'] = $v['tpmedia_id'];
                        $poly_tmp[$key]['tp_md5']     = $v['tp_md5'];
                        $poly_tmp[$key]['is_sapp_qrcode'] = intval($v['is_sapp_qrcode']);
                    }
                    $data['version']['label'] = '聚屏广告期号';
                    $data['version']['type']  = 'poly';
                    $data['version']['version']= $poly_period;
                    $data['media_lib'] = $poly_tmp;
                    $data['menu_num'] = $poly_period;
                    $redis->set($cache_key, json_encode($data),$this->expire);
                    $this->to_back($data);
                }else {
                    $this->to_back(10105);
                }
            }else { 
                $data = json_decode($redis_result,true);
                $this->to_back($data);
            }
        }else {
            $this->to_back(10104);   
        }
    }
}