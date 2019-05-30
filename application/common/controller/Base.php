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
                $this->check_sign($this->params);
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
        if(isset($_SERVER['traceinfo'])){
            $traceinfo = $_SERVER['traceinfo'];//versionname=;versioncode=;macaddress=;buildversion=;systemtimezone=
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
        $this->headerinfo['boxMac'] = isset($_SERVER['boxMac'])?$_SERVER['boxMac']:'';
        $this->headerinfo['hotelId'] = isset($_SERVER['hotelId'])?$_SERVER['hotelId']:0;
        $this->headerinfo['X-VERSION'] = isset($_SERVER['X-VERSION'])?$_SERVER['X-VERSION']:'';
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

    public function __destruct(){

    }
}
