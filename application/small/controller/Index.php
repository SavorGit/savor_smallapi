<?php
namespace app\small\controller;
use app\common\controller\Base;
class Index extends Base{

    function _init_() {
        switch($this->action) {
            case 'index':
                $this->method = 'get';
                break;
        }
        parent::_init_();
    }

    public function index(){
        $this->to_back(1008);
    }
}
