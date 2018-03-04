# Class_DB 
<img src="https://www.issart.com/blog/wp-content/uploads/2017/03/boxbarimage5.jpg" width="150" align="right">

![MIT license](https://img.shields.io/badge/license-MIT-blue.svg)
[![built with PHP](https://img.shields.io/badge/built%20with-PHP-red.svg)](https://www.php.net/)
![MySQL Ready](https://img.shields.io/badge/mysql-ready-green.svg)
![MySQLI Ready](https://img.shields.io/badge/mysqli-ready-green.svg)
![mssql Ready](https://img.shields.io/badge/mssql-ready-green.svg)
![sqlserv Ready](https://img.shields.io/badge/sqlserv-ready-green.svg)
![pgsql Ready](https://img.shields.io/badge/pgsql-ready-green.svg)

*Write less. Do a lot more.*
**Class_DB allows you to write secure SQL querys easily and work with many different databases using the same syntax**

Everytime i was working with or starting a new system, i had to look at the database documentation and choose between create a class for it using mine functions or write the system following his own. But what if the driver changed? Yeah, Everyone who is here for a while saw some changes in PHP drivers. Those changes were a painful work for great systems and even more for small teams. We saw mssql become sqlserv, mysql become mysqli and it's common to see groups deciding to change the database in the middle of the project.

Thinking about it i developed class_db. It helps us to write less SQL and don't care for the chosen database.
Just write your code and class_db will do the rest. If the driver or the team resolves to change, just update it!

### Install
Use composer

```php
composer require samuelfaj/class_db
```

Or just download and require it.

```php
require_once('/includes/class_db.php');
```

## Examples

Initializing
```php
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
```

Doing a simple query passing it as text:
```php
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
    $sql->exec("SELECT * FROM users");
    var_dump($sql->query);
```

Doing a select without write one single character of SQL:
```php
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
```

Selecting and updating a user without write one single character of SQL:
```php
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
```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details
