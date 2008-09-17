<?php

class ModelCache
{
    private static $instance;
    
    protected $instances;
    
    private function __construct()
    {
        $this->instances = array();
    }
    
    public static function get_instance()
    {
        if (!self::$instance) {
            self::$instance = new ModelCache();
        }
        
        return self::$instance;
    }
    
    public function __get($model_name)
    {
        if (!isset($this->instances[$model_name])) {
            $this->instances[$model_name] = new $model_name();
        }
        
        return $this->instances[$model_name];
    }
}
