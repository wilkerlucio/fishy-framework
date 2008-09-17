<?php

require_once(dirname(__FILE__) . '/ActiveRelation.php');

/**
 * This class provides one to one relations
 *
 * @package default
 * @author wilker
 */
class ActiveRelationOne extends ActiveRelation
{
    public function refresh()
    {
        $foreign_field = $this->get_foreign_field($this->foreign_model);
        $foreign_key_field = $this->foreign_model->primary_key();
        
        $data = $this->foreign_model->find($this->local_model->$foreign_field);
        
        return $data;
    }
    
    public function set_data($data)
    {
        $foreign_field = $this->get_foreign_field($this->foreign_model);
        
        $this->local_model->$foreign_field = $data->primary_key_value();
    }
} // END class ActiveRelationOne extends ActiveRelation
