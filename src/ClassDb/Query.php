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

class Query extends Db
{

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
}