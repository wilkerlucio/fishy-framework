<?php

class ClassNotFoundException extends Exception
{
	private $classname;
	
	public function __construct($classname)
	{
		$this->set_classname($classname);
		
		parent::__construct("Class $classname not found.");
	}
	
	public function get_classname()
	{
		return $this->classname;
	}
	
	public function set_classname($classname)
	{
		$this->classname = $classname;
	}
}
