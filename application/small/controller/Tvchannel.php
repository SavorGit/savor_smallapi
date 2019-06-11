<?php
namespace app\small\controller;

use app\common\controller\Base;

class Tvchannel extends Base{

    function _init_() {
        switch($this->action) {
            case 'reportdata':
                $this->is_verify = 0;
                break;
            case 'getchannellist':
                $this->is_verify = 0;
                break;
        }
        parent::_init_();
    }

    public function getchannellist(){
        $hotel_id = $this->headerinfo['hotelId'];
        $m_hotel = new \app\small\model\Hotel();
        $res_hotel = $m_hotel->getInfo(array('id'=>$hotel_id),'hotel_box_type');
        $box_type = $res_hotel['hotel_box_type'];//1一代单机版,2二代网络版,3二代5G版,4二代单机版,5三代单机版,6三代网络版,
        if(!in_array($box_type,array(2,6))){
            $this->to_back(10300);
        }
        $box_type = $box_type==6?3:$box_type;
        $m_tvchannel = new \app\small\model\Tvchannel();
        $fields = 'a.channel_name,a.freq,a.hotel_id,a.raw_number,a.play_number,a.is_lock,ext.video_standard,
        ext.audio_standard,ext.display_name,ext.display_number,ext.input_id,ext.is_browsable,ext.provider_data,
        ext.service_id,ext.service_type,ext.type';
        $orderby = 'a.play_number asc';
        $where = array('a.hotel_id'=>$hotel_id,'a.type'=>$box_type);
        $res_tvchannel = $m_tvchannel->getCustomList($fields,$where,$orderby);
        $tvChannelList = array();
        $lockingChannelNum = '';
        foreach ($res_tvchannel as $v){
            if($v['is_lock']){
                $lockingChannelNum = $v['raw_number'];
            }
            if($box_type==2){
                $channel_info = array('freq'=>$v['freq'],'channelName'=>$v['channel_name'],'useNum'=>$v['play_number'],'audioStandard'=>$v['audio_standard'],
                    'videoStandard'=>$v['video_standard'],'flag'=>$v['is_lock'],'id'=>$v['id'],'chennalNum'=>$v['raw_number']);
                $channel_info['tvChannel'] = array('freq'=>$v['freq'],'channelName'=>$v['channel_name'],'useNum'=>$v['play_number'],'audioStandard'=>$v['audio_standard'],
                    'videoStandard'=>$v['video_standard'],'flag'=>$v['is_lock'],'id'=>$v['id'],'channelNum'=>$v['raw_number']);
            }elseif($box_type==3){
                $channel_info = array('channelName'=>$v['channel_name'],'channelNum'=>$v['raw_number'],'displayName'=>$v['display_name'],'displayNumber'=>$v['display_number'],
                    'inputId'=>$v['input_id'],'isBrowsable'=>$v['is_browsable'],'providerData'=>$v['provider_data'],'serviceId'=>$v['service_id'],'serviceType'=>$v['service_type'],
                    'type'=>$v['type'],'flag'=>$v['is_lock'],'useNum'=>$v['play_number'],'id'=>$v['id']);
            }
            $tvChannelList[] = $channel_info;
        }
        $res = array('tvChannelList'=>$tvChannelList,'lockingChannelNum'=>intval($lockingChannelNum));
        $this->to_back($res);
    }


    public function reportdata(){
        $hotel_id = $this->headerinfo['hotelId'];
        $data = $this->params['data'];
        $m_hotel = new \app\small\model\Hotel();
        $res_hotel = $m_hotel->getInfo(array('id'=>$hotel_id),'hotel_box_type');
        $box_type = $res_hotel['hotel_box_type'];//1一代单机版,2二代网络版,3二代5G版,4二代单机版,5三代单机版,6三代网络版,
        if(!in_array($box_type,array(2,6))){
            $this->to_back(10300);
        }
        $box_type = $box_type==6?3:$box_type;
        $m_tvchannel = new \app\small\model\Tvchannel();
        $m_tvchannelext = new \app\small\model\TvchannelExt();
        $where = array('hotel_id'=>$hotel_id,'type'=>$box_type);
        $res_tvchannel = $m_tvchannel->getDataList('id',$where,'id desc');
        if(!empty($res_tvchannel)){
            $tvchannel_ids = array();
            foreach ($res_tvchannel as $v){
                $tvchannel_ids[] = $v['id'];
            }
            $m_tvchannel->delData($where);
            $where_tvchannel = array();
            $where_tvchannel['tvchannel_id'] = array('in',$tvchannel_ids);
            $m_tvchannelext->delData($where_tvchannel);
        }
        foreach ($data as $v){
            $channel_data = array('channel_name'=>$v['channelName'],'hotel_id'=>$hotel_id,'type'=>$box_type);
            if(isset($v['freq'])){
                $channel_data['freq'] = $v['freq'];
            }
            if(isset($v['chennalNum'])){
                $channel_data['raw_number']= $v['chennalNum'];
            }
            if(isset($v['channelNum'])){
                $channel_data['raw_number']= $v['channelNum'];
            }

            $res_channel = $m_tvchannel->addData($channel_data);
            $channel_id = $res_channel['id'];

            if($box_type==2){
                $channel_extdata = array('tvchannel_id'=>$channel_id,'video_standard'=>$v['videoStandard'],'audio_standard'=>$v['audioStandard']);
                $m_tvchannelext->addData($channel_extdata);
            }else{
                //displayName  displayNumber id  inputId  isBrowsable providerData serviceId serviceType type
                $channel_extdata = array('tvchannel_id'=>$channel_id,'display_name'=>$v['displayName'],'display_number'=>$v['displayNumber'],
                    'input_id'=>$v['inputId'],'is_browsable'=>$v['isBrowsable'],'provider_data'=>$v['providerData'],'service_id'=>$v['serviceId'],
                    'service_type'=>$v['serviceType'],'type'=>$v['type']);
                $m_tvchannelext->addData($channel_extdata);
            }
        }
        $res_data = array();
        $this->to_back($res_data);
    }
}
