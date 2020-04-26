<?php
/**+----------------------------------------------------------------------
 * JamesPi Redis [php-redis]
 * +----------------------------------------------------------------------
 * Redis Logic Configuration file
 * +----------------------------------------------------------------------
 * Copyright (c) 2020-2030 http://www.pijianzhong.com All rights reserved.
 * +----------------------------------------------------------------------
 * Author：PiJianZhong <jianzhongpi@163.com>
 * +----------------------------------------------------------------------
 */

namespace Jamespi\Redis\Logic;

use Redis;
use RedisCluster;
use ReflectionClass;
use Jamespi\Redis\Api\RedisApiInterface;
use Jamespi\Redis\Common\Common;
class RedisCache
{
    /**
     * 调用实例
     * @var RedisApiInterface
     */
    protected $redisCache;
    /**
     * Redis服务器链接
     * @var
     */
    protected $redisInstance;
    /**
     * Mysql服务器链接
     * @var
     */
    protected $mysqlInstance;

    public function __construct(RedisApiInterface $redisCache)
    {
        $this->redisCache = $redisCache;
    }

    /**
     * 更新缓存
     * @param array $config redis配置
     * @param array $mysql 数据库配置
     * @param array $paramsData 请求参数
     * @return string
     */
    public function write(array $config, array $mysql, array $paramsData):string
    {
        //缓存更新模式(1：Cache Aside模式 2：Through模式 3：Write Back模式)
        $cache_mode = (isset($paramsData['cache_mode'])&&$paramsData['cache_mode']>0)?1:1;
        $this->redisInstance = $this->_redisConnect($config);
        $this->_mysqlConnect($mysql);
        $result = $this->_checkMode($cache_mode, $paramsData);
        if ($result){
            switch ($result['type']){
                case 'string':
                    $this->redisCache->del($result['key']);
                    break;
                case 'hash':
                    break;
                case 'list':
                    break;
                case 'set':
                    break;
                case 'sorted_set':
                    break;
            }
        }
    }

    /**
     * 读取缓存
     * @param array $config redis配置
     * @param array $mysql 数据库配置
     * @param array $paramsData 请求参数
     * @return string
     */
    public function read(array $config, array $mysql, array $paramsData):string
    {

    }

    /**
     * cache Aside模式
     * @param array $paramsData 请求参数
     * @return array|bool
     */
    private function _cacheAsideMode(array $paramsData)
    {
        $param = [];
        if ( (isset($paramsData['key']) && $paramsData['key'])&&(isset($paramsData['msg']) && $paramsData['msg']) ){
            switch ($paramsData['type']){
                case 'string':
                    $param[$paramsData['key']] = $paramsData['msg'];
                    break;
                case 'hash':
                    $param[$paramsData['key']] = json_decode($paramsData['msg'], true);
                    break;
                case 'list':
                    $param[$paramsData['key']] = json_decode($paramsData['msg'], true);
                    break;
                case 'set':
                    $param[$paramsData['key']] = json_decode($paramsData['msg'], true);
                    break;
                case 'sorted_set':
                    $param[$paramsData['key']] = json_decode($paramsData['msg'], true);
                    break;
                default:
                    break;
            }
        }

        if ($this->mysqlInstance){
            $class = $this->mysqlInstance['namespace'];
            $action = $this->mysqlInstance['action'];
            try{
                $result = call_user_func_array([$class, $action], [$param]);
                if ($result){
                    return ['type'=> $paramsData['type'], 'key' => $paramsData['key']];
                }
                return false;
            }catch (\Exception $e){
                return false;
            }
        }
        return false;
    }

    /**
     * through模式
     * @param array $paramsData 请求参数
     */
    private function _throughMode(array $paramsData)
    {

    }

    /**
     * write back模式
     * @param array $paramsData 请求参数
     */
    private function _writeBackMode(array $paramsData)
    {

    }

    /**
     * 选择缓存模型
     * @param int $cache_mode 缓存模型id
     * @param array $paramsData 请求参数
     */
    private function _checkMode(int $cache_mode, array $paramsData)
    {
        switch ($cache_mode){
            case 1:
                $result = $this->_cacheAsideMode($paramsData);
                break;
            case 2:
                $result = $this->_throughMode($paramsData);
                break;
            case 3:
                $result = $this->_writeBackMode($paramsData);
                break;
            default:
                $result = $this->_cacheAsideMode($paramsData);
                break;
        }

        return $result;
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
                    if (is_string($value) && !empty($value))
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
     * mysql数据库连接
     * @param array $config
     * @return \mysqli
     */
    private function _mysqlConnect(array $config)
    {
        try{
            $class = new ReflectionClass($config['namespace']);
            $class->getMethod($config['action']);
            $this->mysqlInstance = $config;
            return true;
        }catch (\Exception $e){
            unset($this->mysqlInstance);
            return Common::resultMsg('failed', '数据库建立通信失败，失败原因：'.$e->getMessage());
        }
    }
}