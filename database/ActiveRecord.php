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

require_once(dirname(__FILE__) . '/../libraries/Inflector.php');
require_once(dirname(__FILE__) . '/FieldAct.php');
require_once(dirname(__FILE__) . '/Validators.php');
require_once(dirname(__FILE__) . '/DbCommand.php');
require_once(dirname(__FILE__) . '/TableDescriptor.php');
require_once(dirname(__FILE__) . '/ModelCache.php');
require_once(dirname(__FILE__) . '/relations/ActiveRelationOne.php');
require_once(dirname(__FILE__) . '/relations/ActiveRelationMany.php');

/**
 * Active Record class for data access layer
 *
 * @package DB
 * @author wilker
 * @version 0.1.3
 */
abstract class ActiveRecord
{
	protected $_attributes;
	private $_exists;
	private $_relations;
	private $_validators;
	private $_errors;
	private $_scopes;
	private $_scopes_enabled;
	private $_field_act;
	
	/**
	 * Creates a new record
	 *
	 * @return void
	 * @author Wilker
	 **/
	public function __construct($params = array())
	{
		$this->_exists = false;
		$this->_attributes = array();
		$this->_relations = array();
		$this->_validators = array();
		$this->_errors = array();
		$this->_scopes = array();
		$this->_scopes_enabled = array();
		$this->_field_act = array();
		
		$this->initialize_fields();
		$this->fill($params);
		
		$this->setup();
	}
	
	/**
	 * Get a shared instace of current model
	 *
	 * @param string $model_name The name of model
	 * @return mixed The model instance
	 */
	public static function model($model_name)
	{
		$model_name = ucfirst($model_name);
		
		$modelcache = ModelCache::get_instance();
		
		return @$modelcache->$model_name;
	}
	
	public function setup() {}
	
	/**
	 * Fill object with attributes with a given data array using key/value scheme
	 *
	 * @param $data Array containing key as index and values as data
	 * @return void
	 * @author Wilker
	 **/
	public function fill($data)
	{
		foreach ($data as $key => $value) {
			$this->$key = $value;
		}
	}
	
	/**
	 * Return the name of primary key field
	 *
	 * @return string
	 * @author Wilker
	 **/
	public function primary_key()
	{
		return 'id';
	}
	
	/**
	 * Shotcut to get ID value of current register, this method is equivalent to
	 * use:
	 *
	 * $pk = $this->primary_key();
	 * return $this->$pk;
	 *
	 * @return string
	 * @author Wilker
	 */
	public function primary_key_value()
	{
		$pk = $this->primary_key();
		
		return $this->read_attribute($pk);
	}
	
	/**
	 * Get record table
	 *
	 * @return string
	 * @author Wilker
	 **/
	public function table()
	{
		$class = Inflect::pluralize(get_class($this));
		$table = $class[0];
		
		for ($i = 1; $i < strlen($class); $i++) { 
			$char = $class[$i];
			
			if (ord($char) > 64 && ord($char) < 91) {
				$table .= '_' . strtolower($char);
			} else {
				$table .= $char;
			}
		}
		
		return strtolower($table);
	}
	
	/**
	 * Initialize instance attributes
	 *
	 * @return void
	 * @author Wilker
	 **/
	protected function initialize_fields()
	{
		$autospecial_fields = array(
			'created_at' => 'datetime',
			'updated_at' => 'datetime'
		);
		
		$fields = $this->fields();
		
		foreach ($fields as $field) {
			$this->_attributes[$field] = null;
			
			if (isset($autospecial_fields[$field])) {
				$this->register_field_as($autospecial_fields[$field], array($field));
			}
		}
	}
	
	/**
	 * Verify if the record exists at database
	 *
	 * @return boolean true if record exists, false otherwise
	 * @author wilker
	 */
	public function exists()
	{
		return $this->_exists;
	}
	
