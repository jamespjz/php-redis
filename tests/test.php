<?php
require_once 'vendor/autoload.php';

use Jamespi\Redis\Controller\RedisLock;

$servers = [
    [
        ['192.168.87.54', '7100', 3, 1.5],
        ['192.168.87.56', '7000', 3, 1.5],
        ['192.168.87.194', '7200', 3, 1.5],
    ],
    'auth' => '123456'
];
//获取分布式锁
$params = [
    'token_key' => 'redis_key',
    'acquire_number' => 3,
    'requests_number' => 3,
    'lock_timeout' => 300 //单位s
];
$bb = (new RedisLock($servers))->acquireLock($params);
var_dump($bb);
//释放分布式锁
$params1 = [
    'token_key' => 'redis_key',
    'identifier' => '4a3068a14f90554383dcaedf59c367a3',
];
//$aa = (new RedisLock($servers))->unlock($params1);
//检测分布式锁key是否已被定义
$params2 = [
    'token_key' => 'redis_key'
];
//(new RedisLock($servers))->isLock($params2);
