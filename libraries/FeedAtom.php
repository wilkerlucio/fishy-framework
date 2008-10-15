<?php

class Fishy_FeedAtom
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
		$output->appendln('<feed xmlns="http://www.w3.org/2005/Atom">');
		$output->increase_indent();
		
		$output->appendln("<id>{$this->data->website}</id>");
		$output->appendln("<title>{$this->data->title}</title>");
		$output->appendln("<subtitle>{$this->data->description}</subtitle>");
		$output->appendln("<updated>{$this->data->last_change}</updated>");
		
		if ($this->data->copyright) $output->appendln("<rights>{$this->data->copyright}</rights>");
		if ($this->data->generator) $output->appendln("<generator>{$this->data->generator}</generator>");
		
		foreach ($this->data->get_entries() as $entry) {
			$output->appendln('<entry>');
			$output->increase_indent();
			
			$output->appendln("<id>{$entry->permalink}</id>");
			$output->appendln("<title>{$entry->title}</title>");
			$output->appendln("<updated>{$entry->updated}</updated>");
			if ($entry->author) $output->appendln("<author>{$entry->author}</author>");
			if ($entry->content) $output->appendln("<content type=\"xhtml\"><div xmlns=\"http://www.w3.org/1999/xhtml\">{$entry->content}</div></content>");
			
			$output->decrease_indent();
			$output->appendln('</entry>');
		}
		
		$output->decrease_indent();
		$output->appendln('</feed>');
		
		return $output->get_data();
	}
}
