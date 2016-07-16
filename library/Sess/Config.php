<?php
/**
 * Une classe qui permet de charger des fichiers
 * de configuration tout en gérant le cache
 * 
 * @package zfbook
 * @subpackage config
 */
abstract class Sess_Config
{
    protected static $_backendCache;  
    protected static $_lifeTime = 86400;  
    protected $_config;  
    protected $_cache;
    
    public static function setBackendCache(Zend_Cache_Backend_Interface $cache)
    {
        self::$_backendCache = $cache;        
    }
    
    public static function setLifeTime($lifeTime)
    {
        self::$_lifeTime = abs((int)$lifeTime); 
    }
    
    public function __construct($filename, $section = null, $options = false)
    {
        $this->_setupCache($filename);
        $thisClass = substr(get_class($this), 0, strpos(get_class($this), '_'));
        $zendClass = str_replace($thisClass, 'Zend', get_class($this));
        if (($self = $this->_cache->load(spl_object_hash($this))) == false) {
            $this->_config = new $zendClass($filename, $section, $options);
            $this->_cache->save($this->_config, spl_object_hash($this));
        } else {
            $this->_config = $self;
        }
    }
    
    public function __get($prop)
    {
        return $this->_config->get($prop);
    }
    
    public function __set($prop, $val)
    {
        $this->_config->__set($prop, $val);
    }
    
    public function getConfigObject()
    {
        return $this->_config;
    }
    
    public function __call($meth, $args)
    {
        return call_user_func_array(array($this->_config, $meth), $args);
    }
    
    protected function _setupCache($filename)
    {
        if (is_null(self::$_backendCache)) {
            throw new Zend_Config_Exception("no backend cache provided to ".get_class($this));
        }
        $options = array('automatic_serialization' => true, 'lifetime' => self::$_lifeTime,
                         'master_file' => $filename);
        $this->_cache = new Zend_Cache_Frontend_File($options);
        $this->_cache->setBackend(self::$_backendCache);
    }    
}
