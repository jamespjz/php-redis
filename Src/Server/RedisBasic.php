<?php
/**+----------------------------------------------------------------------
 * JamesPi Redis [php-redis]
 * +----------------------------------------------------------------------
 * Redis Basic Service Configuration file
 * +----------------------------------------------------------------------
 * Copyright (c) 2020-2030 http://www.pijianzhong.com All rights reserved.
 * +----------------------------------------------------------------------
 * Author：PiJianZhong <jianzhongpi@163.com>
 * +----------------------------------------------------------------------
 */

namespace Jamespi\Redis\Server;

use Redis;
use RedisCluster;
abstract class RedisBasic
{
    /**
     * 服务配置项
     * @var mixed
     */
    protected $config;


    /**
     * Redis服务器链接
     * @var
     */
    protected static $redisInstance;

    public function __construct(array $config)
    {
        $this->config = $config;
        if (is_null(self::$redisInstance))
            self::$redisInstance = $this->_redisConnect($this->config);
    }

    /**
     * Redis服务器连接
     * @param array $config
     * @return Redis|RedisCluster
     */
    private function _redisConnect(array $config)
    {
        $redis_setting = 1;
        $host = '127.0.0.1';
        $port = 6379;
        $auth = '123456';
        foreach ($config as $key=>$value){
            switch ($key){
                case 'host':
                    if (!empty($value))
                        $host = (string)$value;
                    break;
                case 'port':
                    if (!empty($value))
                        $port = (int)$value;
                    break;
                case 'auth':
                    if (!empty($value))
                        $auth = (string)$value;
                    break;
                case 'redis_setting':
                    if (!empty($value))
                        $redis_setting = (int)$value;
                    break;
            }
        }

        if ($redis_setting == 1){
            //单机redis
            $redis = new Redis();
            $redis->connect($host, $port, 1.5);
            $redis->auth($auth);
        }else{
            //集群redis
            $redis = new RedisCluster(null, [$host.":".$port], 1.5, 1.5, true, $auth);
        }
        return $redis;
    }
}