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
	
	public static function form_end()
	{
		array_pop(self::$form_stack);
		
		return '</form>';
	}
	
	public static function text_field($field, $options = array(), $html_options = array())
	{
		$object = self::get_object();
		$fieldname = self::get_field($field);
		$fieldid = self::get_field_id($field);
		$normal = self::get_normalized_field($field);
		
		$attributes = array_merge(array(
			'id' => $fieldid,
			'name' => $fieldname,
			'value' => $object->$normal,
			'type' => 'text',
			'class' => ''
		), $html_options);
		
		if (!is_a($object, 'BlankObject') && $object->field_has_errors($field)) {
			$attributes['class'] .= ' error';
		}
		
		return self::build_tag('input', $attributes);
	}
	
	public static function text_area($field, $options = array(), $html_options = array())
	{
		$object = self::get_object();
		$fieldname = self::get_field($field);
		$fieldid = self::get_field_id($field);
		$normal = self::get_normalized_field($field);
		
		$attributes = array_merge(array(
			'id' => $fieldid,
			'name' => $fieldname,
			'class' => ''
		), $html_options);
		
		if (!is_a($object, 'BlankObject') && $object->field_has_errors($normal)) {
			$attributes['class'] .= ' error';
		}
		
		return self::build_tag('textarea', $attributes, $object->$normal);
	}
	
	public static function password_field($field, $options = array(), $html_options = array())
	{
		$html_options = array_merge(array('type' => 'password', 'value' => ''), $html_options);
		
		return self::text_field($field, $options, $html_options);
	}
	
	public static function hidden_field($field, $options = array(), $html_options = array())
	{
		$html_options = array_merge(array('type' => 'hidden'), $html_options);
		
		return self::text_field($field, $options, $html_options);
	}
	
	public static function relational_field($field, $options = array(), $html_options = array())
	{
		$object = self::get_object();
		$normal = self::get_normalized_field($field);
		
		$options = array_merge(array(
			'id_field' => 'id',
			'value_field' => 'name'
		), $options);
		
		if (!isset($options['query'])) $options['query'] = array('select' => "{$options['id_field']}, {$options['value_field']}", 'order' => $options['value_field']);
		
		$foreign = $object->describe_relation($field);
		$all = $foreign['model']->all($options['query']);
		
		$filter = ActiveRecord::model_diff($all, $object->$normal);
		
		$hidden_value = array();
		
		foreach ($object->$normal as $comes) {
		    $hidden_value[] = $comes->primary_key_value();
		}
		
		$hidden_value = implode(',', $hidden_value);
		
		$common_html = array(
			'multiple' => 'multiple',
			'size' => '6'
		);
		
		$button_html = array(
			'type' => 'button'
		);
		
		$hidden_id = self::get_field_id($field);
		$source_id = self::get_field_id($field . '_source');
		$destiny_id = self::get_field_id($field . '_destiny');
		
		$out = '';
		
		$out .= self::hidden_field($field, array(), array('value' => $hidden_value));
		$out .= self::select_for_model($field . '_source', $filter, $options['value_field'], $options['id_field'], array(), $common_html);
		$out .= "<br /><br />";
		$out .= self::build_tag('button', array_merge(array('onclick' => "Fishy.Util.move_options('#{$source_id}', '#{$destiny_id}')"), $button_html), 'Adicionar');
		$out .= self::build_tag('button', array_merge(array('onclick' => "Fishy.Util.move_options('#{$destiny_id}', '#{$source_id}')"), $button_html), 'Remover');
		$out .= "<br /><br />";
		$out .= self::select_for_model($field . '_destiny', $object->$normal, $options['value_field'], $options['id_field'], array(), $common_html);
		$out .= "<script type=\"text/javascript\"> Fishy.Util.relational_map('#{$destiny_id}', '#{$hidden_id}') </script>";
		
		return $out;
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
		$normal = self::get_normalized_field($field);
		
		$options = array_merge(array(
			'include_blank' => null,
			'selected' => $object->$normal
		), $options);
		
		if ($options['selected'] !== null && !is_array($options['selected'])) {
			$options['selected'] = array((string) $options['selected']);
		}
		
		$html_options = array_merge(array(
    		'name' => $fieldname,
    		'id' => $fieldid,
    		'class' => ''
		), $html_options);
		
		if (!is_a($object, 'BlankObject') && $object->field_has_errors($normal)) {
			$html_options['class'] .= ' error';
		}
		
		$out  = '<select';
		$out .= self::build_html_attributes($html_options);
		$out .= ">";
		
		if ($options['include_blank'] !== null) {
			$out .= "<option value=\"\">{$options['include_blank']}</option>";
		}
		
		foreach ($choices as $value => $name) {
			$attr = array('value' => $value);
			
			if ($options['selected'] !== null && in_array($value, $options['selected'])) {
				$attr['selected'] = 'selected';
			}
			
			$out .= self::build_tag('option', $attr, $name);
		}
		
		$out .= "</select>";
		
		return $out;
	}
	
	public static function select_for_model($field, $collection, $name_field, $value_field = 'id', $options = array(), $html_options = array())
	{
		$object = self::get_object();
		$fieldname = self::get_field($field);
		$normal = self::get_normalized_field($field);
		
		$value = $object->$normal;
		
		if (is_array($value)) {
			$options['selected'] = array();
			$foreign = isset($options['foreign_value']) ? $options['foreign_value'] : 'id';
			
			foreach ($object->$normal as $row) {
				$options['selected'][] = $row->$foreign;
			}
		} else {
			$options['selected'] = $object->$normal;
		}
		
		$choices = array();
		
		foreach ($collection as $item) {
			$choices[$item->$value_field] = $item->$name_field;
		}
		
		return self::select($field, $choices, $options, $html_options);
	}
	
	public static function checkbox_tree($field, $roots, $options = array(), $html_options = array())
	{
		if (count($roots) == 0) {
			return '';
		}
		
		$object = self::get_object();
		$fieldname = self::get_field($field);
		$normal = self::get_normalized_field($field);
		
		$options = array_merge(array(
			'value_field' => 'id',
			'label_field' => 'name',
			'selected' => array()
		), $options);
		
		$html_options = array_merge(array(
			'type' => 'checkbox',
			'name' => $fieldname
		), $html_options);
		
		$content = '';
		
		foreach ($roots as $item) {
			$html_options['value'] = $item->$options['value_field'];
			
			if (in_array($item->$options['value_field'], $options['selected'])) {
				$html_options['checked'] = 'checked';
			} else {
				unset($html_options['checked']);
			}
			
			$tag  = self::build_tag('input', $html_options);
			$tag .= $item->$options['label_field'];
			$tag .= self::checkbox_tree($field, $item->childs, $options, $html_options);
			
			$content .= self::build_tag('li', array(), $tag);
		}
		
		return self::build_tag('ul', array(), $content);
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
	
	private static function get_normalized_field($fieldname)
	{
		return preg_replace('/[^a-z0-9_-]/i', '', $fieldname);
	}
	
	private static function get_field($fieldname)
	{
		$object = self::get_object();
		
		if (is_a($object, 'BlankObject')) {
			return $fieldname;
		} else {
			$filtred_name = self::get_normalized_field($fieldname);
			
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
		$fieldname = self::get_normalized_field($fieldname);
		
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
	
	private static function build_tag($tagname, $attributes = array(), $content = false, $force_open = false)
	{
		$attributes = self::build_html_attributes($attributes);
		
		if ($force_open) {
			return "<$tagname $attributes>";
		}
		
		if ($content !== false) {
			return "<$tagname $attributes>$content</$tagname>";
		} else {
			return "<$tagname $attributes />";
		}
	}
}