<?php
namespace app\small\model;

class PubAdsBox extends Base
{
    public function getAdsList($box_id){
        
        $now_date = date('Y-m-d H:i:s');
        $data = $this->alias('a')
        ->leftJoin('savor_pub_ads b',  'a.pub_ads_id= b.id')
        ->leftJoin('savor_ads c ' ,'b.ads_id=c.id')
        ->leftJoin('savor_media d ','c.media_id=d.id')
        ->field("b.id pub_ads_id,b.create_time,d.id,substr(d.oss_addr,16) as name,
                 d.md5 AS md5,'easyMd5' AS md5_type,c.name AS chinese_name,
    			 'ads' AS `type`,
				 d.oss_addr AS oss_path,
				 d.duration AS duration,
				 d.surfix AS suffix,b.start_date,
                 b.end_date,a.location_id,c.is_sapp_qrcode,
                 c.resource_type media_type")
    	->where('a.box_id='.$box_id." and b.start_date<='".$now_date."' and b.end_date>'".$now_date.  
    	         "' and b.state=1 and c.state=1 and d.oss_addr is not null")
    	->order('b.start_date asc')
    	->select();
        return $data;
    }
    public function getBoxPorid($box_id){
        $now_date = date('Y-m-d H:i:s');
        $data = $this->alias('a')
        ->leftJoin('savor_pub_ads b',  'a.pub_ads_id= b.id')
        ->field("b.create_time")
        ->where('a.box_id='.$box_id." and b.end_date>'".$now_date.
                "' and b.state=1 ")
        ->order('b.id desc')
        ->limit(1)
        ->find();
        return $data;
    }
}