<?php

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
        $this->foreign_model = ActiveRecord::model($foreign_model);
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
    	
        return strtolower(get_class($model)) . '_id';
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
