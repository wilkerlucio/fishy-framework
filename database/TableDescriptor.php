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
