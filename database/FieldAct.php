<?php

define('FIELD_ACT_GET', 1);
define('FIELD_ACT_SET', 2);
define('FIELD_ACT_CALL', 4);

class FieldAct
{
	private static function _set_file($object, $field, $value)
	{
		
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
