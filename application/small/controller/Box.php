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
        $m_box = new \app\small\model\Box();
        $fields = "a.switch_time,a.volum as volume,hotel.id as hotel_id,room.id as room_id,hotel.name as hotel_name,
        room.name as room_name,a.id as box_id,a.name as box_name,hotel.area_id,hotel.media_id,room.type as room_type";
        $where = array('a.mac'=>$box_mac);
        $res_box = $m_box->getHotelBoxInfo($fields,$where);
        $room_types = config('room_type_arr');
        if(!empty($res_box)){
            $res_box['room_type'] = $room_types[$res_box['room_type']];
        }
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
        $res_box['oss_bucket_name'] = 'redian-produce';
        $res_box['playbill_version_list'] = array();//todo 广告期号、宣传片占位符期号、节目期号
        $res_box['demand_version_list'] = array();//todo 点播期号
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
