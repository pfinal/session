<?php

namespace PFinal\Session;

/**
 * Resis Session
 *
 * composer require predis/predis
 *
 * @author  Zou Yiliang
 * @since   1.0
 */
class RedisSession extends NativeSession implements \SessionHandlerInterface
{
    protected $keyPrefix = 'pfinal:session:';
    protected $flashKeyPrefix = 'flash:';
    protected $expire;// 例如3600秒
    protected $server;

    /** @var $redis \Predis\Client */
    protected $redis;

    public function __construct($config = array())
    {
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }

        if ($this->expire == null) {
            $this->expire = (int)ini_get("session.gc_maxlifetime");
        }

        session_set_save_handler($this, true);
    }

    /**
     * Close the session
     * @link http://php.net/manual/en/sessionhandlerinterface.close.php
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function close()
    {
        return true;
    }

    /**
     * Destroy a session
     * @link http://php.net/manual/en/sessionhandlerinterface.destroy.php
     * @param string $session_id The session ID being destroyed.
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function destroy($session_id)
    {
        return true;
    }

    /**
     * Cleanup old sessions
     * @link http://php.net/manual/en/sessionhandlerinterface.gc.php
     * @param int $maxlifetime <p>
     * Sessions that have not updated for
     * the last maxlifetime seconds will be removed.
     * </p>
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function gc($maxlifetime)
    {
        return true;
    }

    /**
     * Initialize session
     * @link http://php.net/manual/en/sessionhandlerinterface.open.php
     * @param string $save_path The path where to store/retrieve the session.
     * @param string $name The session name.
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function open($save_path, $name)
    {
        if (empty($this->server)) {
            $params = array(
                'scheme' => 'tcp',
                'host' => '127.0.0.1',
                'port' => 6379,
            );
        } else {
            $params = $this->server;
        }

        if (!$this->redis instanceof \Predis\Client) {
            $this->redis = new \Predis\Client($params);
        }
        return true;
    }

    /**
     * Read session data
     * @link http://php.net/manual/en/sessionhandlerinterface.read.php
     * @param string $session_id The session id to read data for.
     * @return string <p>
     * Returns an encoded string of the read data.
     * If nothing was read, it must return an empty string.
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function read($session_id)
    {
        return (string)$this->redis->get($this->generateUniqueKey($session_id));
    }

    /**
     * Write session data
     * @link http://php.net/manual/en/sessionhandlerinterface.write.php
     * @param string $session_id The session id.
     * @param string $session_data <p>
     * The encoded session data. This data is the
     * result of the PHP internally encoding
     * the $_SESSION superglobal to a serialized
     * string and passing it as this parameter.
     * Please note sessions use an alternative serialization method.
     * </p>
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function write($session_id, $session_data)
    {
        /** @var  $status \Predis\Response\Status */
        $status = $this->redis->setex($this->generateUniqueKey($session_id), $this->expire, $session_data);
        return $status->getPayload() === 'OK';
    }

    /**
     * 生成唯一的Key
     * @param string $key
     * @return string
     */
    private function generateUniqueKey($key)
    {
        return $this->keyPrefix . $key;
    }
}