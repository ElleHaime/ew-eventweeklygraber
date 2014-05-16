<?php

namespace Library\Cache;

use Phalcon\Cache\Backend,
    Phalcon\Cache\BackendInterface,
    Phalcon\Cache\Exceptions;


class Memcache extends Backend implements BackendInterface
{
    public $prefix = '';
    public $memcache;
   

    public function __construct(\Phalcon\Cache\FrontendInterface $frontCache, $options = [])
    {
        if (empty($options)) {
            throw new \Exception('Memcache connection error: connection options are required');
        }

        if (!isset($options['host'])) {
            throw new \Exception('Memcache connection error: Host is required');
        }
        if (!isset($options['port'])) {
            throw new \Exception('Memcache connection error: Port is required');
        }

        $this -> prefix = $options['prefix'] . '.';
        $this -> memcache = new \Phalcon\Cache\Backend\Memcache($frontCache, 
            ['host' => $options['host'],
             'port' => $options['port'],
             'persistent' => $options['persistent']
            ]
        );

        return $this -> memcache;
    }

    /**
     * Returns a cached content
     *
     * @param   int|string $keyName
     * @param   long $lifetime
     * @return  mixed
     */
    public function get($keyName, $lifetime = null)
    {
        $value = $this -> memcache -> get($this -> prefix . $keyName);

        if (is_null($value)) {
            return null;
        }
        $frontend = $this -> memcache -> getFrontend();
        $this -> memcache -> setLastKey($this -> prefix . $keyName);

        //return $frontend -> afterRetrieve($value);
        return $value;
    }

    /**
     * Stores cached content into the file backend and stops the frontend
     *
     * @param int|string $keyName
     * @param string $content
     * @param long $lifetime
     * @param boolean $stopBuffer
     */
    public function save($keyName = null, $content = null, $lifetime = null, $stopBuffer = null)
    {
        if ($keyName === null) {
            $lastKey = $this -> _lastKey;
        } else {
            $lastKey = $this -> prefix . $keyName;
        }

        if (!$lastKey) {
            throw new Exception('The cache must be started first');
        }     

        $frontend = $this -> memcache -> getFrontend();
        if ($content === null) {
            $content = $frontend -> getContent();
        } 
        if ($lifetime === null) {
            $lifetime = $frontend -> getLifetime();
        }

        $this -> memcache -> save($lastKey, $content, $lifetime, $stopBuffer);

        $isBuffering = $frontend -> isBuffering();
        if ($stopBuffer) {
            $frontend -> stop();
        }
        if ($isBuffering) {
            echo $content;
        }
    }

    /**
     * Deletes a value from the cache by its key
     *
     * @param int|string $keyName
     * @return boolean
     */
    public function delete($keyName)
    {
        return $this -> memcache -> delete($this -> prefix . $keyName);
    }
    
    
    public function flush()
    {
    	parent::flush();
    }

    /**
     * Query the existing cached keys
     *
     * @return array
     */
    public function queryKeys($prefix = null)
    {
        return $this -> memcache -> queryKeys($this -> prefix);
    }

    /**
     * Checks if cache exists and it hasn't expired
     *
     * @param  string $keyName
     * @param  long $lifetime
     * @return boolean
     */
    public function exists($keyName = null, $lifetime = null)
    {
        return $this -> memcache -> exists($this -> prefix . $keyName);
    }
}