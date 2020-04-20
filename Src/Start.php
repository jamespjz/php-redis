<?php
/**+----------------------------------------------------------------------
 * JamesPi Redis [php-redis]
 * +----------------------------------------------------------------------
 * Redis Basic Service Configuration file
 * +----------------------------------------------------------------------
 * Copyright (c) 2020-2030 http://www.pijianzhong.com All rights reserved.
 * +----------------------------------------------------------------------
 * Author：PiJianZhong <jianzhongpi@163.com>
 * +----------------------------------------------------------------------
 */

namespace Jamespi\Redis;

use Jamespi\Redis\Controller\RedisApi;
use Jamespi\Redis\Controller\RedisLock;
use Jamespi\Redis\Controller\RedisCache;
class Start
{
    /**
     * 服务配置项
     * @var mixed
     */
    protected $config;
    /**
     * 服务实例化对象
     * @var object
     */
    protected $model;

    /**
     * reids环境
     * @var mixed
     */
    protected $redis_setting;

    public function __construct()
    {
        $this->config = require_once (__DIR__.'/Config/config.php');
    }

    /**
     * 启动服务
     * @param int $redis_setting redis环境（1：单机 2：集群）
     * @param int $type 服务类型
     * @param array $config 服务配置
     * @return $this 服务实例化对象
     */
    public function run(int $type, array $config, int $redis_setting = 1)
    {
        if (!empty($config))
            $this->config = $config;
        $this->redis_setting = $redis_setting;
        switch ($type){
            case 1:
                $this->model = (new RedisApi());
                break;
            case 2:
                $this->model = (new RedisLock());
                break;
            case 3:
                $this->model = (new RedisCache());
                break;
            default:
                $this->model = (new RedisApi());
                break;
        }

        return $this;
    }

    public function __call(string $name, array $arguments)
    {
        $arguments = array_merge($arguments, $this->config);
        // TODO: Implement __call() method.
        try{
            $class = new ReflectionClass($this->model);
            $class->getMethod($name);
            $data = call_user_func_array([$this->model, $name], [$this->redis_setting, $arguments]);
            $data = json_decode($data, true);
            if ($data['status'])
                return json_encode(['status'=>'success', 'msg'=>'调用成功！', 'data'=>$data]);
            else
                return json_encode(['status'=> 'failed', 'msg'=>'Error：'.$data['msg'], 'data'=>$data['data']]);
        }catch (\Exception $e){
            return json_encode(['status'=> 'failed', 'msg'=>'Error：'.$e->getMessage()]);
        }
    }

}