<?php
namespace app\small\model;

class ProgramMenuItem extends Base
{
    protected $table = 'savor_programmenu_item';
    /**
     * @desc 获取节目单的节目资源列表
     * @param  int $menuid 节目单id
     * @return array
     */
    public function getMenuInfo($menuid){
        $field = "media.id AS id,
				  media.oss_addr AS name,
				  media.md5 AS md5,
				  substr(media.oss_addr,16) as name,
				  case item.type
				  when 2 then 'pro' END AS type,
				  media.oss_addr AS oss_path,
				  media.duration AS duration,
				  media.surfix AS suffix,
				  item.sort_num AS 'order',
				  item.ads_name AS chinese_name,
	              ads.is_sapp_qrcode,
                 ads.resource_type media_type";
        $sql = "select ".$field;
    
        $sql .= "  FROM savor_ads ads LEFT JOIN savor_programmenu_item item
        on ads.id = item.ads_id
        LEFT JOIN savor_media media on media.id = ads.media_id
        where
        ads.state=1
        and item.menu_id=$menuid
        and item.type = 2
        and media.oss_addr is not null";
        $result = $this->query($sql);
        return $result;
    }
    /**
     * @desc 获取节目单的占位符
     * @param int $menuid  节目单id
     * @param str $type_str 占位符类型字符串
     * @return array
     */
    public function getMenuAdsPlaceholder($menuid,$type_str){
        $sql ="SELECT `ads_name` AS `chinese_name` ,  `location_id`,`sort_num` AS `order`,
        case type
        when 1 then 'ads'
        when 3 then 'adv'
        when 4 then 'shopgoods'
        when 5 then 'poly'
        when 6 then 'actgoods'
        when 7 then 'selectcontent'
        END AS type
        FROM savor_programmenu_item WHERE menu_id=$menuid and  type in($type_str) order by sort_num asc";
        $result = $this->query($sql);
        return $result;
    }
    /**
     * @desc 获取酒楼节目单对应的宣传片
     */
    public function getadvInfo($hotelid,$menuid){
        $field = "media.id AS id,
				  substr(media.oss_addr,16) as name,
				  media.md5 AS md5,
				  case item.type
				  when 3 then 'adv' END AS type,
				  media.oss_addr AS oss_path,
				  media.duration AS duration,
				  media.surfix AS suffix,
				  item.sort_num AS 'order',
				  item.ads_name AS chinese_name,
	              ads.is_sapp_qrcode,
                  ads.resource_type media_type";
        $sql = "select ".$field;
         
        $sql .= " FROM savor_ads ads
        LEFT JOIN savor_programmenu_item item on ads.name like CONCAT('%',item.ads_name,'%')
        LEFT JOIN savor_media media on media.id = ads.media_id
        where item.menu_id={$menuid}
        and ads.hotel_id={$hotelid}
        and item.type=3
        and (item.ads_id is null or item.ads_id=0)
        and ads.state=1
        and media.oss_addr is not null order by item.sort_num asc";
        $result = $this->query($sql);
        return $result;
    }
}