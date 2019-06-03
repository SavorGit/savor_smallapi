<?php
//缓存设置

//redis缓存配置
$redis = array();
$redis['db1']['0']['host'] = Env::get('redis_master_host');
$redis['db1']['0']['port'] = Env::get('redis_port');
$redis['db1']['0']['password'] = Env::get('redis_password');
$redis['db1']['0']['isMaster'] = '1';
$redis['db1']['1']['host'] = Env::get('redis_slave_host');
$redis['db1']['1']['port'] = Env::get('redis_port');
$redis['db1']['1']['password'] = Env::get('redis_password');
$redis['db1']['1']['isMaster'] = '0';
return [
    // 驱动方式
    'type'   => 'File',
    // 缓存保存目录
    'path'   => '',
    // 缓存前缀
    'prefix' => 'vsmall:',
    // 缓存有效期 0表示永久缓存
    'expire' => 86400,

    'redis_config' => $redis,
];
