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

use ReflectionClass;
use Jamespi\Redis\Controller\RedisApi;
use Jamespi\Redis\Controller\RedisLock;
use Jamespi\Redis\Controller\RedisCache;
class Start
{
    /**
     * 锁服务配置项
     * @var mixed
     */
    protected $config = [];
    /**
     * 缓存服务配置项
     * @var mixed
     */
    protected $cacheConfig = [];
    /**
     * 业务场景类别
     * @var mixed
     */
    protected $type = 1;
    /**
     * 服务实例化对象
     * @var object
     */
    protected $model;

    public function __construct()
    {
        $this->config = include (__DIR__.'/Config/config.php');
        $this->cacheConfig = include (__DIR__.'/Config/cacheConfig.php');
    }

    /**
     * 启动服务
     * @param int $type 服务类型
     * @param array $config 服务配置
     * @return $this 服务实例化对象
     */
    public function run(int $type, array $config)
    {
        $mysql = [];
        $this->type = $type;
        if ($type == 2){
            if (!empty($config)) $this->config = array_merge($this->config, $config);
        }elseif ($type == 3){
            if (isset($config['mysql']) && !empty($config['mysql']))    $mysql = $config['mysql'];
            if (!empty($config)) $this->cacheConfig = array_merge($this->cacheConfig, $config['cache']);
        }

        switch ($type){
            case 1:
                $this->model = (new RedisApi($this->config));
                break;
            case 2:
                $this->model = (new RedisLock($this->config));
                break;
            case 3:
                $this->model = (new RedisCache($this->cacheConfig, $mysql));
                break;
            default:
                $this->model = (new RedisLock($this->config));
                break;
        }

        return $this;
    }

    public function __call(string $name, array $arguments)
    {
        // TODO: Implement __call() method.
        try{
            $class = new ReflectionClass($this->model);
            $class->getMethod($name);
            $data = call_user_func_array([$this->model, $name], $arguments);
            $data = json_decode($data, true);
            if ($data['status'] == 'success')
                return json_encode(['status'=>'success', 'msg'=>'调用成功！', 'data'=>isset($data['data'])?$data['data']:[]]);
            else
                return json_encode(['status'=> 'failed', 'msg'=>'Error：'.isset($data['msg'])?$data['msg']:[], 'data'=>isset($data['data'])?$data['data']:[]]);
        }catch (\Exception $e){
            return json_encode(['status'=> 'failed', 'msg'=>'Error：'.$e->getMessage()]);
        }
    }

}