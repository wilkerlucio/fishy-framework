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
