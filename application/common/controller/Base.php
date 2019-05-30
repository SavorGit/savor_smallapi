<?php
namespace app\common\controller;
use think\Controller;
use think\facade\Request;

class Base extends Controller{
    protected $action = '';
    protected $is_verify = 1;//检验接口 0不校验 1校验
    protected $method = '';//请求方式 GET POST
    protected $params = array();
    protected $valid_fields = array(); //数据有效性验证(必参)
    protected $headerinfo = array();
    protected $start_time = '';
    protected $end_time = '';
    protected $expire ;

    public function __construct(){
        parent::__construct();
        $this->expire = config('cache.expire');
        $this->action = Request::action();
        $this->_init_();
    }

    /*
    * 初始化请求数据
    *
    */
    protected function _init_() {
        $this->start_time = microtime(true);
        if($this->method){
            switch ($this->method){
                case 'get':
                    $this->params = $_GET;
                    break;
                case 'post':
                    $get_params = array('time'=>$_GET['time'],'sign'=>$_GET['sign']);
                    $input_params = file_get_contents('php://input');
                    if(empty($input_params)){
                        $input_params=array();
                    }else{
                        $input_params = json_decode($input_params, true);
                    }
                    $this->params = array_merge($get_params,$_POST,$input_params);
                    break;
            }
        }else{
            $input_params = file_get_contents('php://input');
            if(empty($input_params))  $input_params=array();
            $this->params = array_merge($input_params,$_GET,$_POST);
        }

        $this->header_file();

        if($this->is_verify){
            if(empty($this->params)){
                $this->to_back(1001);
            }else{
                //$this->check_sign($this->params);
                if(!empty($this->valid_fields)){
                    foreach ($this->valid_fields as $key=>$value){
                        $tv = trim($this->params[$key]);
                        if(!in_array($key, array('encryptedData','iv'))){
                            $this->params[$key] = addslashes($tv);
                        }
                        if($value == 1001){//验证参数不能为空
                            if(empty($tv) && "0" != strval($tv)){
                                $this->to_back($value);
                            }
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * 校验签名
     */
    protected function check_sign($params) {
        if (!empty($params['time']) && !empty($params['sign'])){
            $sign = $params['sign'];
            $compare = gen_params_sign($params);
            if (empty($sign) || $compare['sign'] != $sign) {
                $app_debug = env('app_debug');
                if($app_debug){
                    $this->to_back($compare);
                }else{
                    $this->to_back(1007);
                }
            }
            $request_time = $params['time'];
            $now_time = time();
            $api_expiretime = config('api_expire_time');
            if($request_time+$api_expiretime<$now_time){
                $this->to_back(1002);
            }
            unset($compare);
        }else{
            $this->to_back(1007);
        }
        return true;
    }


    private function header_file(){
        if(isset($_SERVER['HTTP_TRACEINFO'])){
            $traceinfo = $_SERVER['HTTP_TRACEINFO'];//versionname=;versioncode=;macaddress=;buildversion=;systemtimezone=
            $traceinfo_arr = explode(';', $traceinfo);
            foreach ($traceinfo_arr as $v){
                $info = explode('=', $v);
                $this->headerinfo[$info[0]] = $info[1];
            }
        }
        if($this->is_verify){
            if(empty($this->headerinfo)){
                $this->to_back(1003);
            }
        }
        $this->headerinfo['boxMac'] = isset($_SERVER['HTTP_BOXMAC'])?$_SERVER['HTTP_BOXMAC']:'';
        $this->headerinfo['hotelId'] = isset($_SERVER['HTTP_HOTELID'])?$_SERVER['HTTP_HOTELID']:0;
        $this->headerinfo['X-VERSION'] = isset($_SERVER['HTTP_X_VERSION'])?$_SERVER['HTTP_X_VERSION']:'';
        return true;
    }

    /**
     * @param  $data
     * @param  $type 1为明文json 2为加密
     */
    public function to_back($data,$type=1) {
        $apiResp = new \ApiResp();
        $errorinfo = config('errorcode.');
        if(is_numeric($data)){
            $resp_msg = $errorinfo[$data];
            $resp_code = $data;
            $resp_result = new \stdClass();
        }elseif(is_array($data)){
            $resp_code = $apiResp->code;
            $resp_msg = $errorinfo[$apiResp->code];
            $resp_result = !empty($data)?$data:new \stdClass();
        }elseif(is_object($data)){
            $resp_code = $apiResp->code;
            $resp_msg = $errorinfo[$apiResp->code];
            $resp_result = $data;
        }
        $apiResp->code = $resp_code;
        $apiResp->msg = $resp_msg;
        $apiResp->result = $resp_result;
        $result = json_encode($apiResp);
        $url='http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
        $this->end_time = microtime(true);
        $is_log = env('api_log');
        if($is_log){
            \RecordLog::add_client_api_log($url,$_SERVER['REQUEST_METHOD'],$this->headerinfo, $this->params, $result,$this->start_time,$this->end_time);
        }
        if($type == 2){
            $encry = encrypt_data($result);
            header('des:true');
            echo $encry;
        }else{
            //entity 实体, virtual 虚拟
            header('X-SMALL-TYPE:virtual');
            echo $result;
        }
        exit;
    }
    public function getHotelInfo($box_mac){
        $m_box = new \app\small\model\Box();
        
        
        $where = array();
        $where['mac']  = $box_mac;
        $where['flag'] = 0;
        $where['state']= 1;
        $box_result = $m_box->getInfo($where,'id box_id');
        $redis  = \SavorRedis::getInstance();
        $redis->select(15);
        $cache_key = 'savor_box_'.$box_result['box_id'];
        $redis_box_info = $redis->get($cache_key);
        $box_info = json_decode($redis_box_info,true);
        $result = [];
        if(empty($box_info)){
            $fields = 'a.state box_state,a.flag box_flag,hotel.area_id,a.id box_id,a.mac box_mac,a.name box_name,
                   a.switch_time,a.volum,hotel.id hotel_id,hotel.name hotel_name,hotel.addr address,hotel.contractor linkman,
                   hotel.tel,ext.server_location server,ext.mac_addr mac,hotel.level,hotel.iskey key_point,a.tpmedia_id,
                   hotel.install_date,hotel.state hotel_state,hotel.state_change_reason state_reason,hotel.remark,hotel.create_time,
                   hotel.update_time,hotel.flag hotel_flag,hotel.hotel_box_type,
                   room.id room_id,room.name room_name,room.type room_type,room.probe,room.flag room_flag,room.state room_state';
            $where = array();
            $where['mac'] = $box_mac;
            $where['a.flag']= 0;
            $where['a.state'] = 1;
            $where['hotel.flag'] = 0;
            $where['hotel.state']= 1;
            $result = $m_box->getHotelBoxInfo($fields, $where);
        }else {
            $cache_key = 'savor_room_'.$box_info['room_id'];
            $redis_room_info = $redis->get($cache_key);
            $room_info = json_decode($redis_room_info,true);
            $cache_key = 'savor_hotel_'.$room_info['hotel_id'];
            $redis_hotel_info = $redis->get($cache_key);
            $hotel_info = json_decode($redis_hotel_info,true);
            $cache_key = 'savor_hotel_ext_'.$room_info['hotel_id'];
            $redis_hotel_ext_info = $redis->get($cache_key);
            $hotel_ext_info = json_decode($redis_hotel_ext_info,true);
        
            $result['box_state'] = $box_info['state'];
            $result['box_flag']  = $box_info['flag'];
            $result['area_id']   = $hotel_info['area_id'];
        
            $result['box_id']    = $box_result['box_id'];
            $result['box_mac']   = $box_mac;
            $result['box_name']  = $box_info['name'];
            $result['switch_time']= $box_info['switch_time'];
            $result['volum']     = $box_info['volum'];
            $result['hotel_id']  = $room_info['hotel_id'];
            $result['hotel_name']= $hotel_info['name'];
            $result['address']   = $hotel_info['addr'];
            $result['linkman']   = $hotel_info['contractor'];
            $result['tel']       = $hotel_info['tel'];
            $result['server']    = $hotel_ext_info['server_location'];
            $result['mac']       = $hotel_ext_info['mac_addr'];
            $result['level']     = $hotel_info['level'];
            $result['key_point'] = $hotel_info['iskey'];
            $result['tpmedia_id']= $box_info['tpmedia_id'];
            $result['install_date']= $hotel_info['install_date'];
            $result['hotel_state']=$hotel_info['state'];
            $result['state_reason']= $hotel_info['state_change_reason'];
            $result['remark']    = $hotel_info['remark'];
            $result['create_time']= $hotel_info['create_time'];
            $result['update_time']= $hotel_info['update_time'];
            $result['hotel_flag'] = $hotel_info['flag'];
            $result['hotel_box_type'] = $hotel_info['hotel_box_type'];
            $result['room_id']    = $box_info['room_id'];
            $result['room_name']  = $room_info['name'];
            $result['room_type']  = $room_info['type'];
            $result['probe']      = $room_info['probe'];
            $result['room_flag']  = $room_info['flag'];
            $result['room_state'] = $room_info['state'];
        
        }
        
        if(empty($result)){
            $this->to_back(10100);
        }
        return $result;
    }

    public function __destruct(){

    }
}
