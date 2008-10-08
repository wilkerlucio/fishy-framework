<?php

/**
 * Pagination class to work with ActiveRecord
 *
 * This classe provides a easy way to create pagination using ActiveRecord
 * as data layer of application
 *
 * @version 1.0.2
 * @author Wilker Lucio <wilkerlucio@gmail.com>
 */
class Fishy_ArPagination
{
	private $model;
	private $query;
	private $per_page;
	private $config;
	
	/**
	 * Creates a new pagination object
	 *
	 * @param $model A string containg the name of model to be used or the model itself (can use named scopes)
	 * @param $per_page The number of records per pege to display
	 * @param $query Query data to be passed when quering ActiveRecord
	 * @return ArPagination
	 */
	public function __construct($model, $per_page = 10, $query = array())
	{
		$this->model = is_string($model) ? ActiveRecord::model($model) : $model;
		$this->query = $query;
		$this->per_page = $per_page;
		
		$this->config = $this->default_config();
	}

	private function default_config()
	{
		return array(
			'cur_page'        => 1,
			'num_links'       => 5,
			'base_url'        => '',
			
			'full_tag_open'   => '<p>',
			'full_tag_close'  => '</p>',
			
			'first_link'      => '&laquo;',
			'first_tag_open'  => ' ',
			'first_tag_close' => ' ',
			
			'last_link'       => '&raquo;',
			'last_tag_open'   => ' ',
			'last_tag_close'  => ' ',
			
			'next_link'       => '&gt;',
			'next_tag_open'   => ' ',
			'next_tag_close'  => ' ',
			
			'prev_link'       => '&lt;',
			'prev_tag_open'   => ' ',
			'prev_tag_close'  => ' ',
			
			'first_inactive_link'      => '',
			'first_inactive_tag_open'  => '',
			'first_inactive_tag_close' => '',
			
			'last_inactive_link'       => '',
			'last_inactive_tag_open'   => '',
			'last_inactive_tag_close'  => '',
			
			'next_inactive_link'       => '',
			'next_inactive_tag_open'   => '',
			'next_inactive_tag_close'  => '',
			
			'prev_inactive_link'       => '',
			'prev_inactive_tag_open'   => '',
			'prev_inactive_tag_close'  => '',
			
			'cur_tag_open'   => '<b>',
			'cur_tag_close'  => '</b>',
			
			'num_tag_open'   => ' ',
			'num_tag_close'  => ' ',
			
			'num_separator'  => ' ',
		);
	}

	private function make_link($page, $text)
	{
		$link = "<a href=\"{$this->config['base_url']}{$page}\">";
		$link .= $text;
		$link .= "</a>";
		
		return $link;
	}

	private function make_wrap($page, $prefix, $link = null)
	{
		$wrapper = $this->config[$prefix . '_tag_open'];
		$wrapper .= $this->make_link($page, $link ? $link : $this->config[$prefix . '_link']);
		$wrapper .= $this->config[$prefix . '_tag_close'];
		
		return $wrapper;
	}
	
	private function make_wrap_wl($page, $prefix, $link = null) {
		$wrapper = $this->config[$prefix . '_tag_open'];
		$wrapper .= $link ? $link : $this->config[$prefix . '_link'];
		$wrapper .= $this->config[$prefix . '_tag_close'];
		
		return $wrapper;
	}

	/**
	 * Set a configuration option
	 * You can pass one associative array to set many options at once
	 *
	 * @param $data A string containg the name of configuration to change (or one
	 *	associative array to set many options at once)
	 * @param $value The value of option (if you passed a string at first argument)
	 * @return void
	 */
	public function set_config($data, $value = null)
	{
		if (is_array($data) && $value === null) {
			$this->config = array_merge($this->config, $data);
		} else {
			$this->config[$data] = $value;
		}
	}

	/**
	 * Get the total number of records
	 *
	 * @return integer The number of records to be paginated
	 */
	public function get_total()
	{
		return $this->model->count($this->query);
	}
	
	/**
	 * Get current page (normalized)
	 *
	 * @return integer
	 */
	public function get_cur_page()
	{
		$total = $this->get_total();
		$pages = ceil($total / $this->per_page);
		$page = $this->config['cur_page'];
		
		if ($page < 1) {
			$page = 1;
		} elseif ($page > $pages) {
			$page = $pages;
		}
		
		return $page;
	}

	/**
	 * Get data of current page
	 *
	 * @return array Array containg data of current page
	 */
	public function data()
	{
		$query = $this->query;
		$query['limit'] = $this->per_page;
		$query['offset'] = ($this->get_cur_page() - 1) * $this->per_page;
		
		return $this->model->all($query);
	}

	/**
	 * Generate and return the links to navigation
	 *
	 * @return string Navigation links
	 */
	public function create_links()
	{
		$total = $this->get_total();
		$pages = ceil($total / $this->per_page);
		$cur_page = $this->get_cur_page();
		
		$page_range = ($this->config['num_links'] - 1) / 2;
		
		$page_start = $cur_page - ceil($page_range);
		$page_end = $cur_page + floor($page_range);
		
		if ($page_start < 1) {
			$page_end += 1 - $page_start;
			$page_start = 1;
		}
		
		if ($page_end > $pages) {
			$page_start = max(1, $page_start - ($page_end - $pages));
			$page_end = $pages;
		}
		
		$links = '';
		
		$links .= $this->config['full_tag_open'];
		
		if ($cur_page > 1) {
			if ($this->config['first_link']) $links .= $this->make_wrap(1, 'first');
			if ($this->config['prev_link']) $links .= $this->make_wrap($cur_page - 1, 'prev');
		} else {
			if ($this->config['first_inactive_link']) $links .= $this->make_wrap_wl(1, 'first_inactive');
			if ($this->config['prev_inactive_link']) $links .= $this->make_wrap_wl($cur_page - 1, 'prev_inactive');
		}
		
		for ($i = $page_start; $i <= $page_end; $i++) {
			if ($i != $cur_page) {
				$links .= $this->make_wrap($i, 'num', $i);
			} else {
				$links .= $this->make_wrap_wl($i, 'cur', $i);
			}
			
			$links .= $i < $page_end ? $this->config['num_separator'] : '';
		}
		
		if ($cur_page < $pages) {
			if ($this->config['next_link']) $links .= $this->make_wrap($cur_page + 1, 'next');
			if ($this->config['last_link']) $links .= $this->make_wrap($pages, 'last');
		} else {
			if ($this->config['next_inactive_link']) $links .= $this->make_wrap_wl($cur_page + 1, 'next_inactive');
			if ($this->config['last_inactive_link']) $links .= $this->make_wrap_wl($pages, 'last_inactive');
		}
		
		$links .= $this->config['full_tag_close'];
		
		return $links;
	}
}