	/**
	 * Find records at database
	 *
	 * @param $what Type of search
	 * @param $options Options of search
	 * @return mixed
	 * @author wilker
	 */
	public function find($what = 'all', $options = array())
	{
		$options = array_merge(array(
			'conditions' => '',
			'order'      => $this->primary_key() . ' ASC',
			'limit'      => '',
			'offset'     => false,
			'select'     => '*',
			'from'       => '`' . $this->table() . '`',
			'groupby'    => '',
			'joins'      => array()
		), $options);
		
		switch ($what) {
			case 'all':
				return $this->find_every($options);
				break;
			
			case 'first':
				return $this->find_initial($options);
				break;
			
			case 'last':
				return $this->find_last($options);
				break;
			
			default:
				return $this->find_from_ids($what, $options);
				break;
		}
	}
	
	/**
	 * Wrapper for find with all as first argument
	 *
	 * @return mixed
	 * @author wilker
	 */
	public function all()
	{
		$args = func_get_args();
		array_unshift($args, 'all');
		
		return call_user_func_array(array($this, 'find'), $args);
	}
	
	/**
	 * Wrapper for find with first as first argument
	 *
	 * @return mixed
	 * @author wilker
	 */
	public function first()
	{
		$args = func_get_args();
		array_unshift($args, 'first');
		
		return call_user_func_array(array($this, 'find'), $args);
	}
	
	/**
	 * Wrapper for find with last as first argument
	 *
	 * @return mixed
	 * @author wilker
	 */
	public function last()
	{
		$args = func_get_args();
		array_unshift($args, 'last');
		
		return call_user_func_array(array($this, 'find'), $args);
	}
	
	/**
	 * Find records by specifique fields
	 *
	 * @return array
	 * @author Wilker
	 **/
	public function dynamic_find($type, $fields, $values)
	{
		$fields = explode('_and_', $fields);
		
		$conditions = array();
		
		foreach ($fields as $field) {
			$value = $this->sanitize(array_shift($values));

			$conditions[] = "`$field` = '$value'";
		}

		$conditions = implode(" AND ", $conditions);
		
		$options = array(
			'conditions' => $conditions
		);
		
		if (count($values) > 0) {
			$user_options = array_shift($values);
			
			if (is_array($user_options)) {
				$options = array_merge($options, $user_options);
			}
		}
		
		switch ($type) {
			case 'all_by':
				return $this->all($options);
			case 'by':
				return $this->first($options);
		}
	}
	
	/**
	 * Get the number of rows at table
	 *
	 * @return integer
	 * @author Wilker
	 **/
	public function count($options = array())
	{
		$options['select'] = 'count(*) as total';
		
		$result = $this->first($options);
		
		return $result->total;
	}
	
	/**
	 * Save object into database, if the object exists, the instance is
	 * only updated at database
	 *
	 * @return boolean
	 * @author wilker
	 */
	public function save()
	{
		$this->create_or_update();
	}
	
	/**
	 * Delete current register from database
	 *
	 * @return boolean
	 * @author wilker
	 */
	public function destroy()
	{
		//check if record exists before delete
		if (!$this->exists()) {
			return false;
		}
		
		$this->before_destroy();
		
		$pk     = $this->primary_key();
		$pk_val = $this->sanitize($this->$pk);
		$table  = $this->table();
		
		$sql = "DELETE FROM `$table` WHERE `$pk` = '$pk_val'";
		
		DbCommand::execute($sql);
		
		$this->_exists = false;
		
		$this->after_destroy();
		
		return true;
	}
	
	/**
	 * Truncate table
	 *
	 * @return void
	 * @author wilker
	 */
	public function truncate()
	{
		$table = $this->table();
		
		$sql = "TRUNCATE `$table`";
		
		DbCommand::execute($sql);
	}
	
	/**
	 * Map result data into object
	 *
	 * @return mixed
	 * @author Wilker
	 **/
	protected function map_object($data)
	{
		$class = get_class($this);
		$object = new $class();
		
		foreach ($data as $key => $value) {
			$object->write_attribute($key, $value);
		}
		
		return $object;
	}
	
