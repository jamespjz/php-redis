<?php
/**
 * Created by PhpStorm.
 * User: jamespi
 * Date: 2021/11/8
 * Time: 13:38
 */

namespace Jamespi\Redis\Controller;

use Redis;
use RedisCluster;
use ReflectionClass;
use Jamespi\Redis\Logic\RedisCache as RedisCacheLogic;
use Jamespi\Redis\Server\RedisCache as RedisCacheServer;
class RedisCache
{
    /**
     * redis server collection
     * @var array
     */
    private $servers = [];
    /**
     * 其他请求参数
     * @var
     */
    protected $paramsData = [];


    public function __construct(array $servers)
    {
        $this->servers = $servers;
        if (is_null(self::$instance)){
            $connect = $this->_connect($servers);
            if ($connect)
                self::$instance = $connect;
            else
                throw new Exception("Failed to connect to redis!");
        }
    }

    /**
     * Redis服务器连接
     * @param array $servers
     * @return bool|Redis|RedisCluster
     */
    private function _connect(array $servers){
        if (count($servers) == 0 || ( count($servers)>0 && !is_array($servers[0]) ) || ( count($servers)>0 && ( is_array($servers[0]) && count($servers[0])==0 ) )){
            return false;
        }

        if (count($servers) < 2){
            //单机redis
            $redis = new Redis();
            $redis->connect($servers[0][0], $servers[0][1], $servers[0][2]);//host、port、timeout连接超时
            $redis->auth($servers['auth']);
            $this->acquireTimeout = ($servers[0][2]??0)+($servers[0][3]??0);
        }else{
            $this->acquireLogo = 2;
            $host = [];
            $timeout = 0;
            $readTimeout = 0;
            $auth = '';
            foreach ($servers as $k=>$v){
                if (is_array($v)){
                    $host[] = $v[0].":".$v[1];
                    $timeout = ($timeout < $v[2])?$v[2]:$timeout;
                    $readTimeout = ($readTimeout < $v[3])?$v[3]:$readTimeout;
                }else{
                    $auth = $v;
                }
            }
            //集群redis
            $redis = new RedisCluster(null, $host, !empty($timeout)?$timeout:1.5, !empty($readTimeout)?$readTimeout:1.5, true, $auth);
        }
        return $redis;
    }

    /**
     * 读取缓存
     * @param array $arguments 请求参数
     * @return string
     */
    public function readCache(array $arguments):string
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

        $redisServer = new RedisCacheLogic(new RedisCacheServer());
        return $redisServer->read($this->config, $this->mysql, $this->paramsData);
    }

    /**
     * 更新缓存
     * @param array $arguments 请求参数
     * @param array $storage 存储信息
     * @return string
     */
    public function writeCache(array $arguments, array $storage):string
    {
        foreach ($arguments as $key=>$value){
            switch ($key){
                case 'cache_mode': //缓存更新模式(1：Cache Aside模式 2：Through模式 3：Write Back模式)
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
                case 'type': //数据类型：string、hash、list、set、sorted_set
                    if (!empty($value))
                        $this->paramsData['type'] = (string)$value;
                    break;
            }
        }
        $redisServer = new RedisCacheLogic(new RedisCacheServer());
        return $redisServer->write($this->config, $this->mysql, $this->paramsData);
    }

    /**
     * 魔术方法
     * @param $name 请求调用方法
     * @param $arguments 请求调用参数
     * @return string
     */
    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        try{
            $class = new ReflectionClass(new RedisLock());
            $class->getMethod($name);
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }

}