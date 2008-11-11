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

define('FIELD_ACT_GET', 1);
define('FIELD_ACT_SET', 2);
define('FIELD_ACT_CALL', 4);

class FieldAct
{
	private static $upload_base_dir = './';
	
	public static function set_upload_path($path)
	{
		self::$upload_base_dir = $path;
	}
	
	private static function _set_file($object, $field, $value)
	{
		if(!$value['tmp_name']) {
			return $object->$field;
		}
		
		$dir = self::$upload_base_dir;
		
		$bits = array(date('Y'), date('m'), date('d'));
		
		foreach ($bits as $part) {
			$dir .= $part . '/';
			
			if (!is_dir($dir)) {
				mkdir($dir);
			}
		}
		
		$new_path = $dir . uniqid() . '.' . $value['name'];
		
		move_uploaded_file($value['tmp_name'], $new_path);
		
		//remove previous file
		if (is_file($object->$field)) {
			unlink($object->$field);
		}
		
		return $new_path;
	}
	
	private static function _call_datetime($object, $field, $format = '%m/%d/%Y')
	{
		return date($format, strtotime($object->$field));
	}
	
	/**
	 * Get possible formats for a action
	 *
	 * @param string $name Name of act
	 * @return integer A integer with possible flags
	 */
	public static function formats($name)
	{
		$methods = 0;
		
		if (method_exists('FieldAct', '_get_' . $name)) {
			$methods = $methods | FIELD_ACT_GET;
		}
		
		if (method_exists('FieldAct', '_set_' . $name)) {
			$methods = $methods | FIELD_ACT_SET;
		}
		
		if (method_exists('FieldAct', '_call_' . $name)) {
			$methods = $methods | FIELD_ACT_CALL;
		}
		
		return $methods;
	}
	
	/**
	 * Do a get operation
	 * 
	 * @param string $name Name of act
	 * @param array $arguments Arguments of get
	 * @return mixed
	 */
	public static function get($name, $arguments)
	{
		return self::work($name, $arguments, 'get');
	}
	
	/**
	 * Do a set operation
	 * 
	 * @param string $name Name of act
	 * @param array $arguments Arguments of set
	 * @return mixed
	 */
	public static function set($name, $arguments)
	{
		return self::work($name, $arguments, 'set');
	}
	
	/**
	 * Do a call operation
	 * 
	 * @param string $name Name of act
	 * @param array $arguments Arguments of call
	 * @return mixed
	 */
	public static function call($name, $arguments)
	{
		return self::work($name, $arguments, 'call');
	}
	
	/**
	 * Do low level work
	 * 
	 * @param string $name Name of act
	 * @param array $arguments Arguments of get
	 * @param string $type Type of operation
	 * @return mixed
	 */
	private static function work($name, $arguments, $type)
	{
		return call_user_func_array(array('self', "_{$type}_" . $name), $arguments);
	}
}