	private function construct_finder_sql($options)
	{
		$sql  = "SELECT {$options['select']} ";
		$sql .= "FROM {$options['from']} ";
		
		$this->add_joins($sql, $options['joins']);
		$this->add_conditions($sql, $options['conditions']);
		$this->add_groupby($sql, $options['groupby']);
		$this->add_order($sql, $options['order']);
		$this->add_limit($sql, $options['limit'], $options['offset']);
		
		return $sql;
	}
	
	private function add_joins(&$sql, $joins)
	{
		if (is_array($joins)) {
			$cur_table = $this->table();
			$cur_key = $this->primary_key();
			$cur_fk = strtolower(get_class($this)) . '_id';
			
			foreach ($joins as $join) {
				$model = ActiveRecord::model($join);
				
				$join_table = $model->table();
				$join_key = $model->primary_key();
				$join_fk = strtolower(get_class($model)) . '_id';
				
				$sql .= "INNER JOIN `{$join_table}` ON `{$join_table}`.`{$cur_fk}` = `{$cur_table}`.`{$cur_key}` ";
			}
		} elseif (is_string($joins)) {
			$sql .= $joins . " ";
		}
	}
	
	private function add_conditions(&$sql, $conditions)
	{
		$nest = array();
		
		foreach ($this->_scopes_enabled as $scope) {
			$nest[] = $this->build_conditions($this->_scopes[$scope]);
		}
		
		if ($conditions) {
			$nest[] = $this->build_conditions($conditions);
		}
		
		if (count($nest) > 0) {
			$sql .= 'WHERE (' . implode(') AND (', $nest) . ') ';
		}
	}
	
	private function build_conditions($conditions)
	{
		$sql = '';
		
		if (is_array($conditions)) {
			if (array_keys($conditions) === range(0, count($conditions) - 1)) {
				$query = array_shift($conditions);
				
				for($i = 0; $i < strlen($query); $i++) {
					if ($query[$i] == '?') {
						if (count($conditions) == 0) {
							throw new QueryMismatchParamsException('The number of question marks is more than provided params');
						}
						
						$sql .= $this->prepare_for_value(array_shift($conditions));
					} else {
						$sql .= $query[$i];
					}
				}
			} else {
				$factors = array();
				
				foreach ($conditions as $key => $value) {
					$matches = array();
					
					if (preg_match("/([a-z_].*?)\s*((?:[><!=\s]|LIKE|IS|NOT)+)/i", $key, $matches)) {
						$key  = $matches[1];
						$op   = strtoupper($matches[2]);
					} else {
						if ($value === null) {
							$op = 'IS';
						} elseif (is_array($value)) {
							$op = 'IN';
						} else {
							$op = "=";
						}
					}
					
					$value = $this->prepare_for_value($value);
					
					$factors[] = "`$key` $op $value";
				}
				
				$sql .= implode(" AND ", $factors);
			}
		} else {
			$sql .= $conditions;
		}
		
		return $sql;
	}
	
	private function add_groupby(&$sql, $order)
	{
		if ($order) {
			$sql .= "GROUP BY $order ";
		}
	}
	
	private function add_order(&$sql, $order)
	{
		if ($order) {
			$sql .= "ORDER BY $order ";
		}
	}
	
	private function add_limit(&$sql, $limit, $offset)
	{
		if ($limit) {
			if ($offset !== false) {
				$sql .= "LIMIT $offset, $limit ";
			} else {
				$sql .= "LIMIT $limit ";
			}
		}
	}
	
	private function find_every($options)
	{
		return $this->find_by_sql($this->construct_finder_sql($options));
	}
	
	private function find_initial($options)
	{
		$options['limit'] = 1;
		
		$data = $this->find_every($options);
		
		return count($data) > 0 ? $data[0] : null;
	}
	
	private function find_last($options)
	{
		if ($options['order']) {
			$options['order'] = $this->reverse_sql_order($options['order']);
		}
		
		return $this->find_initial($options);
	}
	
