<?php
namespace app\small\controller;

use app\common\controller\Base;

class Test extends Base{

    function _init_() {
        switch($this->action) {
            case 'index':
                $this->is_verify = 0;
                $this->method = 'get';
                break;
        }
        parent::_init_();
    }

    public function index(){
        $key_box = 'smallapp:forscreen:box0527';
        $redis  = \SavorRedis::getInstance();
        $redis->select(5);
        $resbox = $redis->get($key_box);
        if(empty($resbox)){
            $m_box = new \app\small\model\Box();
            $where = array('state'=>1,'flag'=>0);
            $res = $m_box->getBoxlist($where);
            $redis->set($key_box,json_encode($res),600);
        }else{
            $res = json_decode($resbox,true);
        }
        $data = array('boxlist'=>$res);
        $this->to_back($data);
    }
}
