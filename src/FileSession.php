<?php

namespace PFinal\Session;

/**
 * File Session Handler
 *
 * @author  Zou Yiliang
 * @since   1.0
 */
class FileSession implements SessionInterface
{
    protected $savePath;
    protected $expire = 3600;//秒

    protected $keyPrefix = '';
    protected $flashKeyPrefix = 'flash:';
    protected $sessionName = 'PFSESSID';
    protected $sessionId;
    protected $data = array();

    public function __construct($config = array())
    {
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }

        if (empty($this->savePath)) {
            $this->savePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->sessionName . '_DATA';
        }

        if (!file_exists($this->savePath)) {
            mkdir($this->savePath, 0777, true);
        }
        $this->savePath = realpath($this->savePath);
    }

    /**
     * 开启Session
     */
    private function start()
    {
        if ($this->sessionId !== null) {
            return;
        }

        $this->sessionId = isset($_COOKIE[$this->sessionName]) ? $_COOKIE[$this->sessionName] : '';
        if (!preg_match('/^[0-9a-f]{40}$/', $this->sessionId)) {
            $this->sessionId = strtolower(sha1(time() . mt_rand(10000000, 99999999) . uniqid('sess', true) . $this->random(32) . serialize($_SERVER)));
            setcookie($this->sessionName, $this->sessionId, 0, '/');
        }


        register_shutdown_function(array($this, 'save'));

        if (file_exists($this->savePath . DIRECTORY_SEPARATOR . $this->sessionId)) {
            $data = file_get_contents($this->savePath . DIRECTORY_SEPARATOR . $this->sessionId);
            $data = @unserialize($data);
            if (is_array($data)) {
                $this->data = $data;
            }
        }
    }

    public function save()
    {
        file_put_contents($this->savePath . DIRECTORY_SEPARATOR . $this->sessionId, serialize($this->data), LOCK_EX);

        if (mt_rand(0, 1000000) < 100) {
            $this->gc();
        }
    }

    protected function gc($path = null)
    {
        if ($path === null) {
            $path = $this->savePath;
        }
        if (($handle = opendir($path)) === false) {
            return;
        }
        while (($file = readdir($handle)) !== false) {
            if ($file[0] === '.') { // . and ..
                continue;
            }
            $fullPath = $this->savePath . DIRECTORY_SEPARATOR . $file;
            if (is_dir($fullPath)) {
                $this->gc($fullPath);
            } elseif (@filemtime($fullPath) < time() - $this->expire) {
                @unlink($fullPath);
            }
        }
        closedir($handle);
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
        $this->data[$this->generateUniqueKey($key)] = $value;
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
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
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
        if (array_key_exists($key, $this->data)) {
            $value = $this->data[$key];
            unset($this->data[$key]);
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
        foreach ($this->data as $key => $value) {
            if (strpos($key, $this->keyPrefix) === 0) {
                unset($this->data[$key]);
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
        $this->data[$key] = $value;
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
        return isset($this->data[$key]);
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
        if (array_key_exists($key, $this->data)) {
            $value = $this->data[$key];
            unset($this->data[$key]);
            return $value;
        }
        return $defaultValue;
    }

    //兼容处理
    public function put($key, $value)
    {
        $this->set($key, $value);
    }

    public function pull($key)
    {
        return $this->remove($key);
    }

    public function forget($key)
    {
        $this->remove($key);
    }

    public function flush()
    {
        $this->clear();
    }

    public function token()
    {
        $this->start();
        $key = $this->generateUniqueKey(':token');
        return static::get($key);
    }

    public function regenerateToken()
    {
        $this->start();
        $key = $this->generateUniqueKey(':token');
        $token = self::random(40);
        $this->set($key, $token);
        return $token;
    }

    protected function random($length = 16)
    {
        if (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes($length * 2);

            if ($bytes === false) {
                throw new \RuntimeException('Unable to generate random string.');
            }

            return substr(str_replace(array('/', '+', '='), '', base64_encode($bytes)), 0, $length);
        }

        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }

}