	private function find_from_ids($ids, $options)
	{
		$pk = $this->primary_key();
		
		if (is_array($ids)) {
			$options['conditions'] = "`$pk` in ('" . implode("','", $this->sanitize_array($ids)) . "')";
		} else {
			$id = $this->sanitize($ids);
			$options['conditions'] = "`$pk` = '$id'";
		}
		
		return is_array($ids) ? $this->find_every($options) : $this->find_initial($options);
	}
	
	private function reverse_sql_order($order)
	{
		$reversed = explode(',', $order);
		
		foreach ($reversed as $k => $rev) {
			if (preg_match('/\s(asc|ASC)$/', $rev)) {
				$rev = preg_replace('/\s(asc|ASC)$/', ' DESC', $rev);
			} elseif (preg_match('/\s(desc|DESC)$/', $rev)) {
				$rev = preg_replace('/\s(desc|DESC)$/', ' ASC', $rev);
			} elseif (!preg_match('/\s(acs|ASC|desc|DESC)$/', $rev)) {
				$rev .= " DESC";
			}
			
			$reversed[$k] = $rev;
		}
		
		return implode(',', $reversed);
	}
	
	/**
	 * Find records by using a sql statement, avoid to use this method if you
	 * can do it in another way (like using default find methods)
	 *
	 * @param $sql SQL Statement
	 * @return array Array of objects returned by query
	 */
	public function find_by_sql($sql)
	{
		$data = DbCommand::all($sql);
		$data = array_map(array($this, 'map_object'), $data);
		
		foreach ($data as $model) {
			$model->_exists = true;
		}
		
		return $data;
	}
	
	private function create_or_update()
	{
		$this->before_save();
		
		if ($this->exists()) {
			$this->before_update();
			$this->validate_ex();
			$this->save_relations();
			$this->update();
			$this->after_update();
		} else {
			$this->before_create();
			$this->validate_ex();
			$this->save_relations();
			$this->create();
			$this->after_create();
		}
		
		$this->after_save();
	}
	
	private function create()
	{
		$this->write_magic_time('created_at');
		$this->write_magic_time('updated_at');
		
		$pk = $this->primary_key();
		$table = $this->table();
		$fields = $this->map_real_fields();
		
		$sql_fields = implode("`,`", array_keys($fields));
		$sql_values = implode(",", array_map(array($this, 'prepare_for_value'), $fields));
		
		$sql = "INSERT INTO `$table` (`$sql_fields`) VALUES ($sql_values);";
		
		DbCommand::execute($sql);
		
		$this->$pk = DbCommand::insert_id();
		$this->_exists = true;
	}
	
	private function update()
	{
		$this->write_magic_time('updated_at');
		
		$pk = $this->primary_key();
		$pk_value = $this->sanitize($this->$pk);
		$table = $this->table();
		$fields = $this->map_real_fields();
		
		$sql_set = array();
		
		foreach ($fields as $key => $value) {
			$sql_set[] = "`$key` = " . $this->prepare_for_value($value);
		}
		
		$sql_set = implode(",", $sql_set);
		
		$sql = "UPDATE `$table` SET $sql_set WHERE `$pk` = '$pk_value';";
		
		DbCommand::execute($sql);
	}
	
	private function save_relations()
	{
		foreach ($this->_relations as $rel) {
			$rel->save();
		}
	}
	
	public function fields()
	{
		$descriptor = TableDescriptor::get_instance();
		$table = $this->table();
		
		return $descriptor->$table;
	}
	
	private function map_real_fields()
	{
		$pk = $this->primary_key();
		$data = array();
		$fields = $this->fields();
		
		foreach ($fields as $field) {
			if ($field != $pk) {
				$data[$field] = isset($this->_attributes[$field]) ? $this->_attributes[$field] : null;
			}
		}
		
		return $data;
	}
	
	private function map_real_fields_sanitized()
	{
		return $this->sanitize_array($this->map_real_fields());
	}
	
	private function sanitize($data)
	{
		if ($data === null) {
			return 'NULL';
		} elseif (is_array($data)) {
			return '(\'' . implode('\', \'', $this->sanitize_array($data)) . '\')';
		}
		
		return mysql_real_escape_string($data);
	}
	
