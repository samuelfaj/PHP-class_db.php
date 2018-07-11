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

class Query extends Db{
    public $orders = array();
    public $wheres = array();

    /**
     * Starts the object.
     * @param array or object $connection
     *  -> You can set a whole new connection passing the same parameters of __construct method from class db
     *  -> or just pass a "class db" object previously initialized.
     * @param boolean $debugmod It sets the "Debug Mode" which will show some logs.
     * @param boolean $connect_automatically If true, calls "$this->connect()" function automatically.
     * @param boolean $disconnect_at_destruct If true, calls the "close" function of the selected database type when object gets destructed.
     * ----------------------------------------------------------------------------------------
     * EXAMPLES OF USE:
     * ----------------------------------------------------------------------------------------
     *  $db = new db(array(
     *		'host'     => 'localhost'      ,
     *		'user'     => 'username'       ,
     *		'password' => 'mysecretpass'   ,
     *		'database' => 'myapplication'  ,
     *		'db_type'  => 'mysql'          ,
     *  ));
     *
     *  $sql = new query($db);
     * ----------------------------------------------------------------------------------------
     * 	$sql = new query(array(
     *		'host'     => 'localhost'      ,
     *		'user'     => 'username'       ,
     *		'password' => 'mysecretpass'   ,
     *		'database' => 'myapplication'  ,
     *		'db_type'  => 'mysql'          ,
     *	));
     * ----------------------------------------------------------------------------------------
     */
    public function __construct($connection = array(), $debugmode = false, $connect_automatically = true, $disconnect_at_destruct = false){
        if(is_object($connection)){
            foreach((array) $connection as $k=>$v){ $this->$k = $v; }
        }else{
            parent::__construct($connection, $debugmode, $connect_automatically, $disconnect_at_destruct);
        }
    }

    /**
     * Executes a new SQL query.
     * @param string $sql (optional) SQL query to execute. If empty it'll use $this->sql.
     * @param string $result_comparison_signal Comparison signal to verify if query was successfully executed.
     *  -> NOTE: See more on function result() documentation.
     * @param string $fetch_all_rows
     *  -> If true  returns fetch parameter as an array with all results gotten.
     *  -> If false returns fetch parameter as the fetch_assoc correspondent method.
     * @return object - NOTE: After the execution, this return can be get as $this->query too.
     *  -> 'sql'			#  string   -  The sent SQL query.
     *  -> 'fetch'		    #  array    -  Fetchs a result row as an array.
     *  -> 'result'		    #  boolean  -  Returns true if num_rows >= 0 OR affected_rows >= 0.
     *  -> 'object'		    #  object   -  Returns the configured database query object.
     *  -> 'num_rows'		#  int      -  Returns the gotten number of rows in result.
     *  -> 'have_rows'	    #  boolean  -  Returns true if the number of rows in result is higher than 0.
     *  -> 'affected_rows'  #  int 	    -  Returns the gotten number of affected rows in result.
     *  -> 'elapsed_time'   #  int 	    -  Elapsed time between start and finish of query.
     */
    public function exec(string $sql = '', string $result_comparison_signal = '>', bool $fetch_all_rows = true) : object
    {
        if(empty($sql)){ $sql = $this->sql; } else { $this->sql = $sql; }
        if(empty($sql)) return false;

        $this->info['start_date'] = date('Y-m-d H:i:s');

        if ($this->debugmode){ echo $this->print_formatter($sql); }

        switch ($this->connection['db_type']) {
            case 'mysql':
                $this->obj_query = mysql_query($sql,$this->obj_db_connection)
                or trigger_error($this->print_formatter(mysql_error()),E_USER_ERROR);
                break;
            case 'mysqli':
                $this->obj_query = $this->obj_db_connection->query($sql)
                or trigger_error($this->print_formatter($this->obj_db_connection->error), E_USER_ERROR);
                break;
            case 'mssql':
                $this->obj_query = mssql_query($sql,$this->obj_db_connection)
                or trigger_error($this->print_formatter(mssql_get_last_message()),E_USER_ERROR);
                break;
            case 'pgsql':
                $this->obj_query = pg_query($sql,$this->obj_db_connection)
                or trigger_error($this->print_formatter(pg_last_error($this->obj_db_connection)), E_USER_ERROR);
                break;
            case 'sqlserv':
                $this->obj_query = sqlsrv_query($this->obj_db_connection,$sql,array(),array("Scrollable" => "buffered"))
                or trigger_error($this->print_formatter(print_r(sqlsrv_errors())),E_USER_ERROR);
                break;
        }

        return $this->query = ((object) array(
            'sql'    => $this->sql,
            'fetch'  => $this->fetch($fetch_all_rows),
            'result' => $this->result($result_comparison_signal),
            'object' => $this->obj_query,
            'num_rows'  => $this->num_rows(),
            'have_rows' => $this->have_rows(),
            'affected_rows' => $this->affected_rows(),
            'elapsed_time'  => (strtotime(date('Y-m-d H:i:s')) - strtotime($this->info['start_date']))
        ));
    }

