<?php
/**+----------------------------------------------------------------------
 * JamesPi Redis [php-redis]
 * +----------------------------------------------------------------------
 * Redis Cache Service Configuration file
 * +----------------------------------------------------------------------
 * Copyright (c) 2020-2030 http://www.pijianzhong.com All rights reserved.
 * +----------------------------------------------------------------------
 * Author：PiJianZhong <jianzhongpi@163.com>
 * +----------------------------------------------------------------------
 */

namespace Jamespi\Redis\Controller;

use Jamespi\Redis\Logic\RedisCache as RedisCacheLogic;
use Jamespi\Redis\Server\RedisCache as RedisCacheServer;
class RedisCache
{
    /**
     * 服务配置参数
     * @var
     */
    protected $config = [];
    /**
     * 数据库配置参数
     * @var
     */
    protected $mysql = [];
    /**
     * 其他请求参数
     * @var
     */
    protected $paramsData = [];

    /**
     * RedisCache constructor.
     * @param array $config 配置参数
     * @param array $mysql 数据库配置参数
     */
    public function __construct(array $config, array $mysql)
    {
        $this->config = $config;
        $this->mysql = $mysql;
        $this->paramsData['result_value'] = '-1';
    }

    /**
     * 更新缓存
     * @param array $arguments 请求参数
     * @return string
     */
    public function write(array $arguments):string
    {
        foreach ($arguments as $key=>$value){
            switch ($key){
                case 'cache_mode':
                    if (!empty($value))
                        $this->paramsData['cache_mode'] = (int)$value;
                    break;
                case 'key':
                    if (!empty($value))
                        $this->paramsData['key'] = (string)$value;
                    break;
                case 'msg':
                    if (!empty($value))
                        $this->paramsData['msg'] = (string)$value;
                    break;
                case 'type':
                    if (!empty($value))
                        $this->paramsData['type'] = (string)$value;
                    break;
            }
        }
        $redisServer = new RedisCacheLogic(new RedisCacheServer($this->config));
        return $redisServer->write($this->config, $this->mysql, $this->paramsData);
    }

    /**
     * 读取缓存
     * @param array $arguments 请求参数
     * @return string
     */
    public function read(array $arguments):string
    {
        foreach ($arguments as $key=>$value){
            switch ($key){
                case 'cache_mode':
                    if (!empty($value))
                        $this->paramsData['cache_mode'] = (int)$value;
                    break;
                case 'key':
                    if (!empty($value))
                        $this->paramsData['key'] = (string)$value;
                    break;
                case 'field':
                    if (!empty($value))
                        $this->paramsData['field'] = (string)$value;
                    break;
                case 'type':
                    if (!empty($value))
                        $this->paramsData['type'] = (string)$value;
                    break;
                case 'cache_timeout':
                    if (!empty($value))
                        $this->paramsData['cache_timeout'] = (int)$value;
                    break;
            }
        }

        $redisServer = new RedisCacheLogic(new RedisCacheServer($this->config));
        return $redisServer->read($this->config, $this->mysql, $this->paramsData);
    }
}