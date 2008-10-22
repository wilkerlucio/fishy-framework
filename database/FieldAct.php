<?php

define('FIELD_ACT_GET', 1);
define('FIELD_ACT_SET', 2);
define('FIELD_ACT_CALL', 4);

class FieldAct
{
	private static function _call_datetime($object, $field, $format = '%m/%d/%Y')
	{
		return date($format, strtotime($object->$field));
	}
	
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
	
	public static function get($name, $arguments)
	{
		return self::work($name, $arguments, 'get');
	}
	
	public static function set($name, $arguments)
	{
		return self::work($name, $arguments, 'set');
	}
	
	public static function call($name, $arguments)
	{
		return self::work($name, $arguments, 'call');
	}
	
	private static function work($name, $arguments, $type)
	{
		return call_user_func_array(array('self', "_{$type}_" . $name), $arguments);
	}
}
