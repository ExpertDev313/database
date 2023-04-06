# ExpertDev313 Database, a professional DBMS

Created by <a href="https://t.me/OctalDev" target="_blank" rel="noopener">Octal Developer</a>

`#The_Promised_Savior`

## Usage

To use the ExpertDev313 Database Library, you first need to create a new instance of the `Database` class:

```php
use OctalDev\Database;

$db = new Database('localhost', 'username', 'password', 'my_database');
```

This creates a new instance of the `Database` class that connects to a MySQL database named `my_database` on `localhost` using the username `username` and password `password`.

Once you have created an instance of the `Database` class, you can use it to execute queries:

```php
$results = $db->query('SELECT * FROM users');
```

This executes a SELECT query on the `users` table and returns an array of results.

You can also use prepared statements to execute queries with parameters:

```php
$results = $db->query('SELECT * FROM users WHERE id = ?', [$id]);
```

This executes a SELECT query on the `users` table where the ID matches `$id`.

## Tables

for create a new table you must do the following in your PHP file:

```php
$db->createTable("users", function (Table $table){
	$table->id();
	$table->string("name"); // Varchar ( max is 255 )
	$table->string("email");
	$table->int("phone", 11);
});
```
drop a table :

```php
$db->drop("table", "users");
```

drop all tables :

```php
$db->dropAllTables();
```

show create table :

```php
echo $db->showCreateTable("users");
```

show tables :

```php
print_r($db->showTables());
```

show columns :

```php
print_r($db->showColumns());
```

add a column in a table :

```php
$db->addColumn("users", "column", "dataType");
```

add few columns in a table :

```php
$db->addColumns("users", [
	"column1" => "dataType",
	"column2" =>  "dataType"
]);
```
## Query builder

This method is used to insert a new record into the data table in the MySQL database.  To use this method, you must do the following in your PHP file:

```php
$users = $db->table("users");

$users->insert([
    'name' => 'John Doe',
    'email' => 'johndoe@example.com',
    'phone' => '1234567890'
])->execute();
```

This method is used to update a record in the data table in the MySQL database.  To use this method, you must do the following in your PHP file:

```php

$users->update([
    'name' => 'John Doe 2',
    'email' => 'johndoe@example.com',
])->where("phone", "=", "1234567890")->execute();
```

This method is used to delete a record from the data table in the MySQL database.  To use this method, you must do the following in your PHP file:

```php

$users->delete()->find(1)->execute(); # Find by Id
```

and for get data from table you can use this code:

```php

$stmt = $users->select()->find(1)->execute() # Find By Id | Select *;
$user = $stmt->fetch(PDO::FETCH_ASSOC);

/** Or you can use this code **/

/*
$stmt = $users->select(["name", "phone"])->find1()->excute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
*/
```

## Backup and restore backup

for take backup from your database you must do the following  in your PHP file:

```php
$backup_name = $db->backup("*", "backup_path/"); 

// or $backup_name = $db->backup();
// or $backup_name = $db->backup("table1,table2,table3", "backup_path/");
// or $backup_name = $db->backup(["table1", "table2", "table3"], "backup_path/");

# If backup_path not exists backup method will create backup_path
```

for restore your data from your backup :

```php
$db->restoreBackup("backup_file");
```

## License

The ExpertDev313 Database Library is open source software licensed under the AGPL-3.0-only license. See LICENSE.txt for more information.
