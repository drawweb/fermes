<?php

class Sess_Cache
{
    private static $_cache = null;

    private static $_lifetime = 3600;

    private static $_cacheDir = null;

    private static function init()
    {
        if (self::$_cache === null) {
            $frontendOptions = array('automatic_serialization' => true, 'lifetime' => self::$_lifetime);
            $backendOptions  = array('cache_dir' => self::$_cacheDir);
            try {
                if (extension_loaded('APC')) {
                    self::$_cache = Zend_Cache::factory('Core', 'APC', $frontendOptions, array());
                } else {
                    self::$_cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
                }
            } catch (Zend_Cache_Exception $e) {
                throw new Le_Cache_Exception($e);
            }
            if (!self::$_cache) {
                throw new Le_Cache_Exception("No cache backend available.");
            }
        }
    }

    public static function setup($lifetime, $filesCachePath = null)
    {
        if (self::$_cache !== null) {
            throw new Le_Cache_Exception("Cache already used.");
        }
        self::$_lifetime = (integer) $lifetime;
        if ($filesCachePath !== null) {
            self::$_cacheDir = realpath($filesCachePath);
        }
    }

    public static function set($data, $key)
    {
        self::init();
        return self::$_cache->save($data, $key);
    }

    public static function get($key)
    {
        self::init();
        return self::$_cache->load($key);
    }

    public static function clean($key = null)
    {
        self::init();
        if ($key === null) {
            return self::$_cache->clean();
        }
        return self::$_cache->remove($key);
    }
    
    public static function getCacheInstance()
    {
        self::init();
        if (is_null(self::$_cache)) {
            throw new Sess_Cache_Exception("Cache not set yet.");
        }
        return self::$_cache; 
    }
}
