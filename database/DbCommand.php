<?php

/**
 * Classe simples para acesso a dados
 *
 * @package DB
 * @author Wilker
 */
class DbCommand
{
    private static $DB_HOST = "127.0.0.1";
    private static $DB_USER = "root";
    private static $DB_PASSWORD = "";
    private static $DB_DATABASE = "";
    
    protected static $connection = null;
    
    /**
     * undocumented function
     *
     * @return void
     * @author Wilker
     **/
    public static function configure($host, $user, $password, $database)
    {
        self::$DB_HOST = $host;
        self::$DB_USER = $user;
        self::$DB_PASSWORD = $password;
        self::$DB_DATABASE = $database;
    }
    
    /**
     * Conectar ao banco de dados
     *
     * @return void
     * @author Wilker
     */
    protected static function connect()
    {
        if(self::$connection !== null) {
            return;
        }
        
        self::$connection = @mysql_connect(self::$DB_HOST, self::$DB_USER, self::$DB_PASSWORD);
        
        if (!(self::$connection)) {
            throw new DbConnectionException("Unable to connect to database");
        }
        
        if (!@mysql_select_db(self::$DB_DATABASE, self::$connection)) {
            throw new DbConnectionException("Unable to select " . self::$DB_DATABASE . " database");
        }
    }
    
    /**
     * Verificar se existe conex&atilde;o, conectar caso n&atilde;o exista
     *
     * @return void
     * @author Wilker
     */
    protected static function check_connection()
    {
        if(self::$connection === null) {
            self::connect();
        }
    }
    
    /**
     * Efetuar comando SQL
     *
     * @param $sql Consulta SQL
     * @return integer
     * @author Wilker
     */
    protected static function query($sql)
    {
        self::check_connection();
        
        $result = @mysql_query($sql, self::$connection);
        
        if (!$result) {
            throw new InvalidQueryException("Error executing query: " . mysql_error(self::$connection) . " at " . $sql);
        }
        
        return $result;
    }
    
    /**
     * Executar comando SQL no banco
     *
     * @param $sql Consulta SQL
     * @return integer
     * @author Wilker
     */
    public static function execute($sql)
    {
        self::query($sql);
        
        return mysql_affected_rows(self::$connection);
    }
    
    /**
     * Executa uma query e retorna a primeira linha de resultado
     *
     * @param $sql Consulta SQL
     * @return array
     * @author Wilker
     */
    public static function row($sql)
    {
        $result = self::query($sql);
        
        return mysql_fetch_assoc($result);
    }
    
    /**
     * Executa a query e retorna a primeira c&eacute;lula do primeiro resultado
     *
     * @param $sql Consulta SQL
     * @return string
     * @author Wilker
     */
    public static function cell($sql)
    {
        $result = self::query($sql);
        
        return mysql_result($result, 0, 0);
    }
    
    /**
     * Le todos os resultados de uma consulta
     *
     * @param $sql Consulta SQL
     * @return array
     * @author Wilker
     */
    public static function all($sql)
    {
        $result = self::query($sql);
        $data = array();
        
        while($row = mysql_fetch_assoc($result)) {
            $data[] = $row;
        }
        
        return $data;
    }
    
    /**
     * Retorna a defini&ccedil;&atilde;o de uma tabela
     *
     * @param $table Table name
     * @return array
     * @author Wilker
     */
    public static function table_fields($table)
    {
        $data = self::all("DESCRIBE `$table`");
        $fields = array();
        
        foreach ($data as $field) {
            $fields[] = $field['Field'];
        }
        
        return $fields;
    }
    
    /**
     * Ler o &uacute;ltimo ID inserido na base de dados
     *
     * @return integer
     * @author wilker
     */
    public static function insert_id()
    {
        return mysql_insert_id();
    }
} // END class DbCommand

class DbConnectionException extends Exception {}
class InvalidQueryException extends Exception {}
