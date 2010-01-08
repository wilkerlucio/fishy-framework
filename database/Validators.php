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
		'invalid_format' => 'The field %s doesn\'t match with a needed format',
		'invalid_confirmation' => 'The field %s is not equal to %s'
	);
	
	/**
	 * Validates if the field is not blank
	 *
	 * @param ActiveRecord $object Object to be tested
	 * @param string $field Field to test
	 * @return boolean
	 * @author wilker
	 */
	public static function validates_presence_of($object, $field, $err = null)
	{
		if (!$object->$field) {
			$object->add_error($field, $err ? $err : sprintf(self::$validations_messages['nonblank'], $field));
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
	public static function validates_email_of($object, $field, $err = null)
	{
		if (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i", $object->$field)) {
			$object->add_error($field, $err ? $err : sprintf(self::$validations_messages['invalid_email'], $field));
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
	public static function validates_numericality_of($object, $field, $err = null)
	{
		if (!is_numeric($object->$field)) {
			$object->add_error($field, $err ? $err : sprintf(self::$validations_messages['invalid_number'], $field));
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
	public static function validates_confirmation_of($object, $field, $field2, $err = null)
	{
		if ($object->$field != $object->$field2) {
			$object->add_error($field, $err ? $err : sprintf(self::$validations_messages['invalid_confirmation'], $field, $field2));
			return false;
		}
		
		return true;
	}
	
	/**
	 * Validates if a field has a unique value at that column in database
	 *
	 * @param ActiveRecord $object Object to be tested
	 * @param string $field Field to test
	 * @return void
	 */
	public static function validates_uniqueness_of($object, $field, $err = null)
	{
		$record = $object->first(array('conditions' => array($field => $object->$field)));
		
		if ($record && $record->id != $object->id) {
			$object->add_error($field, $err ? $err : sprintf(self::$validations_messages['not_unique'], $field, $object->$field));
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
	public static function validates_format_of($object, $field, $format, $err = null)
	{
		if (!preg_match($format, $object->$field)) {
			$object->add_error($field, $err ? $err : sprintf(self::$validations_messages['invalid_format'], $field));
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
	public static function validates_inclusion_of($object, $field, $list, $err = null)
	{
		if (!in_array($object->$field, $list)) {
			$object->add_error($field, $err ? $err : sprintf(self::$validations_messages['invalid_list'], $field, join(', ', $list), $object->$field));
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
