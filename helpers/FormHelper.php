<?php

/**
 * This class provides some functions to help form build
 *
 * @author Wilker Lucio
 */
class Fishy_FormHelper
{
	private static $form_stack = array();
	
	public static function form_for($object)
	{
		array_push(self::$form_stack, $object);
		
		return '<form action="" method="">';
	}
	
	public static function text_field($field, $options = array())
	{
		$object = self::get_object();
		$field = self::get_field($field);
		
		return '<input type="text" name="' . $field . '" value="' . $object->$field . '" />';
	}
	
	public static function end_form()
	{
		array_pop(self::$form_stack);
		
		return '</form>';
	}
	
	private static function get_object()
	{
		$stack_size = count(self::$form_stack);
		
		if ($stack_size > 0) {
			return self::$form_stack[$stack_size - 1];
		} else {
			return new BlankObject();
		}
	}
	
	private static function get_field($fieldname)
	{
		$object = self::get_object();
		
		if (is_a($object, 'BlankObject')) {
			return $fieldname;
		} else {
			return "data[$fieldname]";
		}
	}
}