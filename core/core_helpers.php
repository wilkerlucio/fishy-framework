<?php

/**
 * This function get the base URL of site
 *
 * @param $sulfix Sulfix to be append at end of file
 * @return string The final path
 */
function base_url($sulfix = '')
{
	return FISHY_BASE_URL . $sulfix;
}

/**
 * This function get one internal url of site
 *
 * @param $sulfix Sulfix to be append at end of file
 * @return string The final path
 */
function site_url($sulfix = '')
{
	return FISHY_BASE_URL . FISHY_INDEX_PAGE . '/' . $sulfix;
}

/**
 * This function get one public url of site
 *
 * @param $sulfix Sulfix to be append at end of file
 * @return string The final path
 */
function public_url($sulfix = '')
{
	return FISHY_PUBLIC_PATH . '/' . $sulfix;
}

/**
 * Simple class that doesn't anything
 */
class BlankObject
{
	public function __get($propertie) { return ''; }
	public function __set($propertie, $value) {}
	public function __call($method, $arguments) {}
}