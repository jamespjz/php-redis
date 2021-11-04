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

use Jamespi\Redis\Api\RedisLockInterface;
use Jamespi\Redis\Common\Common;
class RedisLock
{
    protected $redisLock;

    public function __construct(RedisLockInterface $redisLock)
    {
        $this->redisLock = $redisLock;
    }

    /**
     * 获取分布式锁
     * @param $instance 链接redis实例化对象
     * @param string $tokenKey 分布式锁key
     * @param int $acquireNumber 获取分布式锁次数
	 * @param int $requestsNumber 分布式锁失败后沦陷次数
     * @param int $acquireTimeout 请求分布式锁超时时间
     * @param int $lockTimeOut 分布式锁过期时间
     * @param int $acquireLogo redis请求类型标识
     * @return string
     */
    public function acquireLock($instance, string $tokenKey, int $acquireNumber, int $requestsNumber, int $acquireTimeout, int $lockTimeOut, int $acquireLogo):string
    {
		$i = 0;
        if (isset($acquireLogo) && $acquireLogo == 2){
            $time = $instance->time('x');
        }else{
            $time = $instance->time();
        }
        $acquireTime = $time[0] + ceil(($time[1]+$acquireTimeout)/1000000);
        $identifier = md5($time[0].$time[1].mt_rand(1, 10000000));
        $tokenKey = 'lock:'.$tokenKey;
        $lockTimeOut = intval(ceil($lockTimeOut));
        if ($instance->hGet('client', $tokenKey) > $acquireNumber){
            $instance->hDel('client', $tokenKey);
            return Common::resultMsg('failed', '分布式锁获取次数超过最大请求次数');
        }else{
            $result = '';
			while($i<$requestsNumber){
				$result = $this->redisLock->acquireLock($instance, $tokenKey, $identifier, $acquireTime, $lockTimeOut);
				if($result)	break;
				$i++;
            }
			if ($result){
                $instance->hIncrBy('client', $tokenKey, 1);
                return json_encode(['status'=>'success', 'msg'=>'分布式锁获取成功', 'data'=> $result]);
            }else{
                return json_encode(['status'=>'failed', 'msg'=>'分布式锁获取失败']);
            }
        }
    }

    /**
     * 释放分布式锁
     * @param $instance 链接redis实例化对象
     * @param string $tokenKey 分布式锁key
     * @param int $requestsNumber 分布式锁失败后轮询次数
     * @param string $identifier 分布式锁key值
     * @return string
     */
    public function unLock($instance, string $tokenKey, int $requestsNumber, string $identifier):string
    {
		$i = 0;
        $result = '';
        $tokenKey = 'lock:'.$tokenKey;
		while($i<$requestsNumber){
			$result = $this->redisLock->unLock($instance, $tokenKey, $identifier);
			if($result)	break;
			$i++;
		}
        if ($result){
            $instance->hDel('client', $tokenKey);
            return json_encode(['status'=>'success', 'msg'=>'分布式锁释放成功']);
        }
        return json_encode(['status'=>'failed', 'msg'=>'分布式锁释放失败']);
    }

    /**
     * 查询分布式锁key是否被定义
     * @param $instance
     * @param string $tokenKey
     * @return object|string
     */
    public function isLock($instance, string $tokenKey){
        $tokenKey = 'lock:'.$tokenKey;
        $result = $this->redisLock->isKey($instance, $tokenKey);
        if ($result){
            return json_encode(['status'=>'success', 'msg'=>'该分布式锁key已被定义']);
        }
        return json_encode(['status'=>'failed', 'msg'=>'该分布式锁key未被定义']);
    }
}