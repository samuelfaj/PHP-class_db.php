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

/*
EXAMPLES:
----------------------------------------------------------------------------------------------------------------------------------------
<?php
include ('class_db.php');

$db = new db(array(
    'host'     => 'localhost'      ,  // string - Host of Connection.
    'user'     => 'username'       ,  // string - Database's User.
    'password' => 'mysecretpass'   ,  // string - User's Password.
    'database' => 'myapplication'  ,  // string - Default Database name.
    'db_type'  => 'mysql'          ,  // string - Type of Database. (It can be: 'mysql', 'mysqli' , 'mssql' , 'sqlserv' , 'pgsql').
));
$sql = new query($db);

$sql->exec("SELECT 1");
var_dump($sql->query);
----------------------------------------------------------------------------------------------------------------------------------------
<?php
	include ('class_db.php');

	$sql = new query(array(
        'host'     => 'localhost'      ,  // string - Host of Connection.
        'user'     => 'username'       ,  // string - Database's User.
        'password' => 'mysecretpass'   ,  // string - User's Password.
        'database' => 'myapplication'  ,  // string - Default Database name.
        'db_type'  => 'mysql'          ,  // string - Type of Database. (It can be: 'mysql', 'mysqli' , 'mssql' , 'sqlserv' , 'pgsql').
    ));

	$sql->exec("SELECT * FROM users");
	var_dump($sql->query);
----------------------------------------------------------------------------------------------------------------------------------------
<?php
	include ('class_db.php');

	$sql = new query(array(
        'host'     => 'localhost'      ,  // string - Host of Connection.
        'user'     => 'username'       ,  // string - Database's User.
        'password' => 'mysecretpass'   ,  // string - User's Password.
        'database' => 'myapplication'  ,  // string - Default Database name.
        'db_type'  => 'mysql'          ,  // string - Type of Database. (It can be: 'mysql', 'mysqli' , 'mssql' , 'sqlserv' , 'pgsql').
    ));

	$sql->table('users');
	$sql->limit(array(0,10));

	var_dump($sql->select());
----------------------------------------------------------------------------------------------------------------------------------------
<?php

	...

	$sql = new query($db);
	$sql->table('users');
	$sql->where(
        array('id'    , $_POST['id']),
        array('email' , $_POST['email'])
    );
	$sql->order('id','DESC');
	$sql->limit(1);

	if( $sql->select()->have_rows ){
        $sql->update(array('active' => 0));
    }
----------------------------------------------------------------------------------------------------------------------------------------
 */
