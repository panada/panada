<?php

/**
 * Panada Objet Cache session handler.
 *
 * @author	Iskandar Soesman
 *
 * @since	Version 0.3
 */
namespace Drivers\Session;

use Resources;

class Cache extends Native
{
    private $sessionStorageName = 'sessions_';

    public function __construct($config)
    {
        $this->sessionStorageName   = $config['storageName'].'_';
        $this->cache            = new Resources\Cache($config['driverConnection']);

        session_set_save_handler(
        array($this, 'sessionStart'),
        array($this, 'sessionEnd'),
        array($this, 'sessionRead'),
        array($this, 'sessionWrite'),
        array($this, 'sessionDestroy'),
        array($this, 'sessionGc')
    );

        parent::__construct($config);
    }

    /**
     * Required function for session_set_save_handler act like constructor in a class.
     *
     * @param string
     * @param string
     */
    public function sessionStart($savePath, $sessionName)
    {
        //We don't need anythings at this time.
    }

    /**
     * Required function for session_set_save_handler act like destructor in a class.
     */
    public function sessionEnd()
    {
        //we also don't have do anythings too!
    }

    /**
     * Read session from db or file.
     *
     * @param string $id The session id
     *
     * @return string|array|object|bool
     */
    public function sessionRead($id)
    {
        return $this->cache->getValue($this->sessionStorageName.$id);
    }

    /**
     * Write the session data.
     *
     * @param string
     * @param string
     *
     * @return bool
     */
    public function sessionWrite($id, $sessData)
    {
        if ($this->sessionRead($id)) {
            return $this->cache->updateValue($this->sessionStorageName.$id, $sessData, $this->sesionExpire);
        } else {
            return $this->cache->setValue($this->sessionStorageName.$id, $sessData, $this->sesionExpire);
        }
    }

    /**
     * Remove session data.
     *
     * @param string
     *
     * @return bool
     */
    public function sessionDestroy($id)
    {
        return $this->cache->deleteValue($this->sessionStorageName.$id);
    }

    /**
     * Clean all expired record in db trigered by PHP Session Garbage Collection.
     * All cached session object will automaticly removed by the cache service, so we
     * dont have to do anythings.
     */
    public function sessionGc($maxlifetime = 0)
    {
        //none
    }
}
