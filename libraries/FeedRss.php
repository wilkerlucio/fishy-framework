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

class Fishy_FeedRss
{
	private $data;
	
	public function __construct(Fishy_FeedSpec $spec)
	{
		$this->data = $spec;
	}
	
	public function generate()
	{
		$output = new Fishy_StringBuilder();
		
		$output->appendln('<?xml version="1.0" encoding="' . $this->data->encoding . '" ?>');
		$output->appendln('<rss version="2.0">');
		$output->increase_indent();
		$output->appendln('<channel>');
		$output->increase_indent();
		
		$output->appendln("<title>{$this->data->title}</title>");
		$output->appendln("<link>{$this->data->website}</link>");
		$output->appendln("<description>{$this->data->description}</description>");
		
		if ($this->data->language) $output->appendln("<language>{$this->data->language}</language>");
		if ($this->data->copyright) $output->appendln("<copyright>{$this->data->copyright}</copyright>");
		if ($this->data->last_change) $output->appendln("<lastBuildDate>{$this->data->last_change}</lastBuildDate>");
		if ($this->data->generator) $output->appendln("<generator>{$this->data->generator}</generator>");
		
		foreach ($this->data->get_entries() as $entry) {
			$output->appendln('<item>');
			$output->increase_indent();
			
			if ($entry->title) $output->appendln("<title>{$entry->title}</title>");
			if ($entry->permalink) $output->appendln("<link>{$entry->permalink}</link>");
			if ($entry->author) $output->appendln("<author>{$entry->author}</author>");
			if ($entry->content) $output->appendln("<description><![CDATA[{$entry->content}]]></description>");
			
			$output->decrease_indent();
			$output->appendln('</item>');
		}
		
		$output->decrease_indent();
		$output->appendln('</channel>');
		$output->decrease_indent();
		$output->appendln('</rss>');
		
		return $output->get_data();
	}
}
