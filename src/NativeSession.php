<?php

namespace PFinal\Session;

/**
 * PHP原生Session操作
 * @author  Zou Yiliang
 * @since   1.0
 */
class NativeSession implements SessionInterface
{
    protected $keyPrefix = 'pfinal.session.';
    protected $flashKeyPrefix = 'flash.';

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
        if (!isset($_SESSION)) {
            session_start();
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
     */
    public function set($key, $value)
    {
        $this->start();
        $_SESSION[$this->generateUniqueKey($key)] = $value;
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
        if (array_key_exists($key, $_SESSION)) {
            return $_SESSION[$key];
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
        if (array_key_exists($key, $_SESSION)) {
            $value = $_SESSION[$key];
            unset($_SESSION[$key]);
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
        foreach ($_SESSION as $key => $value) {
            if (strpos($key, $this->keyPrefix) === 0) {
                unset($_SESSION[$key]);
            }
        }
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
        $_SESSION[$key] = $value;
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
        return isset($_SESSION[$key]);
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
        if (array_key_exists($key, $_SESSION)) {
            $value = $_SESSION[$key];
            unset($_SESSION[$key]);
            return $value;
        }
        return $defaultValue;
    }
}