    /**
     * Executes a new SELECT query.
     * @param array $fields (optional) fields to get. If empty will use wildcard.
     *  -> Example: array('id','register_date') or array('*')
     * @param string $result_comparison_signal Comparison signal to verify if query was successfully executed.
     *  -> NOTE: See more on function result() documentation.
     * @param string $fetch_all_rows
     *  -> If true  returns fetch parameter as an array with all results gotten.
     *  -> If false returns fetch parameter as the fetch_assoc correspondent method.
     * @return object It'll mount the query and returns the execution of $this->exec() function.
     */
    public function select($fields = array('*'), $result_comparison_signal = '>=', $fetch_all_rows = true){
        $this->sql = '
			SELECT '.implode($this->r_addslashes($fields)).' 
			FROM '.$this->table.' '.
            $this->mount_where();

        if(!empty($this->group_by)){ $this->sql .= ' GROUP BY '.$this->group_by; }

        $this->sql .= $this->mount_order_by();

        if(!empty($this->limit)){ $this->sql .= ' LIMIT '.$this->limit; }

        return $this->exec(null,$result_comparison_signal, $fetch_all_rows);
    }

    /**
     * Executes a new INSERT query.
     * @param string $insert Fields and values to insert.
     *  -> Example: array('id' => 1,'register_date' => date('Y-m-d H:i:s'))
     * @param boolean $addslashes If true it'll quote strings with slashes on $insert values.
     * @param boolean $literal If false it'll add, IN THE QUERY, quotes before and after the values.
     * @param string $result_comparison_signal Comparison signal to verify if query was successfully executed.
     *  -> NOTE: See more on function result() documentation.
     * @return object It'll mount the query and returns the execution of $this->exec() function.
     */
    public function insert($insert, $addslashes = true, $literal = false, $result_comparison_signal = '>'){
        if($addslashes) $insert = $this->r_addslashes($insert);

        $this->sql = "
			INSERT INTO ".$this->table." (".implode(array_keys($insert),',').")
			VALUES ('".implode(array_values($insert),"','")."');
		";

        if($literal){
            $this->sql = "
				INSERT INTO ".$this->table." (".implode(array_keys($insert),',').")
				VALUES (".implode(array_values($insert),",").");
			";
        }

        return $this->exec(null,$result_comparison_signal, false);
    }

    /**
     * Executes a new UPDATE query.
     * @param string $update Fields and values to update.
     *  -> Example: array('name' => 'Samuel Faj','register_date' => date('Y-m-d H:i:s'))
     * @param string $safemode If true it will not execute the query if there aren't where conditions.
     * @param boolean $addslashes If true it'll quote strings with slashes on $insert values.
     * @param boolean $literal If false it'll add, IN THE QUERY, quotes before and after the values.
     * @param string $result_comparison_signal Comparison signal to verify if query was successfully executed.
     *  -> NOTE: See more on function result() documentation.
     * @return object It'll mount the query and returns the execution of $this->exec() function.
     */
    public function update($update, $safemode = true, $addslashes = true, $literal = false, $result_comparison_signal = '>'){
        $where = $this->mount_where();

        if($safemode && empty($where)) return false;
        $this->updates = ($addslashes) ? $this->r_addslashes($update) : $update;

        $this->sql = "UPDATE " . $this->table . $this->mount_update($literal) . $where;
        return $this->exec(null,$result_comparison_signal, false);
    }

