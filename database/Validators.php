<?php

/**
 * Provides the validators
 *
 * @package DB
 * @author wilker
 */
class ActiveRecord_Validators
{
	public static $validations_messages = array(
		'nonblank' => 'The field %s cannot be blank',
		'invalid_email' => 'The field %s is not a valid e-mail',
		'invalid_number' => 'The field %s is not a number',
		'not_unique' => 'The field %s with value %s is already exists, try another value',
		'invalid_list' => 'The field %s needs to be one of these values: %s (currently: %s)',
		'invalid_format' => 'The field %s doesn\'t match with a needed format'
	);
	
	/**
	 * Validates if the field is not blank
	 *
	 * @param ActiveRecord $object Object to be tested
	 * @param string $field Field to test
	 * @return boolean
	 * @author wilker
	 */
	public static function validates_presence_of($object, $field)
	{
		if (!$object->$field) {
			$object->add_error($field, sprintf(self::$validations_messages['nonblank'], $field));
			return false;
		}
		
		return true;
	}
	
	/**
	 * Validates if the field is a valid e-mail
	 *
	 * @param ActiveRecord $object Object to be tested
	 * @param string $field Field to test
	 * @return boolean
	 */
	public static function validates_email_of($object, $field)
	{
		if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $object->$field)) {
			$object->add_error($field, sprintf(self::$validations_messages['invalid_email'], $field));
			return false;
		}
		
		return true;
	}
	
	/**
	 * Validates if a field contains a valid number
	 *
	 * @param ActiveRecord $object Object to be tested
	 * @param string $field Field to test
	 * @return boolean
	 * @author wilker
	 */
	public static function validates_numericality_of($object, $field)
	{
		if (!is_numeric($object->$field)) {
			$object->add_error($field, sprintf(self::$validations_messages['invalid_number'], $field));
			return false;
		}
		
		return true;
	}
	
	/**
	 * undocumented function
	 *
	 * @return void
	 * @author wilker
	 */
	public static function validates_confirmation_of($object, $field)
	{
		return true;
	}
	
	/**
	 * Validates if a field has a unique value at that column in database
	 *
	 * @param ActiveRecord $object Object to be tested
	 * @param string $field Field to test
	 * @return void
	 */
	public static function validates_uniqueness_of($object, $field)
	{
		$n = $object->count(array('conditions' => array($field => $object->$field)));
		
		if ($n > 0) {
			$object->add_error($field, sprintf(self::$validations_messages['not_unique'], $field, $object->$field));
			return false;
		}
		
		return true;
	}
	
	/**
	 * Validates if a field match with a regular expression
	 *
	 * @param ActiveRecord $object Object to be tested
	 * @param string $field Field to test
	 * @param string $format Expression to be evaluated
	 * @return void
	 */
	public static function validates_format_of($object, $field, $format)
	{
		if (!preg_match($format, $object->$field)) {
			$object->add_error($field, sprintf(self::$validations_messages['invalid_format'], $field));
			return false;
		}
		
		return true;
	}
	
	/**
	 * Validates if a field is contained on a list
	 *
	 * @param ActiveRecord $object Object to be tested
	 * @param string $field Field to test
	 * @param string $format List of valid values
	 * @return void
	 */
	public static function validates_inclusion_of($object, $field, $list)
	{
		if (!in_array($object->$field, $list)) {
			$object->add_error($field, sprintf(self::$validations_messages['invalid_list'], $field, join(', ', $list), $object->$field));
			return false;
		}
		
		return true;
	}
	
	public static function validates_size_of($object, $field, $options)
	{
		$size = strlen($object->$field);
		
		//TODO: size validations
		
		return true;
	}
} // END class Validators
