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
    
    protected $local_model;
    protected $foreign_model;
    protected $options;
    
    public function __construct($local_model, $foreign_model, $options = array())
    {
        $this->local_model = $local_model;
        $this->foreign_model = ActiveRecord::model($foreign_model);
        $this->options = $options;
        
        $this->_data = null;
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
    
    public abstract function set_data($data);
    
    public abstract function refresh();
} // END abstract class ActiveRelation
