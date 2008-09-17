<?php

require_once(dirname(__FILE__) . '/ActiveRelation.php');

/**
 * This class provides one to many relation feature
 *
 * @package default
 * @author wilker
 */
class ActiveRelationMany extends ActiveRelation
{
    public function refresh()
    {
        $local_field = $this->get_foreign_field($this->local_model);
        
        $data = $this->foreign_model->all(array('conditions' => array($local_field => $this->local_model->primary_key_value())));
        
        return $data;
    }
    
    public function set_data($data)
    {
        //TODO: implement set data when receiving one array as data
    }
} // END class ActiveRelationMany extends ActiveRelation
