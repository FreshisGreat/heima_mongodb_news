<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
use MongoDB\Client;
// 应用公共文件
// Redis数据库连接
if(!function_exists('getRedis')){
    // 连接redis
    function getRedis(){
        //连接$redis数据库
        $redis = new \Redis();
        //读取config.php文件中的redis配置
        $config_redis = config('redis');
        $redis->connect($config_redis['host'],$config_redis['port'],$config_redis['timeout']);
        //进行数据库口令验证
        $redis->auth($config_redis['auth']);
        return $redis;
    }
}

if(!function_exists('getMongoDB')){
    
    //连接MongoDB
    function getMongoDB(){
        $client = new Client('mongodb://php:001024Wyh@127.0.0.1:27017/php');
        //得到数据库
        $db = $client->php;
        //返回数据库对象
        return $db;
    }
}



if(!function_exists('api')){
    function api(array $ret = [], int $httpCode = 200){
        return json([
            'status' => 200,
            'msg' => '成功',
            'ret' => $ret
        ],$httpCode,[
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PATCH, PUT, DELETE',
            'Access-Control-Allow-Headers' => 'Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With'
        ]);
    }
}

if(!function_exists('getusername')){
    function getusername(){
        //获取用户名
        $session = $_SESSION['think'];
        // var_dump($session);
        reset($session);
        // echo '<br>';

        $hostname = current($session);
        // var_dump($hostname);
        reset($hostname);
        // echo '<br>';

        $username = key($hostname);
        // var_dump($username);
        return $username;
    }
}
