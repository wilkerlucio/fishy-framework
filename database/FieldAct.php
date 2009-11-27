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
	
	/**
	 * Automatic upload file for a field
	 *
	 * Example:
	 *   $this->field_as_file("my_file_field", array("public" => true));
	 *
	 * Properties:
	 * - public: determine if the file will be uploaded into a public location, setting
	 *           this property to true you can easly access the uploaded file by
	 *           getting the public url, but your files will be unprotected, use this
	 *           option when you are not dealing with private files
	 */
	private static function _set_file($object, $field, $value, $configuration = array())
	{
		$configuration = array_merge(array(
			"public" => false
		), $configuration);
		
		if (!$value['tmp_name']) {
			return $object->$field;
		}
		
		$new_name = self::normalize_filename($value['name']);
		
		$dir = $configuration["public"] ? FISHY_PUBLIC_PATH . '/uploads/' : self::$upload_base_dir;
		$rel_path = self::create_uniq_path($dir, $new_name);
		$new_path = $dir . $rel_path;
		
		move_uploaded_file($value['tmp_name'], $new_path);
		
		//remove previous file
		$old_path = $object->$field;
		if ($configuration["public"]) $old_path = FISHY_PUBLIC_PATH . '/' . $old_path;
		
		if (is_file($old_path)) {
			unlink($old_path);
		}
		
		return $configuration["public"] ? 'uploads/' . $rel_path : $new_path;
	}
	
	/**
	 * Configure a field to accept one image file and automatic resize
	 *
	 * In the parameters you should pass one hash containing the name of resize (the key of hash)
	 * and the resize configuration (the value)
	 *
	 * Resize configuration:
	 * Its just a simple string following "WIDTHxHEIGHTxMODE", where:
	 *  - WIDTH: the width of resized image
	 *  - HEIGHT: the height of resized image
	 *  - MODE: the resize mode, see Fishy_Image library for a full description about
	 *          resize modes
	 * You can pass zero (0) for width OR height, this way the library will calculate the proportional
	 * size.
	 * You can use null to get a original version of image
	 *
	 * Example:
	 *   $this->field_as_image("my_image_field", array("default" => "300x0x0", "thumbnail" => "30x30x1", "original" => null));
	 */
	private static function _set_image($object, $field, $value, $configuration = array())
	{
		if(!$value['tmp_name']) {
			return $object->$field;
		}
		
		$dir = FISHY_PUBLIC_PATH . '/uploads/';
		
		if (!is_dir($dir)) {
			mkdir($dir);
		}
		
		$new_name = self::normalize_filename($value['name']);
		
		$rel_path = self::create_uniq_path($dir, pathinfo($new_name, PATHINFO_FILENAME));
		$new_path = $dir . $rel_path;
		$ext = pathinfo($new_name, PATHINFO_EXTENSION);
		
		foreach ($configuration as $key => $config) {
			try {
				if ($config) {
					//get info
					list($width, $height, $mode) = explode("x", $config);
				
					//resize
					$image = new Fishy_Image($value['tmp_name']);
					$image->resize((int) $width, (int) $height, (int) $mode);
					$image->save("$new_path.$key.$ext");
					$image->destroy();
				} else {
					$image = new Fishy_Image($value['tmp_name']);
					$image->save("$new_path.$key.$ext");
					$image->destroy();
				}
				
				//remove previous file
				$old_path = $dir . self::parse_image_path($object->$field, $key);
				
				if (is_file($old_path)) {
					unlink($old_path);
				}
			} catch(Exception $e) {
				throw $e;
				
				return $object->$field;
			}
		}
		
		return $rel_path . ".*." . $ext;
	}
	
	private static function _call_image($object, $field, $config, $sample = "default")
	{
		$base = $object->$field;
		
		if ($base) {
			return self::parse_image_path($base, $sample);
		} else {
			return null;
		}
	}
	
	private static function parse_image_path($path, $key)
	{
		return str_replace("*", $key, $path);
	}
	
	private static function create_uniq_path($dir, $name)
	{
		$bits = array(date('Y'), date('m'), date('d'));
		$current = "";
		
		foreach ($bits as $part) {
			$dir .= $part . '/';
			$current .= $part . '/';
			
			if (!is_dir($dir)) {
				mkdir($dir);
			}
		}
		
		return $current . uniqid() . '.' . $name;
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
	
	private static function normalize_filename($name)
	{
		$name = str_replace(" ", "-", strtolower($name));
		$output = '';
		
		for ($i = 0; $i < strlen($name); $i++) {
			if (preg_match("/[a-z0-9.-]/", $name[$i])) {
				$output .= $name[$i];
			}
		}
		
		return $output;
	}
}
