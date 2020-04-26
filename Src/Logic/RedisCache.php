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
        $result = $this->_checkMode($cache_mode, 1,$paramsData);
        if ($result){
            switch ($result['type']){
                case 'string':
                    $this->redisCache->del($result['key']);
                    break;
                case 'hash':
                    $this->redisCache->del($result['key']);
                    break;
                case 'list':
                    $this->redisCache->del($result['key']);
                    break;
                case 'set':
                    $this->redisCache->del($result['key']);
                    break;
                case 'sorted_set':
                    $this->redisCache->del($result['key']);
                    break;
            }
            $resultStatus = true;
        }
        if ($resultStatus)
            return Common::resultMsg('success', '更新缓存成功！');
        else
            return Common::resultMsg('failed', '更新缓存失败！');
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
        $resultData = $this->_checkDataType($paramsData);
        if (is_array($resultData) && !empty($resultData)){
            $resultStatus = $resultData[0];
            $resultData = $resultData[1];
            if ($resultStatus){
                return Common::resultMsg('success', '获取缓存成功！', [$resultData]);
            }else{
                //缓存更新模式(1：Cache Aside模式 2：Through模式 3：Write Back模式)
                $cache_mode = (isset($paramsData['cache_mode'])&&$paramsData['cache_mode']>0)?1:1;
                $this->_mysqlConnect($mysql);
                $result = $this->_getCacheData($cache_mode, $paramsData);

//                if ($result){
//                    $this->redisCache->sCard($paramsData['key']);
//                    return Common::resultMsg('success', '更新缓存成功！');
//                }
//                else
//                    return Common::resultMsg('failed', '更新缓存失败！');
            }
        }else{
            return $resultData;
        }
    }

    /**
     * 查询缓存
     * @param int $cache_mode
     * @param array $paramsData
     */
    private function _getCacheData(int $cache_mode, array $paramsData)
    {
        $result = $this->_checkMode($cache_mode, 2, $paramsData);
        if (is_array($result) && !empty($result)){
            var_dump($paramsData);
        }
    }

    /**
     * 选择数据处理类型
     * @param $paramsData
     * @return array
     */
    private function _checkDataType($paramsData){
        $resultStatus = false;
        $resultData = '';
        switch ($paramsData['type']){
            case 'string':
                try{
                    if (is_string($resultData = $this->redisCache->get($paramsData['key']))){
                        $resultStatus = true;
                    }
                }catch (\Exception $e){
                    return Common::resultMsg('failed', '缓存key不存在！');
                }
                break;
            case 'hash':
                if (!empty($paramsData['field'])){
                    try{
                        if (is_string($resultData = $this->redisCache->hGet($paramsData['key'], $paramsData['field']))){
                            $resultStatus = true;
                        }
                    }catch (\Exception $e){
                        return Common::resultMsg('failed', '缓存key不存在！');
                    }
                }else{
                    try{
                        if (is_array($resultData = $this->redisCache->hGetAll($paramsData['key'])) && !empty($this->redisCache->hGetAll($paramsData['key']))){
                            $resultData = json_encode($resultData);
                            $resultStatus = true;
                        }
                    }catch (\Exception $e){
                        return Common::resultMsg('failed', '缓存key不存在！');
                    }
                }
                break;
            case 'list':
                if ($this->redisCache->lLen($paramsData['key'])>0){
                    $resultData = $this->redisCache->lpop($paramsData['key']);
                    $resultStatus = true;
                }
                break;
            case 'set':
                if($this->redisCache->sCard($paramsData['key'])>0){
                    $resultData = json_encode($this->redisCache->sMembers($paramsData['key']));
                    $resultStatus = true;
                }
                break;
            case 'sorted_set':
                if($this->redisCache->zCard($paramsData['key'])>0){
                    $resultStatus = true;
                }
                break;
        }

        return [$resultStatus, $resultData];
    }

    /**
     * cache Aside模式
     * @param int $option 操作方式（1：更新 2：查询）
     * @param array $paramsData 请求参数
     * @return array|bool
     */
    private function _cacheAsideMode(int $option, array $paramsData)
    {
        $param = '';
        if ($option == 1) {
            if ((isset($paramsData['key']) && $paramsData['key']) && (isset($paramsData['msg']) && $paramsData['msg'])) {
                switch ($paramsData['type']) {
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
        }elseif ($option == 2){
            $param = $paramsData['key'];
        }

        if ($this->mysqlInstance){
            $class = $this->mysqlInstance['namespace'];
            $action = $this->mysqlInstance['action'];
            try{
                $result = call_user_func_array([new $class(), $action], [$param]);
                if ($result){
                    return ['type'=> $paramsData['type'], 'key' => $paramsData['key'], 'data'=>$result];
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
     * @param int $option 操作方式（1：更新 2：查询）
     * @param array $paramsData 请求参数
     */
    private function _checkMode(int $cache_mode, int $option, array $paramsData)
    {
        switch ($cache_mode){
            case 1:
                $result = $this->_cacheAsideMode($option, $paramsData);
                break;
            case 2:
                $result = $this->_throughMode($option, $paramsData);
                break;
            case 3:
                $result = $this->_writeBackMode($option, $paramsData);
                break;
            default:
                $result = $this->_cacheAsideMode($option, $paramsData);
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