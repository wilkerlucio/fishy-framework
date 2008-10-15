<?php

class Fishy_FeedSpec
{
	public $title;
	public $website;
	public $description;
	public $language;
	public $copyright;
	public $last_change;
	public $generator;
	public $encoding;
	
	protected $entries;
	
	public function __construct()
	{
		$this->website = "http://{$_SERVER['HTTP_HOST']}/";
		$this->encoding = 'utf-8';
		$this->generator = 'Fishy Framework';
		$this->entries = array();
	}
	
	public function add_entry(Fishy_FeedSpecEntry $entry)
	{
		$this->entries[] = $entry;
	}
	
	public function get_entries()
	{
		return $this->entries;
	}
}
