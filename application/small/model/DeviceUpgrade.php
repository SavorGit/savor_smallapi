<?php
namespace app\small\model;

class DeviceUpgrade extends Base{

    public function getLastUpgradeInfo($hotelid,$versionCode = '',$device_type=1){
        $where = '';
        if(!empty($versionCode)){
            $where .= " and version_min<='".$versionCode."' and version_max>='".$versionCode."'";
        }
        $sql =" select id,version,update_type from savor_device_upgrade where device_type=$device_type 
	            and  (hotel_id LIKE '%,{$hotelid},%' OR hotel_id IS NULL) $where order by id desc limit 1";
        $result = $this->query($sql);
        return $result[0];
    }
}