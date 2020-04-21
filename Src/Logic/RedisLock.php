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
     * @param int $lock_timeout 分布式锁过期时间
     * @return string
     */
    public function acquireLock($instance, string $token_key, int $acquire_number, int $lock_timeout):string
    {
        $clientInfo = $instance->client('list');
        $clientInfo = end($clientInfo);
        $identifier = md5($_SERVER['REQUEST_TIME'].$clientInfo['fd'].$clientInfo['addr'].mt_rand(1, 10000000));
        $token_key = 'lock:'.$token_key;
        $lock_timeout = intval(ceil($lock_timeout));
        if ($instance->hGet('client', 'fd'.$clientInfo['fd']) >= $acquire_number){
            $instance->client('kill', $clientInfo['addr']);
            $instance->hDel('client', 'fd'.$clientInfo['fd']);
            return Common::resultMsg('failed', '分布式锁获取次数超过最大请求次数');
        }else{
            $result = $this->redisLock->acquireLock($instance, $token_key, $identifier, $lock_timeout);
            if ($result){
                $instance->hIncrBy('client', 'fd'.$clientInfo['fd'], 1);
                $instance->client('kill', $clientInfo['addr']);
                return Common::resultMsg('success', '分布式锁获取成功', [$result]);
            }else{
                $instance->client('kill', $clientInfo['addr']);
                return Common::resultMsg('failed', '分布式锁获取失败');
            }
        }
    }

    /**
     * 释放分布式锁
     * @param $instance 链接redis实例化对象
     * @param string $token_key 分布式锁key
     * @param string $identifier 分布式锁key值
     * @return string
     */
    public function unLock($instance, string $token_key, string $identifier):string
    {
        $clientInfo = $instance->client('list');
        $clientInfo = end($clientInfo);
        $token_key = 'lock:'.$token_key;
        $result = $this->redisLock->unLock($instance, $token_key, $identifier);
        $instance->client('kill', $clientInfo['addr']);
        if ($result){
            return Common::resultMsg('success', '分布式锁释放成功');
        }
        return Common::resultMsg('failed', '分布式锁释放失败');
    }
}