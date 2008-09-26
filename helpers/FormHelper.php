<?php

/**
 * This class provides some functions to help form build
 *
 * @author Wilker Lucio
 */
class Fishy_FormHelper
{
	private static $form_stack = array();
	
	public static function form_for($object, $options = array(), $html_options = array())
	{
		array_push(self::$form_stack, $object);
		
		$html_options = array_merge(array(
			'accept-charset' => 'UTF-8',
			'method' => 'post',
			'action' => ''
		), $html_options);
		
		return self::build_tag('form', $html_options, null, true);
	}
	
	public static function text_field($field, $options = array(), $html_options = array())
	{
		$object = self::get_object();
		$fieldname = self::get_field($field);
		$fieldid = self::get_field_id($field);
		
		$attributes = array_merge(array(
			'id' => $fieldid,
			'name' => $fieldname,
			'value' => $object->$field,
			'type' => 'text',
			'class' => ''
		), $html_options);
		
		if (!is_a($object, 'BlankObject') && $object->field_has_errors($field)) {
			$attributes['class'] .= ' error';
		}
		
		return self::build_tag('input', $attributes);
	}
	
	/**
	 * Generates <select> tag
	 *
	 * @return void
	 * @author wilker
	 */
	public static function select($field, $choices, $options = array(), $html_options = array())
	{
		$object = self::get_object();
		$fieldname = self::get_field($field);
		$fieldid = self::get_field_id($field);
		
		$options = array_merge(array(
			'include_blank' => null,
			'selected' => null
		), $options);
		
		$html_options['name'] = $fieldname;
		$html_options['id'] = $fieldid;
		
		$out  = '<select';
		$out .= self::build_html_attributes($html_options);
		$out .= ">";
		
		if ($options['include_blank'] !== null) {
			$out .= "<option value=\"\">{$options['include_blank']}</option>";
		}
		
		foreach ($choices as $pair) {
			list($name, $value) = $pair;
			
			$attr = array('value' => $value);
			
			if ($options['selected'] !== null && $options['selected'] == $value) {
				$attr['selected'] = 'selected';
			}
			
			$out .= self::build_tag('option', $attr, $name);
		}
		
		$out .= "</select>";
		
		return $out;
	}
	
	public static function select_for_model($field, $collection, $value_field, $name_field, $options = array(), $html_options = array())
	{
		$object = self::get_object();
		
		$options['selected'] = $object->$field;
		
		$choices = array();
		
		foreach ($collection as $item) {
			$choices[] = array($item->$name_field, $item->$value_field);
		}
		
		return self::select($field, $choices, $options, $html_options);
	}
	
	public static function form_end()
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
			$filtred_name = preg_replace('/[^a-z0-9_-]/i', '', $fieldname);
			
			$result = "data[$filtred_name]";
			
			if (Fishy_StringHelper::ends_with($fieldname, '[]')) {
				$result .= '[]';
			}
			
			return $result;
		}
	}
	
	private static function get_field_id($fieldname)
	{
		$object = self::get_object();
		$fieldname = preg_replace('/[^a-z0-9_-]/', '', $fieldname);
		
		if (is_a($object, 'BlankObject')) {
			return $fieldname . '_field';
		} else {
			return $fieldname . '_field';
		}
	}
	
	private static function build_html_attributes($html_options)
	{
		$out = '';
		
		foreach ($html_options as $key => $value) {
			$out .= " $key=\"$value\"";
		}
		
		return $out;
	}
	
	private static function build_tag($tagname, $attributes = array(), $content = null, $force_open = false)
	{
		$attributes = self::build_html_attributes($attributes);
		
		if ($force_open) {
			return "<$tagname $attributes>";
		}
		
		if ($content !== null) {
			return "<$tagname $attributes>$content</$tagname>";
		} else {
			return "<$tagname $attributes />";
		}
	}
}