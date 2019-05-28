<?php
class RecordLog {
    
	/**
     * 写日志
     * @param string $content 日志内容
     * @return string 成功返回字符串
     */
	public static function addLog($content){
		$queryFile = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : join(" ",$_SERVER['argv']);
		$content = date("Y-m-d H:i:s").'[url]'.$queryFile.'[resp_result]'.$content.'[client_ip]'.get_client_ipaddr().'[server_ip]'.$_SERVER['SERVER_ADDR']."\n";
		$path = env('root_path');
		$log_file_name = $path.'runtime/log/'.'api_error_'.date("Ymd").".log";
		@file_put_contents($log_file_name, $content, FILE_APPEND);
		return true;
	}
	
    /**
     * curl请求日志
     * @param obj $ch
     * @param string $url
     * @param array $post_data
     * @param string $method
     * @return string 成功返回字符串
     */
    public static function add_curl_log($ch, $url, $start_time, $end_time, $post_data=array(),$method='get',$result){
        $calc_time = $end_time - $start_time;
        $url_info = parse_url($url);
        if(is_string($post_data)){
            $post_data = json_decode($post_data,true);
            if(!is_array($post_data)){
                parse_str($post_data, $post_data);
            }
        }
        if(!empty($url_info['query'])){
            parse_str($url_info['query'], $arr);
            if(!empty($arr)){
                $post_data = array_merge($post_data, $arr);
            }
        }
        $http_info = is_resource($ch) ? curl_getinfo($ch) : array();
        $path = $url_info['path'];
        if(!empty($path) && strpos($path, 'get_num') !== false){
            $url_info['path'] = substr($path, 0, strpos($path, 'get_num') + strlen('get_num'));
            $post_data []  = $url;
        }
        $url = 'http://'.$url_info['host'].$url_info['path'];
        $data ['start_time'] = $start_time;
        $data ['end_time'] = $end_time;
        $data ['total_time'] = round($calc_time, 4);
        $data ['url'] = $url;
        $data ['method'] = $method;
        $data['traceinfo']   =  '';
        $data ['params'] = json_encode($post_data);
        $data['resp_result'] = $result;
        $data['client_ip']   = get_client_ipaddr();
        $data['server_ip']   = $_SERVER['SERVER_ADDR'];
        $log_time = date("Y-m-d H:i:s");
        $content = $log_time;
        foreach($data as $k=>$v){
            $content.= "[$k]".$v;
        }
		$content .= "\n";
        $path = env('root_path');
        $log_file_name = $path.'runtime/log/api_server2service_'.date("Ymd").".log";
		file_put_contents($log_file_name, $content, FILE_APPEND);
        return true;
    }

    public static function add_client_api_log($url,$method,$traceinfo = array(),$param = array(), $resp_result,$start_time,$end_time){
    	$content = '';
    	$calc_time = $end_time - $start_time;
        $data = array();
    	$data['start_time']  = $start_time;
    	$data['end_time']    = $end_time;
    	$data['total_time']  = round($calc_time, 4);
    	$data['url']         = $url;
    	$data['method']      = $method;
    	$data['traceinfo']   =  '';
    	$data['params']      = json_encode($param);
    	$data['resp_result'] = $resp_result;
    	$data['client_ip']   = get_client_ipaddr();
    	$data['server_ip']   = $_SERVER['SERVER_ADDR'];
    	$log_time = date("Y-m-d H:i:s");
    	$content = $log_time;
    	foreach($data as $k=>$v){
    		$content.= "[$k]".$v;
    	}
    	$content .= "\n";
        $path = env('root_path');
        $log_file_name = $path.'runtime/log/api_client_'.date("Ymd").".log";
    	file_put_contents($log_file_name, $content, FILE_APPEND);
    	return true;
    }
}
?>