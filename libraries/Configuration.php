<?php

class Fishy_Configuration
{
	private $data;
	
	public function __construct()
	{
		$this->data = array();
	}
	
	public function get($propertie, $default = null)
	{
		return isset($this->data[$propertie]) ? $this->data[$propertie] : $default;
	}
	
	public function get_data()
	{
		return $this->data;
	}
	
	public function __get($propertie)
	{
		return $this->get($propertie);
	}
	
	public function __set($propertie, $value)
	{
		$this->data[$propertie] = $value;
	}
}
