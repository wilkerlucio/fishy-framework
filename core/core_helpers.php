<?php

/**
 * Simple class that doesn't anything
 */
class BlankObject
{
	public function __get($propertie) { return null; }
	public function __set($propertie, $value) {}
	public function __call($method, $arguments) {}
}