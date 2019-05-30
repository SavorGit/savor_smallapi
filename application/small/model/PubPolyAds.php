<?php
namespace app\small\model;

class PubPolyAds extends Base
{
    public function getList($fields,$where, $order='id desc', $limit=''){
        $data = $this->alias('a')
                     ->leftJoin('savor_ads ads','a.ads_id=ads.id')
                     ->leftJoin('savor_media media ',' ads.media_id=media.id')
                     ->field($fields)
                     ->where($where)
                     ->order($order)
                     ->limit($limit)
                     ->select();
        return $data;
    }
}