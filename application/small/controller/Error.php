<?php
namespace app\small\controller;
class Error{

    public function index(){
        $errorinfo = array('code'=>1008,'msg'=>'Interface does not exist');
        echo json_encode($errorinfo);
        exit;
    }
}
