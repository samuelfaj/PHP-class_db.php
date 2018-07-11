<?php
/**
 * class_db - PHP's Facilitation Class for Single and Multiple Databases.
 *
 * @category  Database Access
 * @package   class_db
 * @author    Samuel Fajreldines <samuelfaj@icloud.com>
 * @copyright Copyright (c) 2010-2018
 * @license   MIT
 * @version   3.0.0
 */

namespace ClassDb;

class Database
{
    protected $connection = array(
        'host'     => 'localhost' ,  // string - Host of Connection.
        'user'     => 'root'      ,  // string - Connection Username.
        'password' => ''          ,  // string - Connection Password.
        'database' => 'test'      ,  // string - Default Database name.
        'db_type'  => 'mysqli'    ,  // string - Type of Database. It can be: 'mysql', 'mysqli' , 'mssql' , 'sqlserv' , 'pgsql'.
    );

    public $debug_mode       = false;
    public $auto_disconnect  = true;

    protected $info = array('start_date' => '', 'end_date' => '');
    protected $db_connection;

    /**
     * Starts the object.
     * @param array $connection
     *  -> You can set a whole new connection setting a new array following the "$this->connection" pattern
     *  -> or set just a new item like array('database' => 'new database') to update only the default database name.
     * @param boolean $debug_mode      It sets the "Debug Mode" which will show some logs.
     * @param boolean $auto_connect    If true, calls "$this->connect()" function automatically.
     * @param boolean $auto_disconnect If true, calls the the selected database type "close" function when object gets destructed.
     */
    public function __construct(
        array $connection       = array() ,
        bool  $debug_mode       = false   ,
        bool  $auto_connect     = true    ,
        bool  $auto_disconnect  = false
    ){
        $this->debug_mode         = $debug_mode;
        $this->auto_disconnect    = $auto_disconnect;
        $this->info['start_date'] = date('Y-m-d H:i:s');

        foreach($connection as $key => $value)
        {
            if(in_array($key, array_keys($this->connection)))
            {
                $this->connection[$key] = $value;
            }
        }


        if($auto_disconnect)
        {
            $this->connect();
        }
    }

    /**
     * Called in the destruction of the object.
     */
    public function __destruct()
    {
        if($this->auto_disconnect) $this->disconnect();
        $this->info['end_date'] = date('Y-m-d H:i:s');
    }

    /**
     * Connects with the server and builds the "$this->obj_db_connection" object.
     */
    public function connect(){
        switch ($this->connection['db_type'])
        {
            case 'mysql':
                $this->db_connection = mysql_connect(
                    $this->connection['host']      ,
                    $this->connection['user']      ,
                    $this->connection['password']
                );

                mysql_select_db($this->connection['database'], $this->db_connection);
                break;
            case 'mysqli':
                $this->db_connection = new \MySQLi(
                    $this->connection['host']      ,
                    $this->connection['user']      ,
                    $this->connection['password']  ,
                    $this->connection['database']
                );
                break;
            case 'mssql':
                $this->db_connection = mssql_connect(
                    $this->connection['host']      ,
                    $this->connection['user']      ,
                    $this->connection['password']
                );

                mssql_select_db($this->connection['database'], $this->db_connection);
                break;
            case 'sqlserv':
                $this->db_connection = sqlsrv_connect(
                    $this->connection['host'],
                    array(
                        "Database" => $this->connection['database']  ,
                        'UID'      => $this->connection['user']      ,
                        'PWD'      => $this->connection['password']
                    )
                );
                break;
            case 'pgsql':
                $this->db_connection = pg_connect(
                    "host="     .  $this->connection['host']      .
                    "dbname="   .  $this->connection['database']  .
                    "user="     .  $this->connection['user']      .
                    "password=" .  $this->connection['password']
                );
                break;
        }
    }

    /**
     * Disconnects from the server.
     */
    public function disconnect(){
        switch ($this->connection['db_type'])
        {
            case 'mysql'   : return mysql_close();
            case 'mysqli'  : return $this->db_connection->close();
            case 'mssql'   : return mssql_close($this->db_connection);
            case 'sqlserv' : return sqlsrv_close($this->db_connection);
            case 'pgsql'   : return pg_close($this->db_connection);
        }
    }

    /**
     * @return Database's last error.
     */
    public function last_error(){
        switch ($this->connection['db_type'])
        {
            case 'mysql'   :  return mysql_error();
            case 'mysqli'  :  return $this->db_connection->error;
            case 'mssql'   :  return mssql_get_last_message();
            case 'pgsql'   :  return pg_last_error($this->db_connection);
            case 'sqlserv' :  return sqlsrv_errors();
            default        :  return '';
        }
    }

    /**
     * Returns the elapsed time between the construction of the object and the moment when was called.
     * @return int The elapsed time between the construction of the object and the moment when was called.
     */
    public function elapsed_time() : int
    {
        return (strtotime(date('Y-m-d H:i:s')) - strtotime($this->info['start_date']));
    }
}