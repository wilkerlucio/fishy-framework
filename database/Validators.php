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
		'invalid_email' => 'The field %s is not a valid e-mail'
	);
	
	/**
	 * Validates if the field is not blank
	 *
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
	 * @return boolean
	 * @author wilker
	 */
	public static function validates_numericality_of($object, $field)
	{
		
	}
	
	/**
	 * undocumented function
	 *
	 * @return void
	 * @author wilker
	 */
	public static function validates_confirmation_of($object, $field)
	{
		
	}
} // END class Validators
