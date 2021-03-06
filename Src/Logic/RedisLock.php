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
     * @param string $token_key 分布式锁key
     * @param int $acquire_number 获取分布式锁次数
	 * @param int $requests_number 分布式锁失败后沦陷次数
     * @param int $acquire_timeout 请求分布式锁超时时间
     * @param int $lock_timeout 分布式锁过期时间
     * @param array $params 支撑参数
     * @return string
     */
    public function acquireLock($instance, string $token_key, int $acquire_number, int $requests_number, int $acquire_timeout, int $lock_timeout, array $params):string
    {
		$i = 0;
        if (isset($params['redis_setting']) && $params['redis_setting'] == 1){
            $time = $instance->time();
        }else{
            $time = $instance->time('x');
        }
        $acquire_time = $time[0] + ceil(($time[1]+$acquire_timeout)/1000000);
        $identifier = md5($time[0].$time[1].mt_rand(1, 10000000));
        $token_key = 'lock:'.$token_key;
        $lock_timeout = intval(ceil($lock_timeout));
        if ($instance->hGet('client', $token_key) > $acquire_number){
            $instance->hDel('client', $token_key);
            return Common::resultMsg('failed', '分布式锁获取次数超过最大请求次数');
        }else{
			while($i<$requests_number){
				$result = $this->redisLock->acquireLock($instance, $token_key, $identifier, $acquire_time, $lock_timeout);
				if($result)	break;
				$i++;
            }
			if ($result){
                $instance->hIncrBy('client', $token_key, 1);
                return Common::resultMsg('success', '分布式锁获取成功', [$result]);
            }else{
                return Common::resultMsg('failed', '分布式锁获取失败');
            }
        }
    }

    /**
     * 释放分布式锁
     * @param $instance 链接redis实例化对象
     * @param string $token_key 分布式锁key
     * @param int $requests_number 分布式锁失败后沦陷次数
     * @param string $identifier 分布式锁key值
     * @return string
     */
    public function unLock($instance, string $token_key, int $requests_number, string $identifier):string
    {
		$i = 0;
        $token_key = 'lock:'.$token_key;
		while($i<$requests_number){
			$result = $this->redisLock->unLock($instance, $token_key, $identifier);
			if($result)	break;
			$i++;
		}
        if ($result){
            $instance->hDel('client', $token_key);
            return Common::resultMsg('success', '分布式锁释放成功');
        }
        return Common::resultMsg('failed', '分布式锁释放失败');
    }

    /**
     * 查询分布式锁key是否被定义
     * @param $instance
     * @param string $token_key
     * @return object|string
     */
    public function isLock($instance, string $token_key){
        $token_key = 'lock:'.$token_key;
        $result = $this->redisLock->isKey($instance, $token_key);
        if ($result){
            return Common::resultMsg('success', '该分布式锁key已被定义', ['status'=>true, 'msg'=>'该分布式锁key已被定义']);
        }
        return Common::resultMsg('failed', '该分布式锁key未被定义', ['status'=>false, 'msg'=>'该分布式锁key未被定义']);
    }
}