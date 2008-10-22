<?php

/*
 * Copyright 2008 Wilker Lucio <wilkerlucio@gmail.com>
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License. 
 */

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