	private function sanitize_array($data)
	{
		return array_map(array($this, "sanitize"), $data);
	}
	
	private function prepare_for_value($value)
	{
		$sanitized = $this->sanitize($value);
		
		if (is_string($value)) {
			return "'$sanitized'";
		} else {
			return $sanitized;
		}
	}
	
	public function read_all_attributes()
	{
		return $this->_attributes;
	}
	
	public function read_attribute($attribute)
	{
		return isset($this->_attributes[$attribute]) ? $this->_attributes[$attribute] : null;
	}
	
	public function write_attribute($attribute, $value)
	{
		$this->_attributes[$attribute] = $value;
	}
	
	private function write_magic_time($field)
	{
		$fields = $this->fields();
		
		if (in_array($field, $fields)) {
			$date = date('Y-m-d H:i:s');
			$this->write_attribute($field, $date);
		}
	}
	
	/**
	 * Handles access to dynamic properties
	 *
	 * @return mixed
	 * @author wilker
	 */
	public function __get($attribute)
	{
		//check for method accessor
		if (method_exists($this, 'get_' . $attribute)) {
			return call_user_func(array($this, 'get_' . $attribute));
		}

		//check for id
		if ($attribute == 'id') {
			return $this->primary_key_value();
		}
		
		//chech for field act command
		if (isset($this->_field_act[$attribute])) {
			$data = $this->_field_act[$attribute];
			
			if ($data[0] & FIELD_ACT_GET) {
				return FieldAct::get($data[1], $data[2]);
			}
		}
		
		//check for named scope
		if (isset($this->_scopes[$attribute])) {
			return $this->append_scope($attribute);
		}
		
		//check for relation
		if (isset($this->_relations[$attribute])) {
			return $this->_relations[$attribute]->get_data();
		}
		
		//get table attribute
		return $this->read_attribute($attribute);
		
		//dispatch exception
		//throw new ActiveRecordInvalidAttributeException();
	}
	
	/**
	 * Handles access to write dynamic properties
	 *
	 * @return void
	 * @author wilker
	 */
	public function __set($attribute, $value)
	{
		//chech for field act command
		if (isset($this->_field_act[$attribute])) {
			$data = $this->_field_act[$attribute];
			
			if ($data[0] & FIELD_ACT_SET) {
				$args = $data[2];
				
				$obj = array_shift($args);
				$field = array_shift($args);
				
				array_unshift($args, $value);
				array_unshift($args, $field);
				array_unshift($args, $obj);
				
				if ($data[0] & FIELD_ACT_SET) {
					$this->write_attribute($attribute, FieldAct::set($data[1], $args));
				}
				
				return;
			}
		}
		
		//check for method accessor
		if (method_exists($this, 'set_' . $attribute)) {
			call_user_func(array($this, 'set_' . $attribute), $value);
		} elseif (isset($this->_relations[$attribute])) {
			$this->_relations[$attribute]->set_data($value);
		} else {
			//set attribute
			$this->write_attribute($attribute, $value);
		}
	}
	
	/**
	 * Handles access to dynamic methods
	 *
	 * @return mixed
	 * @author wilker
	 */
	public function __call($name, $arguments)
	{
		//for use in preg matches
		$matches = array();
		
		//chech for field act command
		if (isset($this->_field_act[$name])) {
			$data = $this->_field_act[$name];
			
			if ($data[0] & FIELD_ACT_CALL) {
				$args = $data[2];
				
				foreach ($arguments as $arg) {
					$args[] = $arg;
				}
				
				return FieldAct::call($data[1], $args);
			}
		}
		
		//do a get
		if (preg_match('/^get_(.+)/', $name, $matches)) {
			$var_name = $matches[1];
			
			return $this->$var_name ? $this->$var_name : $arguments[0];
		}
		
		//try to catch validator assign
		if (substr($name, 0, 9) == 'validates') {
			return $this->register_validator($name, $arguments);
		}
		
		//try to catch field act assign
		if (substr($name, 0, 9) == 'field_as_') {
			return $this->register_field_as(substr($name, 9), $arguments);
		}
		
		//try to catch dynamic find
		
		if (preg_match("/^find_(all_by|by)_(.*)/", $name, $matches)) {
			return $this->dynamic_find($matches[1], $matches[2], $arguments);
		}
		
		//send to model try to parse
		$this->call($name, $arguments);
	}
	
