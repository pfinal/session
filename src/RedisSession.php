<?php

namespace PFinal\Session;

/**
 * Redis Session
 *
 * composer require "predis/predis": "1.1"
 *
 * @author  Zou Yiliang
 * @since   1.0
 */
class RedisSession implements SessionInterface
{
    protected $keyPrefix = 'pfinal.session.';
    protected $flashKeyPrefix = 'flash.';
    protected $expire = 3600;//秒
    protected $server;

    /**
     * @var \Predis\Client
     */
    protected $redis;

    public function __construct($config = array())
    {
        foreach ($config as $key => $value) {
            if (isset($this->$key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * 开启Session
     */
    private function start()
    {
        if (!$this->redis instanceof \Predis\Client) {

            if (empty($this->server)) {
                $params = [
                    'scheme' => 'tcp',
                    'host' => '127.0.0.1',
                    'port' => 6379,
                ];
            } else {
                $params = $this->server;
            }

            $this->redis = new \Predis\Client($params);
        }
    }

    /**
     * 生成唯一的Session Key
     * @param $key
     * @return string
     */
    private function generateUniqueKey($key)
    {
        return $this->keyPrefix . $key;
    }

    /**
     * 储存数据到Session中
     * @param string $key
     * @param $value mixed
     * @return bool
     */
    public function set($key, $value)
    {
        $this->start();
        /** @var  $status \Predis\Response\Status */
        $status = $this->redis->setex($this->generateUniqueKey($key), $this->expire, $value);
        return $status->getPayload() == 'OK';
    }

    /**
     * 从Session中取回数据
     * @param string $key
     * @param null $defaultValue 如果对应key不存在时，返回此值
     * @return mixed
     */
    public function get($key, $defaultValue = null)
    {
        $this->start();
        $key = $this->generateUniqueKey($key);

        if ($this->redis->exists($key)) {
            return $this->redis->get($key);
        }
        return $defaultValue;
    }

    /**
     * 从Session中删除该数据
     * @param string $key
     * @return mixed 返回被删除的数据，不存在时返回null
     */
    public function remove($key)
    {
        $this->start();
        $key = $this->generateUniqueKey($key);

        if ($this->redis->exists($key)) {
            $value = $this->redis->get($key);
            $this->redis->del($key);
            return $value;
        }

        return null;
    }

    /**
     * 清空Session中所有数据
     */
    public function clear()
    {
        $this->start();

        $keys = $this->redis->keys($this->keyPrefix . '*');
        foreach ($keys as $key) {
            $this->redis->del($key);
        }

        return true;
    }

    /**
     * 放入闪存数据(Flash Data)到Session中
     * @param string $key
     * @param mixed $value
     */
    public function setFlash($key, $value)
    {
        $this->start();
        $key = $this->generateUniqueKey($this->flashKeyPrefix . $key);
        /** @var  $status \Predis\Response\Status */
        $status = $this->redis->setex($key, $this->expire, $value);
        return $status->getPayload() == 'OK';
    }

    /**
     * Session中是否存在闪存(Flash Data)数据
     * @param string $key
     * @return bool
     */
    public function hasFlash($key)
    {
        $this->start();
        $key = $this->generateUniqueKey($this->flashKeyPrefix . $key);
        return (bool)$this->redis->exists($key);
    }

    /**
     * 从Session中获取闪存数据(获取后该数据将从Session中被删除)
     * @param string $key
     * @param null $defaultValue
     * @return mixed
     */
    public function getFlash($key, $defaultValue = null)
    {
        $this->start();
        $key = $this->generateUniqueKey($this->flashKeyPrefix . $key);

        if ($this->redis->exists($key)) {
            $value = $this->redis->get($key);
            $this->redis->del($key);
            return $value;
        }
        return $defaultValue;
    }
}