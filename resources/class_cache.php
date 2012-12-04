<?php

class CACHE {
    private $id;
    private $obj;

    function __construct($id) {
        $this->id = $id;
        $this->obj = new Memcached($id);
        $this->connect(MEMCACHED_HOST, MEMCACHED_PORT);
    }

    public function connect($host, $port) {
        $servers = $this->obj->getServerList();
        if (is_array($servers)) {
            foreach ($servers as $server)
                if ($server['host'] == $host and $server['port'] == $port)
                    return true;
        }
        return $this->obj->addServer($host, $port);
    }
    
    public function set($Key, $Value, $Expire) {
        return $this->obj->set($Key, $Value, $Expire);
    }
    
    public function get($Key) {
        return $this->obj->get($Key);
    }
    
    public function inc($Key, $Value = 1) {
        return $this->obj->increment($Key, $Value);
    }
    
    public function dec($Key, $Value = 1) {
        return $this->obj->decrement($Key, $Value);
    }
    
    public function delete($Key) {
        return $this->obj->delete($Key);
    }
}

?>