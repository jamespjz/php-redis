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
        $resultStatus = false;
        //缓存更新模式(1：Cache Aside模式 2：Through模式 3：Write Back模式)
        $cache_mode = (isset($paramsData['cache_mode'])&&$paramsData['cache_mode']>0)?1:1;
        $this->_mysqlConnect($mysql);
        $result = $this->_checkMode($cache_mode, $paramsData);
        if ($result){
            switch ($result['type']){
                case 'string':
                    $resultStatus = $this->redisCache->del($result['key']);
                    if ($resultStatus == 1) $resultStatus = true;
                    break;
                case 'hash':
                    $resultStatus = $this->redisCache->del($result['key']);
                    break;
                case 'list':
                    $resultStatus = $this->redisCache->del($result['key']);
                    break;
                case 'set':
                    $resultStatus = $this->redisCache->del($result['key']);
                    break;
                case 'sorted_set':
                    $resultStatus = $this->redisCache->del($result['key']);
                    break;
            }
            if (!$resultStatus) {
                $class = $this->mysqlInstance['edit']['namespace'];
                $action = $this->mysqlInstance['edit']['action'];
                try{
                    $result = call_user_func_array([new $class(), $action], [[$result['key']]]);
                    if (!$result){
                        return Common::resultMsg('failed', '缓存持久化数据清除失败！');
                    }
                }catch (\Exception $e){
                    return Common::resultMsg('failed', '数据库建立通信失败，缓存持久化数据清除失败！');
                }
            }
        }
        if ($resultStatus)
            return Common::resultMsg('success', '更新锁成功！');
        else
            return Common::resultMsg('failed', '更新锁失败，缓存持久化数据已清除！');
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
            $class = $this->mysqlInstance['add']['namespace'];
            $action = $this->mysqlInstance['add']['action'];
            try{
                $result = call_user_func_array([new $class(), $action], [$param]);
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
     * mysql数据库连接
     * @param array $config
     * @return \mysqli
     */
    private function _mysqlConnect(array $config)
    {
        try{
            $class = new ReflectionClass($config['add']['namespace']);
            $class->getMethod($config['add']['action']);
            $this->mysqlInstance = $config;
            return true;
        }catch (\Exception $e){
            unset($this->mysqlInstance);
            return Common::resultMsg('failed', '数据库建立通信失败，失败原因：'.$e->getMessage());
        }
    }
}