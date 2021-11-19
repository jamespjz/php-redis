<?php
/**
 * Created by PhpStorm.
 * User: jamespi
 * Date: 2021/11/3
 * Time: 19:09
 */

namespace Jamespi\Redis\Controller;

use Redis;
use RedisCluster;
use ReflectionClass;
use Jamespi\Redis\Server\RedisLock as RedisLockServer;
use Jamespi\Redis\Logic\RedisLock as RedisLockLogic;
class RedisLock
{
    /**
     * redis server collection
     * @var array
     */
    private $servers = [];
    /**
     * Redis distributed lock timeout
     * @var int
     */
    private $lockTimeOut = 60;
    /**
     * Number of redis distributed lock acquisitions
     * @var int
     */
    private $acquireNumber = 3;
    /**
     * redis distributed lock key
     * @var
     */
    private $tokenkey;
    /**
     * Number of polls after redis distributed lock failure
     * @var int
     */
    private $requestsNumber = 3;
    /**
     * Redis distributed lock key value
     * @var
     */
    private $identifier;
    /**
     * redis request type identification
     * 1=单机   2=集群
     * @var
     */
    private $acquireLogo = 1;
    /**
     * Redis request distributed lock timeout
     * @var
     */
    private $acquireTimeout;
    /**
     * Redis server connection example
     * @var
     */
    private static $instances;

    public function __construct(array $servers)
    {
        $this->servers = $servers;
        if (is_null(self::$instances)){
            $connect = $this->_connect($servers);
            if ($connect)
                self::$instances = $connect;
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
        $host = [];
        if (count($servers[0]) == 0 || ( count($servers[0])>0 && !is_array($servers[0][0]) ) || ( count($servers[0])>0 && ( is_array($servers[0][0]) && count($servers[0][0])==0 ) )){
            return false;
        }

        if (count($servers[0]) < 2){
            //单机redis
            $redis = new \Redis();
            $redis->connect($servers[0][0][0], $servers[0][0][1], $servers[0][0][2]);//host、port、timeout连接超时
            $redis->auth($servers['auth']);
            $this->acquireTimeout = ($servers[0][0][2]??0)+($servers[0][0][3]??0);
        }else{
            $this->acquireLogo = 2;
            $timeout = 0;
            $readTimeout = 0;
            $auth = $servers['auth'];
            foreach ($servers[0] as $k=>$v){
                if (is_array($v)){
                    $timeout = ($timeout < $v[2])?$v[2]:$timeout;
                    $host[] = $v[0].":".$v[1];
                    $readTimeout = ($readTimeout < $v[3])?$v[3]:$readTimeout;
                }
            }

            $this->acquireTimeout = $timeout+$readTimeout;
            //集群redis
            $redis = new \RedisCluster(null, $host, !empty($timeout)?$timeout:1.5, !empty($readTimeout)?$readTimeout:1.5, true, $auth);
        }
        return $redis;
    }

    /**
     * 获取分布式锁
     * @param array $arguments 请求参数
     * @return string
     */
    public function acquireLock(array $arguments):string
    {
        if ( !(isset($arguments['token_key']) && !empty($arguments['token_key'])) ){
            return json_encode(['status'=>'failed', 'msg'=>'Error:缺少必要参数-分布式锁key!']);
        }
        $this->acquireNumber = $arguments['acquire_number']??$this->acquireNumber;
        $this->requestsNumber = $arguments['requests_number']??$this->requestsNumber;
        $this->lockTimeOut = $arguments['lock_timeout']??$this->lockTimeOut;
        $this->tokenkey = $arguments['token_key'];

        try{
            //调用获取分布式锁业务
            $redisService = new RedisLockLogic(new RedisLockServer());
            $result = $redisService->acquireLock(self::$instances, $this->tokenkey, $this->acquireNumber, $this->requestsNumber, $this->acquireTimeout, $this->lockTimeOut, $this->acquireLogo);
            return $result;
        }catch (\Exception $e){
            return json_encode(['status'=>'failed', 'msg'=>'分布式锁获取失败,Error:'.$e->getMessage()]);
        }
    }

    /**
     * 分布式锁释放
     * @param array $arguments 请求参数
     * @return string
     */
    public function unlock(array $arguments):string
    {
        if ( !(isset($arguments['token_key']) && !empty($arguments['token_key'])) ){
            return json_encode(['status'=>'failed', 'msg'=>'Error:缺少必要参数-分布式锁key!']);
        }
        if ( !(isset($arguments['identifier']) && !empty($arguments['identifier'])) ){
            return json_encode(['status'=>'failed', 'msg'=>'Error:缺少必要参数-分布式锁值!']);
        }
        $this->tokenkey = $arguments['token_key'];
        $this->identifier = $arguments['identifier'];

        try{
            //调用获取分布式锁业务
            $redisService = new RedisLockLogic(new RedisLockServer());
            $result = $redisService->unLock(self::$instances, $this->tokenkey, $this->requestsNumber, $this->identifier);
            return $result;
        }catch (\Exception $e){
            return json_encode(['status'=>'failed', 'msg'=>'分布式锁释放失败,Error:'.$e->getMessage()]);
        }
    }

    /**
     * 判断分布式锁key是否被定义
     * @param array $arguments
     * @return object|string
     */
    public function isLock(array $arguments){
        if ( !(isset($arguments['token_key']) && !empty($arguments['token_key'])) ){
            return json_encode(['status'=>'failed', 'msg'=>'Error:缺少必要参数-分布式锁key!']);
        }

        $this->tokenkey = $arguments['token_key'];

        try{
            //调用获取分布式锁业务
            $redisService = new RedisLockLogic(new RedisLockServer());
            $result = $redisService->isLock(self::$instances, $this->tokenkey);
            return $result;
        }catch (\Exception $e){
            return json_encode(['status'=>'failed', 'msg'=>'分布式锁获取失败,Error:'.$e->getMessage()]);
        }
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