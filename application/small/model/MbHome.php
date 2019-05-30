<?php
namespace app\small\model;

class MbHome extends Base
{
    public function getvodInfo(){
        $sql = "SELECT
		        media.id AS id,
                substr(media.oss_addr,16) as name,
                con.vod_md5 AS md5,
                'vod' AS type,
                media.oss_addr AS oss_path,
                con.duration AS duration,
                media.surfix AS suffix,
                home.sort_num AS sortNum,
                con.title AS chinese_name
                FROM savor_mb_home home
                LEFT JOIN savor_mb_content con on home.content_id=con.id
                LEFT JOIN savor_media media on media.id = con.media_id
                where
                home.state=1
                and con.state=2
                and con.type=3
                and home.is_demand=1
                and media.oss_addr is not null
                and (((con.bespeak=1 or con.bespeak=2) and 1=1) or con.bespeak=0 or con.bespeak is NULL)
                order by home.sort_num asc 
        ";
        $result = $this->query($sql);
        return $result;
    }
}