	public function call($name)
	{
		throw new Exception("Method $name is not found in " . get_class($this));
	}
	
	public function __toString() {
		$base = "ActiveRecord::" . get_class($this);
		
		if ($this->exists()) {
			$pk = $this->primary_key_value();
			$base .= "($pk)";
		}
		
		return $base;
	}
	
	/**
	 * Get all data of model
	 *
	 * @return string
	 * @author Wilker
	 **/
	public function inspect()
	{
		$out  = $this->__toString();
		
		foreach ($this->_attributes as $key => $value) {
			$out .= "\n" . $key . " => " . $value;
		}
		
		$out .= "\n";
		
		return $out;
	}
	
	/**
	 * Estabilishy has one connection with another record
	 *
	 * @return void
	 * @author wilker
	 */
	protected function has_one($expression, $options = array())
	{
		list($model, $name) = $this->parse_relation_expression($expression);
		
		$this->_relations[$name] = new ActiveRelationOne($this, $model, $options);
	}
	
	/**
	 * Estabilishy a connection with many related records
	 *
	 * @return void
	 * @author wilker
	 */
	protected function has_many($expression, $options = array())
	{
		list($model, $name) = $this->parse_relation_expression($expression);
		
		$this->_relations[$name] = new ActiveRelationMany($this, Inflect::singularize($model), $options);
	}
	
	/**
	 * Aliases to has one
	 *
	 * @return void
	 * @author wilker
	 */
	protected function belongs_to($model, $options = array())
	{
		$this->has_one($model, $options);
	}
	
	/**
	 * Gives a relation expression and return elements
	 *
	 * @param string $expression Expression to be evaluated
	 * @return array Array containing: [0] => name of relation, [1] => foreign model
	 */
	protected function parse_relation_expression($expression)
	{
		$parts = explode(' as ', $expression);
		
		$model = $parts[0];
		
		if (count($parts) > 1) {
			$name = $parts[1];
		} else {
			$name = $model;
			$model = ucfirst($model);
		}
		
		return array($model, $name);
	}
	
	/**
	 * Get a description of all of model relations
	 *
	 * @return array
	 */
	public function describe_relations()
	{
		$relations = array();
		
		foreach ($this->_relations as $key => $rel) {
			$relations[] = $this->describe_relation($key);
		}
		
		return $relations;
	}
	
	/**
	 * Get the definition of a relation
	 *
	 * @param string $rel The name of relation to check
	 * @return array Array with relation data
	 */
	public function describe_relation($rel)
	{
		if (!isset($this->_relations[$rel])) {
			return null;
		}
		
		$r = $this->_relations[$rel];
		
		return array(
			'name' => $rel,
			'instance' => $r,
			'kind' => get_class($r),
			'model' => $r->get_foreign_model(),
			'loaded' => $r->is_loaded()
		);
	}
	
	/**
	 * Compare two arrays of to get different records of then
	 *
	 * @param array $col1 First array
	 * @param array $col2 Second array
	 * @return array Array with difference between first and second collections
	 */
	public static function model_diff($col1, $col2)
	{
		$keeplist = array();
		
		foreach ($col1 as $item) {
			$keep = true;
			
			foreach ($col2 as $item2) {
				if ($item->equal($item2)) {
					$keep = false;
					break;
				}
			}
			
			if ($keep) {
				$keeplist[] = $item;
			}
		}
		
		return $keeplist;
	}
	
	/**
	 * Test if current object is equals to another
	 *
	 * @param object $obj2 The model to test
	 * @return boolean
	 */
	public function equal($obj2)
	{
		return ($obj2->table() == $this->table()) && ($obj2->primary_key_value() == $this->primary_key_value());
	}
	
