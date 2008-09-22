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
		if (isset($this->options['through'])) {
			$rel_table = $this->options['through'];
			$foreign_table = $this->foreign_model->table();
			$local_field = $this->get_local_field();
			$foreign_field = $this->get_foreign_field();
			
			$foreign_primary_key = $this->foreign_model->primary_key();
			$id = $this->local_model->primary_key_value();
			
			$data = $this->foreign_model->find_by_sql("
				SELECT
					`{$foreign_table}`.*
				FROM
					`{$foreign_table}`
					right join `{$rel_table}` on `{$rel_table}`.`{$foreign_field}` = `{$foreign_table}`.`{$foreign_primary_key}`
				WHERE
					`{$rel_table}`.`{$local_field}` = '{$id}'
			");
		} else {
			$local_field = $this->get_foreign_field($this->local_model);
			
			$data = $this->foreign_model->all(array('conditions' => array($local_field => $this->local_model->primary_key_value())));
		}
		
		return $data;
	}
	
	public function set_data($data)
	{
		//TODO: implement set data when receiving one array as data
	}
} // END class ActiveRelationMany extends ActiveRelation