    /**
     * Executes a new DELETE FROM query.
     * @param string $safemode If true it will not execute the query if there aren't where conditions.
     * @param string $result_comparison_signal Comparison signal to verify if query was successfully executed.
     *  -> NOTE: See more on function result() documentation.
     * @return object It'll mount the query and returns the execution of $this->exec() function.
     */
    public function delete($safemode = true, $result_comparison_signal = '>'){
        $where = $this->mount_where();
        if($safemode && empty($where)) return false;

        $this->sql = "DELETE FROM " . $this->table . $where;
        return $this->exec(null,$result_comparison_signal, false);
    }

    /**
     * Set the table where the querys will run.
     * @param string $table Table name.
     */
    public function table($table){
        $this->table = $table;
    }

    /**
     * Add one or more conditions to the querys.
     * @param string || array $field It can be one single field or many as array.
     * ----------------------------------------------------------------------------------------
     * IF $field IS A STRING it'll be The field name which we want to filter.
     *   @param string $value The value expected.
     *   @param string $operator The query comparison operator.
     *    -> It can be all query comparison operator like: "=","<>","like", between others.
     *   @param boolean $addslashes If true it'll quote strings with slashes on $insert values.
     *   @param boolean $literal If false it'll add, IN THE QUERY, quotes before and after the values.
     * ----------------------------------------------------------------------------------------
     * IF EACH ARGUMENT IS AN ARRAY
     *   @param string The field to filter.
     *   @param string The value expected.
     *   @param string The query comparison operator.
     *    -> It can be all query comparison operator like: "=","<>","like", between others.
     *   @param boolean If true it'll quote strings with slashes on $insert values.
     *   @param boolean If false it'll add, IN THE QUERY, quotes before and after the values.
     * ----------------------------------------------------------------------------------------
     * EXAMPLES OF USE:                              Those structures will do the exactly same.
     * ----------------------------------------------------------------------------------------
     *  $sql->where(
     *		array('id'    , $_POST['uid']),
     *		array('name'  , '%'.$_POST['name'] , 'like'),
     *		array('email' , $_POST['email']    , 'like' , false),
     *		array('NOW()' , 'DATE_ADD(register_date, INTERVAL 10 SECOND)', '<', false, true)
     *	);
     * --------------------------------------- OR ---------------------------------------------
     *  $sql->where('id'    , $_POST['uid']);
     *	$sql->where('name'  , '%'.$_POST['name'] , 'like');
     *	$sql->where('email' , $_POST['email']    , 'like' , false);
     *  $sql->where('NOW()' , 'DATE_ADD(register_date, INTERVAL 10 SECOND)', '<', false, true);
     * ----------------------------------------------------------------------------------------
     */
    public function where($field, $value, $operator = '=', $addslashes = true, $literal = false){
        if(is_array($field)){
            foreach(func_get_args() as $k=>$v){
                $field = $v[0];
                $value = $v[1];
                $operator   = (!isset($v[2])) ? '=' : $v[2];
                $addslashes = (!isset($v[3])) ? true : $v[3];
                $literal 	= (!isset($v[4])) ? true : $v[4];

                if($addslashes) $value = addslashes($value);

                $this->wheres[] = array('field' => $field, 'value' => $value, 'operator' => $operator, 'literal' => $literal);
            }
        }else{
            if($addslashes) $value = addslashes($value);

            $this->wheres[] = array('field' => $field, 'value' => $value, 'operator' => $operator, 'literal' => $literal);
        }
    }

    /**
     * Clears previously passed parameters.
     * ----------------------------------------------------------------------------------------
     * EXAMPLE OF USE:
     * ----------------------------------------------------------------------------------------
     *  $sql->where('id',1);
     *  $sql->where('name','Samuel Faj');
     *	$sql->clear();
     * ----------------------------------------------------------------------------------------
     * It will clear the previously where filters (id and name).
     * @param string $group
     */
    public function clear($parameter = 'all'){
        switch($parameter){
            case 'limit':
                $this->limit = '';
                break;
            case 'group_by':
                $this->group_by = '';
                break;
            case 'where':
            case 'wheres':
                $this->wheres = array();
                break;
            case 'order':
            case 'orders':
                $this->orders = array();
                break;
            default:
                $this->limit    = '';
                $this->group_by = '';

                $this->wheres = array();
                $this->orders = array();
                break;
        }
    }

