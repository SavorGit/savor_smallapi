<?php
namespace app\small\controller;

use app\common\controller\Base;

class Upgrade extends Base{

    function _init_() {
        switch($this->action) {
            case 'boxupgrade':
                $this->is_verify = 1;
                $this->valid_fields =array('boxMac'=>1001);
                $this->method = 'get';
                break;
        }
        parent::_init_();
    }
    public function boxupgrade(){
        $box_mac = $this->headerinfo['boxMac'];
        $hotelid= $this->headerinfo['hotelId'];
        $versionCode = $this->headerinfo['X-VERSION'];
        
        if(empty($box_mac) || empty($hotelid) || empty($versionCode)){
            $this->to_back(1001);
        }
        $redis = \SavorRedis::getInstance();
        $redis->select(15);
        $cache_key = 'savor_hotel_'.$hotelid;
        $redis_hotel_info = $redis->get($cache_key);
        $hotel_info = json_decode($redis_hotel_info,true);
        
        $redis->select(10);
        $cache_key = config('cache.prefix').'apk:'.$hotelid.":".$box_mac;
        $redis_result = $redis->get($cache_key);
        if(empty($redis_result)){
            $device_type = 2;
            $m_device_upgrade = new \app\small\model\DeviceUpgrade();
            $upgrade_info = $m_device_upgrade->getLastUpgradeInfo($hotelid,$versionCode,$device_type);
            
            if(!empty($upgrade_info)){
                $m_device_version = new \app\small\model\DeviceVersion();
                $where = array();
                $where['version_code'] =$upgrade_info['version'];
                $where['device_type']  =$device_type;
                $fields = 'version_code,device_type,oss_addr,md5 ';
            
                $device_version_info = $m_device_version->getInfo($where,$fields);
                if(!empty($device_version_info)){
                    $data = array();
                    $data['isApkForceUpgrade'] = $upgrade_info['update_type'];
                    $data['isRomForceUpgrade'] = 0;
                    $data['isApkPromptUpgrade']= 0;
                    $data['isRomPromptUpgrade']= 0;
                    $data['newestApkVersion']  = $device_version_info['version_code'];
                    $data['newestRomVersion']  = '';
                    $data['apkUrl']            = '';
                    $data['romUrl']            = '';
                    $data['apkMd5']            = $device_version_info['md5'];
                    $data['romMd5']            = '';
                    $data['areaId']            = $hotel_info['area_id'];
                    $data['oss_path']          = $device_version_info['oss_addr'];
                    $redis->set($cache_key, json_encode($data),$this->expire);
                    $this->to_back($data);
                }else {
                    $this->to_back(10107);
                }
            }else {
                $this->to_back('10106');
            }
        }else {
            $data = json_decode($redis_result,true);
            $this->to_back($data);
        }
        
        
    }
}