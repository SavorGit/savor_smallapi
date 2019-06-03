<?php
namespace app\small\controller;

use app\common\controller\Base;

class Box extends Base{

    function _init_() {
        switch($this->action) {
            case 'initdata':
                $this->is_verify = 0;
                $this->method = 'get';
                break;
        }
        parent::_init_();
    }

    public function initdata(){
        $box_mac = $this->params['boxMac'];
        $m_hotel = new \app\small\model\Hotel();
        $res_hotelbox = $m_hotel->getHotelInfo($box_mac);
        $res_box = array('switch_time'=>$res_hotelbox['switch_time'],'volume'=>$res_hotelbox['volum'],'hotel_id'=>$res_hotelbox['hotel_id'],
            'room_id'=>$res_hotelbox['room_id'],'hotel_name'=>$res_hotelbox['hotel_name'],'room_name'=>$res_hotelbox['room_name'],
            'box_id'=>$res_hotelbox['box_id'],'box_name'=>$res_hotelbox['box_name'],'area_id'=>$res_hotelbox['area_id'],
            'media_id'=>$res_hotelbox['hotel_media_id'],'room_type'=>$res_hotelbox['room_type'],);
        $room_types = config('room_type_arr');
        if(!empty($res_box)){
            $res_box['room_type'] = $room_types[$res_box['room_type']];
        }
        $hotel_id = $res_box['hotel_id'];
        $logo_mediaid = $res_box['media_id'];
        unset($res_box['media_id']);
        $m_sysconfig = new \app\small\model\SysConfig();
        $sysconfig = $m_sysconfig->getAllconfig();
        $loading_mediaid = $sysconfig['system_loading_image'];

        $m_media = new \app\small\model\Media();
        $fields = 'id,md5,oss_addr';
        $where = array('id'=>[$logo_mediaid,$loading_mediaid]);
        $res_media = $m_media->getDataList($fields,$where,'');
        foreach ($res_media as $v){
            if($v['id']==$logo_mediaid){
                $res_box['logo_url'] = $v['oss_addr'];
                $res_box['logo_md5'] = $v['md5'];
            }
            if($v['id']==$loading_mediaid){
                $res_box['loading_img_url'] = $v['oss_addr'];
                $res_box['loading_img_md5'] = $v['md5'];
            }
        }
        $all_version_types = config('version_types');
        $res_box['oss_bucket_name'] = env('oss_bucket_name');

        //广告期号
        $m_pub_ads_box = new \app\small\model\PubAdsBox();
        $ads_proid_info = $m_pub_ads_box->getBoxPorid($res_box['box_id']);
        $ads_proid = date('YmdHis',strtotime($ads_proid_info['create_time']));

        //获取最新节目期号
        $m_new_menu_hotel = new \app\small\Model\ProgramMenuHotel();
        $menu_info = $m_new_menu_hotel->getLatestMenuid($hotel_id);
        $menu_id = $menu_info['menu_num'];

        //宣传片期号
        $m_ads= new \app\small\Model\Ads();
        $adv_period_info = $m_ads->getInfo(array('hotel_id'=>$hotel_id,'type'=>3),'max(update_time) as max_update_time');
        $adv_period = date('YmdHis',strtotime($adv_period_info['max_update_time']));
        $adv_period = $adv_period.$menu_id;

        //获取点播期号
        $m_mb_period = new \app\small\model\MbPeriod();
        $field = " period,update_time ";
        $order = 'update_time desc';
        $where = [];
        $vod_period_result = $m_mb_period->getOne($field, $where,$order);
        $demand_period = $vod_period_result['period'];

        $res_box['playbill_version_list'] = array(
            array('label'=>$all_version_types['ads'],'type'=>'ads','version'=>$ads_proid),
            array('label'=>$all_version_types['adv'],'type'=>'adv','version'=>$adv_period),
            array('label'=>$all_version_types['pro'],'type'=>'pro','version'=>$menu_id),
        );

        $demand_version_list = array('label'=>$all_version_types['vod'],'type'=>'vod','version'=>$demand_period);
        $res_box['demand_version_list'] = array($demand_version_list);
        $logo_version_list = array('label'=>$all_version_types['logo'],'type'=>'logo','version'=>$logo_mediaid);
        $res_box['logo_version_list'] = array($logo_version_list);
        $loading_version_list = array('label'=>$all_version_types['load'],'type'=>'load','version'=>$loading_mediaid);
        $res_box['loading_version_list'] = array($loading_version_list);

        $m_upgrade = new \app\small\model\DeviceUpgrade();
        $device_type = 2;//1小平台，2机顶盒，3手机android，4手机iphone
        $res_upgrade = $m_upgrade->getLastUpgradeInfo($res_box['hotel_id'],'',$device_type);
        $apk_version = $res_upgrade['version'];
        $apk_version_list = array('label'=>$all_version_types['apk'],'type'=>'apk','version'=>$apk_version);
        $res_box['apk_version_list'] = array($apk_version_list);
        $res_box['small_web_version_list'] = array();

        $res_box['ads_volume'] = $sysconfig['system_ad_volume'];
        $res_box['project_volume'] = $sysconfig['system_pro_screen_volume'];
        $res_box['demand_volume'] = $sysconfig['system_demand_video_volume'];
        $res_box['tv_volume'] = $sysconfig['system_tv_volume'];

        $m_tv = new \app\small\model\Tv();
        $fields = 'id as tv_id,box_id,tv_brand as tv_Brand,tv_size,tv_source,flag,state';
        $where = array('box_id'=>$res_box['box_id']);
        $res_box['tv_list'] = $m_tv->getDataList($fields,$where,'');

        $this->to_back($res_box);
    }
}
