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
     * 设置string类型变量
     * @param array $arguments 请求参数
     * @return string
     */
    public function write(array $arguments):string
    {
        foreach ($arguments as $key=>$value){
            switch ($key){
                case 'cache_mode':
                    if (is_int($value) && !empty($value))
                        $this->paramsData['cache_mode'] = $value;
                    break;
                case 'key':
                    if (is_int($value) && !empty($value))
                        $this->paramsData['key'] = $value;
                    break;
                case 'msg':
                    if (is_int($value) && !empty($value))
                        $this->paramsData['msg'] = $value;
                    break;
                case 'type':
                    if (is_int($value) && !empty($value))
                        $this->paramsData['type'] = $value;
                    break;
            }
        }
        $redisServer = new RedisCacheLogic(new RedisCacheServer());
        $redisServer->write($this->config, $this->mysql, $this->paramsData);
    }

    public function read():string
    {

    }
}