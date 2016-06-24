<?php

namespace PFinal\Session;

/**
 * Session操作接口
 * @author  Zou Yiliang
 * @since   1.0
 */
interface SessionInterface
{
    /**
     * 储存数据到Session中
     * @param string $key
     * @param $value mixed
     */
    public function set($key, $value);

    /**
     * 从Session中取回数据
     * @param string $key
     * @param null $defaultValue 如果对应key不存在时，返回此值
     * @return mixed
     */
    public function get($key, $defaultValue = null);

    /**
     * 从Session中删除该数据
     * @param string $key
     * @return mixed 返回被删除的数据，不存在时返回null
     */
    public function remove($key);

    /**
     * 清空Session中所有数据
     */
    public function clear();

    /**
     * 放入闪存数据(Flash Data)到Session中
     * @param string $key
     * @param mixed $value
     */
    public function setFlash($key, $value);

    /**
     * Session中是否存在闪存(Flash Data)数据
     * @param string $key
     * @return bool
     */
    public function hasFlash($key);

    /**
     * 从Session中获取闪存数据(获取后该数据将从Session中被删除)
     * @param string $key
     * @param null $defaultValue
     * @return mixed
     */
    public function getFlash($key, $defaultValue = null);
}