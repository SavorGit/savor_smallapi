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
        $fields = 'a.id,a.channel_name,a.freq,a.hotel_id,a.raw_number,a.play_number,a.is_lock,ext.video_standard,
        ext.audio_standard,ext.display_name,ext.display_number,ext.input_id,ext.is_browsable,ext.provider_data,
        ext.service_id,ext.service_type,ext.type';
        $orderby = 'a.play_number asc';
        $where = array('a.hotel_id'=>$hotel_id,'a.type'=>$box_type);
        $res_tvchannel = $m_tvchannel->getCustomList($fields,$where,$orderby);
        if($res_tvchannel->isEmpty()){
            $this->to_back(10301);
        }

        $tvChannelList = array();
        $lockingChannelNum = '';
        foreach ($res_tvchannel as $v){
            if($v['is_lock']){
                $lockingChannelNum = $v['play_number'];
            }
            if($box_type==2){
                $channel_info = array('freq'=>$v['freq'],'channelName'=>$v['channel_name'],'audioStandard'=>$v['audio_standard'],
                    'videoStandard'=>$v['video_standard'],'chennalNum'=>$v['play_number']);
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
        if(empty($this->params['data'])){
            $this->to_back(1001);
        }
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

        $tvchannel_ids = array();
        foreach ($res_tvchannel as $v){
            $tvchannel_ids[] = $v['id'];
        }
        if(!empty($tvchannel_ids)){
            $m_tvchannel->delData($where);
            $where_tvchannel = array();
            $where_tvchannel['tvchannel_id'] = $tvchannel_ids;
            $m_tvchannelext->delData($where_tvchannel);
        }
        foreach ($data as $v){
            $channel_data = array('channel_name'=>$v['channelName'],'hotel_id'=>$hotel_id,'type'=>$box_type);
            if($box_type==2){
                $channel_data['freq'] = $v['freq'];
                $channel_data['raw_number']= $v['chennalNum'];
                $channel_data['play_number']= $v['chennalNum'];

                $channel_extdata = array('video_standard'=>$v['videoStandard'],'audio_standard'=>$v['audioStandard']);
            }else{
                $channel_data['raw_number']= $v['channelNum'];
                $channel_data['play_number']= $v['channelNum'];
                $providerData = json_decode($v['providerData'],true);
                $channel_data['freq'] = $providerData['fe']['freq'];

                //displayName  displayNumber id  inputId  isBrowsable providerData serviceId serviceType type
                $channel_extdata = array('display_name'=>$v['displayName'],'display_number'=>$v['displayNumber'],
                    'input_id'=>$v['inputId'],'is_browsable'=>$v['isBrowsable'],'provider_data'=>$v['providerData'],'service_id'=>$v['serviceId'],
                    'service_type'=>$v['serviceType'],'type'=>$v['type']);

            }
            $res_channel = $m_tvchannel->addData($channel_data);

            $channel_id = $res_channel['id'];
            $channel_extdata['tvchannel_id'] = $channel_id;
            $m_tvchannelext->addData($channel_extdata);
        }
        $res_data = array();
        $this->to_back($res_data);
    }
}
