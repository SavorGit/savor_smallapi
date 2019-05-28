<?php
namespace app\small\controller;

use app\common\controller\Base;

class Program extends Base{

    function _init_() {
        switch($this->action) {
            case 'getmenu':
                $this->is_verify = 0;
                $this->method = 'get';
                break;
        }
        parent::_init_();
    }
    public function getmenu(){
        $box_mac = $this->params['boxMac'];
        $m_box = new \app\small\model\Box();
        $fields = 'id';
        $where = array();
        $where['id'] = 15;
        echo "ddd";exit;
        $m_box->getOne($fields, $where);
    }
}