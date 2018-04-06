<?php
/**
 * class_db - PHP's Facilitation Class for Single and Multiple Databases.
 *
 * @category  Database Access
 * @package   class_db
 * @author    Samuel Fajreldines <samuelfaj@icloud.com>
 * @copyright Copyright (c) 2010-2018
 * @license   MIT
 * @version   2.1.0
 */

namespace ClassDb;

class Db{
	public $default_connection = array(
		'host'     => 'localhost' ,  // string - Host of Connection.
		'user'     => 'root'      ,  // string - Connection Username.
		'password' => ''          ,  // string - Connection Password.
		'database' => 'test'      ,  // string - Default Database name.
		'db_type'  => 'mysqli'    ,  // string - Type of Database. It can be: 'mysql', 'mysqli' , 'mssql' , 'sqlserv' , 'pgsql'.
	);

	public $html_prints = true;   // boolean - If true uses HTML in class prints and logs (like errors or debug mode logs).
	public $connection  = array();

	/**
	 * Starts the object.
	 * @param array $connection 
	 *  -> You can set a whole new connection setting a new array following the "$this->default_connection" pattern
	 *  -> or set just a new item like array('database' => 'new database') to update only the default database name.
	 * @param boolean $debugmod It sets the "Debug Mode" which will show some logs.
	 * @param boolean $connect_automatically  If true, calls "$this->connect()" function automatically.
	 * @param boolean $disconnect_at_destruct If true, calls the the selected database type "close" function when object gets destructed.
	 */
	public function __construct($connection = array(),$debugmode = false, $connect_automatically = true, $disconnect_at_destruct = false){
		$this->debugmode = $debugmode;
		$this->disconnect_at_destruct = $disconnect_at_destruct;

		$connection_fields = array('host','user','password','database','db_type');
		foreach($connection as $key=>$value){
			if(in_array($key,$connection_fields)){ $this->default_connection[$key] = $value; }
		}

		$this->info['start_date'] = date('Y-m-d H:i:s');

		$this->connection = $this->default_connection;
		if($connect_automatically){ $this->connect(); }
	}
	
	/**
	 * Called in the destruction of the object.
	 * @return number Elapsed time between the construction and destruction of the object.
	 */
	public function __destruct(){
		if($this->disconnect_at_destruct){
			$this->disconnect();
		}

		$this->info['end_date'] = date('Y-m-d H:i:s');
		return $this->elapsed_time();
	}

	/**
	 * Connects with the server and builds the "$this->obj_db_connection" object.
	 */
	public function connect(){
		switch ($this->connection['db_type']) {
			case 'mysql':
				$this->obj_db_connection = mysql_connect(
					$this->connection['host']      ,
					$this->connection['user']      ,
					$this->connection['password'] 
				) or trigger_error($this->print_formatter( mysql_error() ),E_USER_ERROR);

				mysql_select_db($this->connection['database'], $this->obj_db_connection);
			break;
			case 'mysqli':
				$this->obj_db_connection = new \MySQLi(
					$this->connection['host']      ,
					$this->connection['user']      ,
					$this->connection['password']  ,
					$this->connection['database']
				) or trigger_error($this->print_formatter( $this->obj_db_connection->connect_error ),E_USER_ERROR);
			break;
			case 'mssql':
				$this->obj_db_connection = mssql_connect(
					$this->connection['host']      ,
					$this->connection['user']      ,
					$this->connection['password']
				) or trigger_error($this->print_formatter( mssql_get_last_message() ),E_USER_ERROR);

				mssql_select_db($this->connection['database'], $this->obj_db_connection);
			break;
			case 'sqlserv':
				$this->obj_db_connection = sqlsrv_connect(
					$this->connection['host'],
					array(
						"Database" => $this->connection['database']  ,
						'UID'      => $this->connection['user']      ,
						'PWD'      => $this->connection['password']
					)
				) or trigger_error($this->print_formatter( sqlsrv_errors() ),E_USER_ERROR);
			break;
			case 'pgsql':
				$this->obj_db_connection = pg_connect(
					"host="     .  $this->connection['host']      .
					"dbname="   .  $this->connection['database']  .
					"user="     .  $this->connection['user']      .
					"password=" .  $this->connection['password']
				) or trigger_error($this->print_formatter( pg_last_error() ),E_USER_ERROR);
			break;
		}
	}
	
	/**
	 * Disconnects from the server.
	 */
	public function disconnect(){
		switch ($this->connection['db_type']) {
			case 'mysql':
				mysql_close();
			break;
			case 'mysqli':
				$this->obj_db_connection->close();
			break;
			case 'mssql':
				mssql_close($this->obj_db_connection);
			break;
			case 'sqlserv':
				sqlsrv_close($this->obj_db_connection);
			break;
			case 'pgsql':
				pg_close($this->obj_db_connection);
			break;
		}
	}
	
	/**
	 * Returns database's last error.
	 * @return string Database's last error (using "$this->print_formatter" to format).
	 */
	public function last_error(){
		switch ($this->obj_db_connection['db_type']) {
			case 'mysql': 
				return $this->print_formatter(mysql_error());
			break;
			case 'mysqli':
				return $this->print_formatter($this->obj_db_connection->error);
			break;
			case 'mssql':
				return $this->print_formatter(mssql_get_last_message());
			break;
			case 'pgsql':
				return $this->print_formatter(pg_last_error($this->obj_db_connection));
			break;
			case 'sqlserv':
				return $this->print_formatter(sqlsrv_errors());
			break;
		}

		return '';
	}
	
	/**
	 * Returns the elapsed time between the construction of the object and the moment when was called.
	 * @return number The elapsed time between the construction of the object and the moment when was called.
	 */
	public function elapsed_time(){
		return (strtotime(date('Y-m-d H:i:s')) - strtotime($this->info['start_date']));
	}

	/**
	 * Returns formatted print as configurated in "$this->html_prints".
	 * @return string
	 */
	public function print_formatter($string){
		if($this->html_prints){
			return '<p>PHP Class DB: </p> <hr> <pre>'.var_export($string,true).'</pre>';
		}else{
			return var_export($string,true);
		}
	}
}