    /**
     * Set a GROUP BY for the SELECT querys.
     * @param string $group
     */
    public function group_by($group){
        $this->group_by = $group;
    }

    /**
     * Add one or more itens to order the querys.
     * @param string || array $field It can be one single field or many as array.
     * ----------------------------------------------------------------------------------------
     * IF $field IS A STRING it'll be The field name which we want to order.
     *   @param string $order The ordenation type. Like ("ASC" and "DESC")
     * ----------------------------------------------------------------------------------------
     * IF EACH ARGUMENT IS AN ARRAY
     *   @param string The field name which we want to order.
     *   @param string The ordenation type. Like ("ASC" and "DESC")
     * ----------------------------------------------------------------------------------------
     * EXAMPLES OF USE:                              Those structures will do the exactly same.
     * ----------------------------------------------------------------------------------------
     *  $sql->order(
     *		array('id'),
     *		array('date'  , 'DESC'),
     *		array('email' , 'ASC')
     *	);
     * --------------------------------------- OR ---------------------------------------------
     *  $sql->order('id');
     *	$sql->order('date'  , 'DESC');
     *	$sql->order('email' , 'ASC');
     * ----------------------------------------------------------------------------------------
     */
    public function order($field , $order = 'ASC'){
        if(is_array($field)){
            foreach(func_get_args() as $k=>$v){
                $field = $v[0];
                $order = $v[1];

                $this->orders[] = array('field' => $field, 'order' => $order);
            }
        }else{
            $this->orders[] = array('field' => $field, 'order' => $order);
        }
    }

    /**
     * Set a limit for the SELECT querys.
     * @param int || array $limit The limit number.
     *  -> You can pass it as a int or a array.
     *  -> Example: array(0,10) in the care will be LIMIT 0,10
     */
    public function limit($limit){
        $this->limit = (is_array($limit)) ? $limit[0] . ',' . $limit[1] : $limit;
    }

    /**
     * Returns the gotten number of affected rows in result.
     * @return int
     */
    public function affected_rows(){
        $rows_number = 0;

        switch ($this->connection['db_type']) {
            case 'mysql':
                $rows_number = mysql_affected_rows();
                break;
            case 'mysqli':
                $rows_number = $this->obj_db_connection->affected_rows;
                break;
            case 'mssql':
                $rows_number = mssql_rows_affected($this->obj_db_connection);
                break;
            case 'pgsql':
                $rows_number = pg_affected_rows($this->obj_query);
                break;
            case 'sqlserv':
                $rows_number = sqlsrv_rows_affected($this->obj_query);
                break;
        }

        if ($this->debugmode){
            echo $this->print_formatter($this->sql . ' - Rows Affected:' . var_export($rows_number,true));
        }

        return $rows_number;
    }

    /**
     * Returns the gotten number of rows in result.
     * @return int
     */
    public function num_rows(){
        $rows_number = 0;

        switch ($this->connection['db_type']) {
            case 'mysql':
                if(is_resource($this->obj_query)) $rows_number = mysql_num_rows($this->obj_query);
                break;
            case 'mysqli':
                if(is_object($this->obj_query)) $rows_number = $this->obj_query->num_rows;
                break;
            case 'mssql':
                if(is_resource($this->obj_query)) $rows_number = mssql_num_rows($this->obj_query);
                break;
            case 'pgsql':
                if(is_resource($this->obj_query)) $rows_number = pg_num_rows($this->obj_query);
                break;
            case 'sqlserv':
                if(is_resource($this->obj_query)) $rows_number = sqlsrv_num_rows($this->obj_query);
                break;
        }

        if ($this->debugmode){
            echo $this->print_formatter($this->sql . ' - Rows Number:' . var_export($rows_number,true));
        }

        return $rows_number;
    }

    /**
     * Returns true if the number of rows in result is higher than 0.
     * @return boolean
     */
    public function have_rows(){
        $condition = ($this->num_rows() > 0);

        if ($this->debugmode){
            echo $this->print_formatter($this->sql . ' - Have Rows: ' . var_export($condition,true));
        }

        return $condition;
    }