	//Validators
	
	private function register_validator($validator, $arguments)
	{
		array_unshift($arguments, $this);
		
		$this->_validators[] = array($validator, $arguments);
	}
	
	protected function validate_ex()
	{
		if (!$this->is_valid()) {
			throw new InvalidRecordException('This record has some invalid fields, please fix problems and try again');
		}
	}
	
	/**
	 * Test if current object is valid
	 *
	 * @return boolean
	 */
	public function is_valid()
	{
		$this->_errors = array();
		$valid = $this->validate();
		
		foreach ($this->_validators as $validator) {
			list($method, $arguments) = $validator;
			
			if (!call_user_func_array(array('ActiveRecord_Validators', $method), $arguments)) {
				$valid = false;
			}
		}
		
		return $valid;
	}
	
	/**
	 * Inject one error at object
	 *
	 * @param string $field The name of field that has the error
	 * @param string $error Error message
	 * @return void
	 */
	public function add_error($field, $error)
	{
		$this->_errors[$field][] = $error;
	}
	
	/**
	 * Check if a field contains errors
	 *
	 * @param string $field The name of field to check
	 * @return boolean
	 */
	public function field_has_errors($field)
	{
		return isset($this->_errors[$field]);
	}
	
	/**
	 * Get a flatten array with all errors
	 *
	 * @return array
	 */
	public function problems()
	{
		$flat = array();
		
		foreach ($this->_errors as $field_errors) {
			foreach ($field_errors as $error) {
				$flat[] = $error;
			}
		}
		
		return $flat;
	}
	
	/**
	 * Get all errors of one field
	 *
	 * @param string $field The name of field
	 * @return array
	 */
	public function field_problems($field)
	{
		return isset($this->_errors[$field]) ? $this->_errors[$field] : array();
	}
	
	/**
	 * Override this method to enable custom validations
	 */
	public function validate() { return true; }
	
	//Conversors
	
	public function to_array()
	{
		return $this->_attributes;
	}
	
	public function to_json()
	{
		return json_encode($this->to_array());
	}
	
	//Named Scopes
	
	/**
	 * Create a new scope into model
	 *
	 * @param string $scope The name of new scope
	 * @param mixed $conditions The conditions of scope, this variable can be like conditions statement of query
	 * @return void
	 */
	protected function named_scope($scope, $conditions)
	{
		$this->_scopes[$scope] = $conditions;
	}
	
	private function append_scope($scope)
	{
		$class = get_class($this);
		$scoped = new $class;
		
		foreach ($this->_scopes_enabled as $se) {
			$scoped->_scopes_enabled[] = $se;
		}
		
		$scoped->_scopes_enabled[] = $scope;
		
		return $scoped;
	}
	
	//Field act helpers
	
	protected function register_field_as($name, $arguments)
	{
		$field = $arguments[0];
		
		array_unshift($arguments, $this);
		
		$formats = FieldAct::formats($name);
		
		$this->_field_act[$field] = array($formats, $name, $arguments);
	}
	
	//Tree Helpers
	
	/**
	 * Make a tree like relations to model
	 *
	 * @param string $parent_field The name of field that make relation possible
	 * @return void
	 */
	protected function act_as_tree($parent_field = 'parent_id')
	{
		$this->has_many(strtolower(Inflect::pluralize(get_class($this))) . ' as childs', array('foreign_field' => $parent_field));
		$this->belongs_to(strtolower(get_class($this)) . ' as parent', array('foreign_field' => $parent_field));
	}
	
	//Events
	
	protected function before_save() {}
	protected function after_save() {}
	
	protected function before_update() {}
	protected function after_update() {}
	
	protected function before_create() {}
	protected function after_create() {}
	
	protected function before_destroy() {}
	protected function after_destroy() {}
	
} // END abstract class ActiveRecord

//Exceptions

class InvalidRecordException extends Exception {}
class QueryMismatchParamsException extends Exception {}
