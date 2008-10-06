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
			$foreign_field = $this->get_foreign_field($this->foreign_model);
			
			$foreign_primary_key = $this->foreign_model->primary_key();
			$id = $this->local_model->primary_key_value();
			
			$sql = "
				SELECT
					`{$foreign_table}`.*
				FROM
					`{$foreign_table}`
					right join `{$rel_table}` on `{$rel_table}`.`{$foreign_field}` = `{$foreign_table}`.`{$foreign_primary_key}`
				WHERE
					`{$rel_table}`.`{$local_field}` = '{$id}'
			";
			
			$data = $this->foreign_model->find_by_sql($sql);
		} else {
			$local_field = $this->get_foreign_field($this->local_model);
			
			$data = $this->foreign_model->all(array('conditions' => array($local_field => $this->local_model->primary_key_value())));
		}
		
		return $data;
	}
	
	public function set_data($data)
	{
		if (is_string($data)) {
			$data = $this->foreign_model->find(explode(',', $data));
		}
		
		if (is_array($data)) {
			$add = ActiveRecord::model_diff($data, $this->get_data());
			$rem = ActiveRecord::model_diff($this->get_data(), $data);
			
			foreach ($add as $item) {
				$this->add_relation($item);
			}
			
			foreach ($rem as $item) {
				$this->break_relation($item);
			}
		}
	}
	
	protected function add_relation($object)
	{
		if (isset($this->options['through'])) {
			$rel_table = $this->options['through'];
			
			$local_field = $this->get_local_field();
			$foreign_field = $this->get_foreign_field($this->foreign_model);
			
			$local_id = $this->local_model->primary_key_value();
			$foreign_id = $object->primary_key_value();
			
			//TODO: fix it to a more generic way
			DbCommand::execute("INSERT INTO `{$rel_table}` (`{$local_field}`, `{$foreign_field}`) VALUES ('{$local_id}', '{$foreign_id}')");
		} else {
			$local_field = $this->get_foreign_field($this->local_model);
			
			$object->$local_field = $this->local_model->primary_key_value();
			$object->save();
		}
	}
	
	protected function break_relation($object)
	{
		if (isset($this->options['through'])) {
			$rel_table = $this->options['through'];
			
			$local_field = $this->get_local_field();
			$foreign_field = $this->get_foreign_field($this->foreign_model);
			
			$local_id = $this->local_model->primary_key_value();
			$foreign_id = $object->primary_key_value();
			
			//TODO: fix it to a more generic way
			DbCommand::execute("DELETE FROM `{$rel_table}` WHERE `{$local_field}` = '{$local_id}' and `{$foreign_field}` = '{$foreign_id}'");
		} else {
			$local_field = $this->get_foreign_field($this->local_model);
			
			$object->$local_field = null;
			$object->save();
		}
	}
} // END class ActiveRelationMany extends ActiveRelation
