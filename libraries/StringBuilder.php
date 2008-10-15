<?php

class Fishy_StringBuilder
{
	private $data;
	
	private $indent_level;
	private $indent_string;
	private $indent_cache;
	private $newline_string;
	
	public function __construct()
	{
		$this->data = '';
		
		$this->indent_level = 0;
		$this->indent_string = "\t";
		$this->indent_cache = '';
		$this->newline_string = "\n";
	}
	
	public function append($string)
	{
		$this->data .= $this->indent_cache . $string;
	}
	
	public function appendln($string)
	{
		$this->append($string . $this->newline_string);
	}
	
	public function increase_indent($level = 1)
	{
		$this->indent_level += $level;
		$this->refresh_ident_cache();
	}
	
	public function decrease_indent($level = 1)
	{
		$this->indent_level -= $level;
		$this->refresh_ident_cache();
	}
	
	private function refresh_ident_cache()
	{
		$this->indent_cache = str_repeat($this->indent_string, $this->indent_level);
	}
	
	public function get_data()
	{
		return $this->data;
	}
}
