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

require_once(dirname(__FILE__) . '/../ActiveRecord.php');

/**
 * This class provides a base for creating relationship between models
 *
 * @package db
 * @subpackage relations
 * @author wilker
 */
abstract class ActiveRelation
{
    private $_data;
    private $_newdata;
    private $_changed;
    
    protected $local_model;
    protected $foreign_model;
    protected $options;
    
    public function __construct($local_model, $foreign_model, $options = array())
    {
        $this->local_model = $local_model;
        $this->foreign_model = class_exists($foreign_model) ? ActiveRecord::model($foreign_model) : $foreign_model;
        $this->options = $options;
        
        $this->_data = null;
        $this->_newdata = null;
        $this->_changed = false;
    }
    
    public function get_local_field()
    {
    	if (isset($this->options['local_field'])) {
    		return $this->options['local_field'];
    	}
    	
        return 'id';
    }
    
    public function get_foreign_field($model)
    {
    	if (isset($this->options['foreign_field'])) {
    		return $this->options['foreign_field'];
    	}
    	
    	$class = get_class($model);
		$field = $class[0];
		
		for ($i = 1; $i < strlen($class); $i++) { 
			$char = $class[$i];
			
			if (ord($char) > 64 && ord($char) < 91) {
				$field .= '_' . strtolower($char);
			} else {
				$field .= $char;
			}
		}
		
		return strtolower($field) . '_id';
    }
    
    public function get_data($force_refresh = false)
    {
        if (!$this->_data || $force_refresh === true) {
            $this->_data = $this->refresh();
        }
        
        return $this->_data;
    }
    
    public function get_foreign_model()
    {
    	return $this->foreign_model;
    }
    
    public function is_loaded()
    {
    	return $this->_data !== null;
    }
    
    public function set_data($data)
    {
    	$this->_newdata = $data;
    	$this->_changed = true;
    }
    
    public function save()
    {
    	if ($this->_changed) {
    		$this->push($this->_newdata);
    	}
    }
    
    public abstract function refresh();
    public abstract function push($data);
} // END abstract class ActiveRelation
