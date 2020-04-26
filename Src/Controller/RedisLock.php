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
use RedisCluster;
use Jamespi\Redis\Server\RedisLock as RedisLockServer;
use Jamespi\Redis\Logic\RedisLock as RedisLockLogic;
use Jamespi\Redis\Common\Common;
class RedisLock
{
    /**
     * 服务配置参数
     * @var
     */
    protected $config;
    /**
     * redis分布式锁key
     * @var
     */
    protected $token_key;
    /**
     * redis分布式锁key值
     * @var
     */
    protected $identifier;
    /**
     * redis分布式锁获取次数
     * @var
     */
    protected $acquire_number = 3;
    /**
     * 请求分布式锁超时时间（微妙）
     * @var
     */
    protected $acquire_timeout = 1000000;
    /**
     * redis分布式锁超时时间（s）
     * @var
     */
    protected $lock_timeout = 60;
    /**
     * Redis服务器链接
     * @var
     */
    protected $instance;

    /**
     * RedisLock constructor.
     * @param array $config 配置参数
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->instance = $this->connect($this->config);
    }

    /**
     * Redis服务器连接
     * @param array $config
     * @return Redis|RedisCluster
     */
    public function connect(array $config)
    {
        $redis_setting = 1;
        $host = '127.0.0.1';
        $port = 6379;
        $auth = '123456';
        foreach ($config as $key=>$value){
            switch ($key){
                case 'host':
                    if (is_string($value) && !empty($value))
                        $host = $value;
                    break;
                case 'port':
                    if (is_int($value) && !empty($value))
                        $port = $value;
                    break;
                case 'auth':
                    if (is_string($value) && !empty($value))
                        $auth = $value;
                    break;
                case 'redis_setting':
                    if (is_int($value) && !empty($value))
                        $redis_setting = $value;
                    break;
            }
        }

        if ($redis_setting == 1){
            //单机redis
            $redis = new Redis();
            $redis->connect($host, $port);
            $redis->auth($auth);
        }else{
            //集群redis
            $redis = new RedisCluster(null, [$host.":".$port], 1.5, 1.5, true, $auth);
        }
        return $redis;
    }

    /**
     * 获取分布式锁
     * @param array $arguments 请求参数
     * @return mixed|void
     */
    public function acquireLock(array $arguments)
    {
        foreach ($arguments as $key=>$value){
            switch ($key){
                case 'token_key':
                    if (is_string($value) && !empty($value))
                        $this->token_key = $value;
                    break;
                case 'acquire_number':
                    if (is_int($value) && !empty($value))
                        $this->acquire_number = $value;
                    break;
                case 'acquire_timeout':
                    if (is_int($value) && !empty($value))
                        $this->acquire_timeout = $value;
                    break;
                case 'lock_timeout':
                    if (is_int($value) && !empty($value))
                        $this->lock_timeout = $value;
                    break;
            }
        }

        if(!empty($this->acquire_timeout) && $this->acquire_timeout > 1000000){
            return Common::resultMsg('failed', '请求超时时间设置过长，为不影响性能建议低于1秒');
        }

        if (empty($this->config['host']) || empty($this->config['port']) || empty($this->token_key) || empty($this->lock_timeout)){
            return Common::resultMsg('failed', '缺少请求必要参数');
        }

        if (!$this->ping($this->config['host'], $this->config['port'])){
            return Common::resultMsg('failed', 'REDIS服务器链接不上');
        }

        //调用获取分布式锁业务
        $redisService = new RedisLockLogic(new RedisLockServer());
        $result = $redisService->acquireLock($this->instance, $this->token_key, $this->acquire_number, $this->acquire_timeout, $this->lock_timeout, $this->config);
        return $result;
    }

    /**
     * 释放分布式锁
     * @param array $arguments 请求参数
     * @return mixed|void
     */
    public function unLock(array $arguments)
    {
        foreach ($arguments as $key=>$value){
            switch ($key){
                case 'token_key':
                    if (is_string($value) && !empty($value))
                        $this->token_key = $value;
                    break;
                case 'identifier':
                    if (is_string($value) && !empty($value))
                        $this->identifier = $value;
                    break;
            }
        }

        if (empty($this->config['host']) || empty($this->config['port']) || empty($this->token_key) || empty($this->identifier)){
            return Common::resultMsg('failed', '缺少请求必要参数');
        }

        if (!$this->ping($this->config['host'], $this->config['port'])){
            return Common::resultMsg('failed', 'REDIS服务器链接不上');
        }

        //调用获取分布式锁业务
        $redisService = new RedisLockLogic(new RedisLockServer());
        $result = $redisService->unLock($this->instance, $this->token_key, $this->identifier);
        return $result;
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