    /**
     * If $mountArray is true it will do a while and return ALL results in an array.
     * If $mountArray is false it will returns the configured database correspondent fetch_array method.
     * If something goes wrong it will returns a empty array.
     * @param boolean $mountArray
     * @return array
     */
    public function fetch($mountArray = false){
        $array = array();

        if (!$this->have_rows()){
            if ($this->debugmode){ echo $this->print_formatter($this->sql . ' - No Rows Found'); }
            return array();
        }

        if($mountArray){
            while($result = $this->fetch(false)){ $array[] = $result; }
        }else{
            switch ($this->connection['db_type']) {
                case 'mysql':
                    if(is_resource($this->obj_query)) return mysql_fetch_array($this->obj_query, MYSQL_ASSOC);
                    break;
                case 'mysqli':
                    return $this->obj_query->fetch_array(MYSQLI_ASSOC);
                    break;
                case 'mssql':
                    if(is_resource($this->obj_query)) return mssql_fetch_array($this->obj_query,MSSQL_ASSOC);
                    break;
                case 'pgsql':
                    if(is_resource($this->obj_query)) return pg_fetch_array($this->obj_query, 0, PGSQL_ASSOC);
                    break;
                case 'sqlserv':
                    if(is_resource($this->obj_query)) return sqlsrv_fetch_array($this->obj_query, SQLSRV_FETCH_ASSOC);
                    break;
            }
        }

        return $array;
    }

    /**
     * Returns true if num_rows() OR affected_rows() fit with the operation.
     * @param string $signal Comparison Operator.
     *  -> It can be "==", "!=", ">=", "<=", ">", "<". Any other will return false.
     * @return boolean
     */
    public function result($signal = '>='){
        switch ($signal) {
            case "==": return ($this->affected_rows() == 0 || $this->num_rows() == 0);
            case "!=": return ($this->affected_rows() != 0 || $this->num_rows() != 0);
            case ">=": return ($this->affected_rows() >= 0 || $this->num_rows() >= 0);
            case "<=": return ($this->affected_rows() <= 0 || $this->num_rows() <= 0);
            case ">":  return ($this->affected_rows()  > 0 || $this->num_rows()  > 0);
            case "<":  return ($this->affected_rows()  < 0 || $this->num_rows()  < 0);
            default: return false;
        }
        $log = ($result) ? 'NO ERRORS' : 'ERRORS IN EXECUTION';

        if ($this->debugmode){
            echo $this->print_formatter($this->sql . ' - Query Result: ' . $log);
        }

        return $result;
    }

    /**
     * Gets the parameters to update and returns the query SET structure.
     * @return string
     */
    private function mount_update($literal){
        if(count($this->updates) == 0) return '';

        $conditions = array();
        foreach($this->updates as $key=>$value){
            $conditions[] = (!$literal) ? $key . " = '". $value ."'" : $key . " = ". $value;
        }

        return ' SET ' . implode($conditions , ' , ');
    }

    /**
     * Gets the parameters to filter and returns the query WHERE structure.
     * @return string
     */
    private function mount_where(){
        if(count($this->wheres) == 0) return '';

        $conditions = array();
        foreach($this->wheres as $key=>$value){
            if($value['literal'] == false){ $value['value'] = "'" . $value['value'] . "'"; }

            $conditions[] = $value['field'] . ' ' . $value['operator'] . " " . $value['value'];
        }

        return ' WHERE ' . implode($conditions , ' AND ');
    }

    /**
     * Gets the parameters to order and returns the query ORDER BY structure.
     * @return string
     */
    private function mount_order_by(){
        if(count($this->orders) == 0) return '';

        $conditions = array();
        foreach($this->orders as $key=>$value){
            $conditions[] = $value['field'] . ' ' . $value['order'];
        }

        return ' ORDER BY ' . implode($conditions , ' , ');
    }

    /**
     * Uses addslashes recursively.
     * @return string
     */
    private function r_addslashes($array){
        array_walk_recursive($array, function(&$item, $key) {
            $item = addslashes($item);
        });

        return $array;
    }
}