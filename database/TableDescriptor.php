<?php

require_once(dirname(__FILE__) . '/DbCommand.php');

/**
 * Cache description of tables
 *
 * @package DB
 * @author Wilker
 **/
class TableDescriptor
{
    private static $instance;
    
    protected $tables;
    
    /**
     * Create TableDescriptor instance
     *
     * @return TableDescriptor
     * @author Wilker
     **/
    private function __construct()
    {
        $this->tables = array();
    }
    
    /**
     * Get TableDescriptor Singleton instance
     *
     * @return TableDescriptor
     * @author Wilker
     **/
    public static function get_instance()
    {
        if (!self::$instance) {
            self::$instance = new TableDescriptor();
        }
        
        return self::$instance;
    }
    
    /**
     * Get definition of specifique table
     *
     * @param $table The name of table
     * @return array
     * @author Wilker
     **/
    public function __get($table)
    {
        if (!isset($this->tables[$table])) {
            $this->tables[$table] = DbCommand::table_fields($table);
        }
        
        return $this->tables[$table];
    }
} // END class TableDescriptor
