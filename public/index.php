<?php
namespace think;
// 加载基础文件
require '../../framework/thinkphp5.1/base.php';

// 支持事先使用静态方法设置Request对象和Config对象

// 执行应用并响应
Container::get('app')->run()->send();
