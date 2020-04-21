<?php
/**+----------------------------------------------------------------------
 * JamesPi Redis [php-redis]
 * +----------------------------------------------------------------------
 * Redis Controller Configuration file
 * +----------------------------------------------------------------------
 * Copyright (c) 2020-2030 http://www.pijianzhong.com All rights reserved.
 * +----------------------------------------------------------------------
 * Author：PiJianZhong <jianzhongpi@163.com>
 * +----------------------------------------------------------------------
 */

namespace Jamespi\Redis\Controller;

use Redis;
use Jamespi\Redis\Server\RedisLock as RedisLockServer;
use Jamespi\Redis\Logic\RedisLock as RedisLockLogic;
use Jamespi\Redis\Common\Common;
class RedisLock
{
    /**
     * redis链接地址
     * @var
     */
    protected $host;
    /**
     * redis链接端口
     * @var
     */
    protected $port;
    /**
     * redis链接密码
     * @var
     */
    protected $auth = null;
    /**
     * redis分布式锁key
     * @var
     */
    protected $token_key;
    /**
     * redis分布式锁获取次数
     * @var
     */
    protected $acquire_number = 1;
    /**
     * redis分布式锁超时时间（s）
     * @var
     */
    protected $lock_timeout;
    /**
     * Redis服务器链接
     * @var
     */
    protected $instance;

    /**
     * 链接Reids服务器
     * @param int $redis_setting redis环境
     * @param string $host 链接地址
     * @param int $port 端口
     * @param $auth 密码
     */
    public function connect(int $redis_setting, string $host, int $port, $auth)
    {
        $redis = new \stdClass();
        if ($redis_setting == 1){
            //单机redis
            $redis = new Redis();
            $redis->connect($host, $port);
            $redis->auth($auth);
        }else{
            //集群redis

        }
        return $redis;
    }

    /**
     * 获取分布式锁
     * @param int $redis_setting redis环境
     * @param array $arguments 请求参数
     * @return mixed|void
     */
    public function acquireLock(int $redis_setting, array $arguments)
    {
        foreach ($arguments as $key=>$value){
            switch ($key){
                case 'host':
                    if (is_string($value) && !empty($value))
                        $this->host = $value;
                    break;
                case 'port':
                    if (is_int($value) && !empty($value))
                        $this->port = $value;
                    break;
                case 'auth':
                    if (is_string($value) && !empty($value))
                        $this->auth = $value;
                    break;
                case 'token_key':
                    if (is_string($value) && !empty($value))
                        $this->token_key = $value;
                    break;
                case 'acquire_number':
                    if (is_int($value) && !empty($value))
                        $this->acquire_number = $value;
                    break;
                case 'lock_timeout':
                    if (is_int($value) && !empty($value))
                        $this->lock_timeout = $value;
                    break;
            }
        }

        if (empty($this->host) || empty($this->port) || empty($this->token_key) || empty($this->lock_timeout)){
            return Common::resultMsg('failed', '缺少请求必要参数');
        }

        if ($this->ping($this->host, $this->port)){
            return Common::resultMsg('failed', 'REDIS服务器链接不上');
        }

        //链接服务器
        $this->instance = $this->connect($redis_setting, $this->host, $this->port, $this->auth);
        //调用获取分布式锁业务
        $redisService = new RedisLockLogic(new RedisLockServer());
        $result = $redisService->acquireLock($this->instance, $this->token_key, $this->acquire_number, $this->lock_timeout);
        return $result;
    }

    /**
     * 释放分布式锁
     * @param int $redis_setting redis环境
     * @param array $arguments 请求参数
     * @return mixed|void
     */
    public function unLock(int $redis_setting, array $arguments)
    {
        
    }

    /**
     * 检测IP+端口是否通畅
     * @param string $host 请求地址
     * @param int $port 请求端口
     * @return boolean
     */
    protected function ping(string $host, int $port):bool
    {
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {        //IPv6
            $socket = socket_create(AF_INET6, SOCK_STREAM, SOL_TCP);
        } elseif (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {    //IPv4
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        } else {
            return false;
        }

        if (!isset($port)) {
            //没有写端口则指定为80
            $port = '80';
        }
        @$ok = socket_connect($socket, $host, $port);
        socket_close($socket);

        if ($ok) {
            return true;
        } else {
            return false;
        }
    }
}