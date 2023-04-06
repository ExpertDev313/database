<?php

/**
 * In the name of Allah
 *
 * Hoping the appearance Promised Savior
 *
 * @author Octal Developer 2023 - 2024 <tg: @OctalDev>
 * @copyright Octal Developer 2023 - 2024 <tg: @OctalDev>
 */
namespace OctalDev;

/**
 * class Database
 */
class Database
{
	/**
	 * @var PDO $pdo database
	 */
	public $pdo;
	
	/**
	 * @var object $query_builder query builder
	 */
	private $query_builder;
	
	/**
	 * Constructor
	 *
	 * @param string $host host
	 * @param string $user username
	 * @param string $pass password
	 * @param string $name database name
	 */
	public function __construct(string $host, string $user, string $pass, string $name)
	{
		
		try {
			$this->pdo = new PDO("mysql:dbname={$name};host={$host};charset=utf8", $user, $pass);
			$this->pdo->exec("SET NAMES 'utf8';");
		} catch ( PDOException $e ) {
			throw new Exception($e->getMessage());
		}
		
		$this->query_builder = (object) [];
		$this->query_builder->mode = "";
		$this->query_builder->where = "";
	}
	
	/**
	 * Query
	 *
	 * @param string $sql sql
	 * @param array $params params
	 *
	 * @return PDO
	 */
	public function query(string $sql, array $params = [])
	{
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute($params);
		
		return $stmt;
	}
	
	/**
	 * Security
	 *
	 * @param string $data data
	 * @return string
	 */
	public function security(string $data)
	{
		return trim(htmlentities(addslashes($data)));
	}
	
	/**
	 * Select table
	 *
	 * @param string $name table name
	 * @return Database
	 */
	public function table(string $name)
	{
		$name = $this->security($name);
		$this->query_builder->table = $name;
		
		return $this;
	}
	
	/**
	 * Insert
	 *
	 * @param array $datas datas
	 * @return Database
	 */
	public function insert(array $datas)
	{
		$this->query_builder->mode = "insert";
		$this->query_builder->insert = $datas;
		
		return $this;
	}
	
	/**
	 * Select
	 *
	 * @param array $fields fields to select
	 * @return Database
	 */
	public function select(array $fields = ['*'])
	{
		$this->query_builder->mode = "select";
		$this->query_builder->select = $fields;
		
		return $this;
	}
	
	/**
	 * Update
	 *
	 * @param array $datas datas
	 * @return Database
	 */
	public function update(array $datas)
	{
		$this->query_builder->mode = "update";
		$this->query_builder->update = $datas;
		
		return $this;
	}
	
	/**
	 * Delete
	 *
	 * @return Database
	 */
	public function delete()
	{
		$this->query_builder->mode = "delete";
		
		return $this;
	}
	
	/**
	 * Create table
	 *
	 * @param string $name table name
	 * @param object $closure function
	 *
	 * @return PDO
	 */
	public function createTable(string $name, object $closure)
	{
		$name = $this->security($name);
		
		$table = new Table();
		
		$closure($table);
		
		$query = $table->query;
		$query = substr($query, 0, strlen($query) - 1);
		
		$query = "CREATE TABLE IF NOT EXISTS `{$name}`(" . $query . ") ";
		$query .= "default charset = utf8mb4;";
		
		return $this->query($query);
	}
	
	/**
	 * Show create table
	 *
	 * @param string $table table name
	 *
	 * @return string
	 */
	public function showCreateTable(string $table)
	{
		$stmt = $this->query("SHOW CREATE TABLE test");
		$row = $stmt->fetch(PDO::FETCH_NUM);
		
		return $row[1];
	}
	
	/**
	 * Drop
	 *
	 * @param string $type (database/table)
	 * @param strinb $name (database/table)
	 *
	 * @return PDO
	 */
	public function drop(string $type, string $name)
	{
		$type = strtoupper($this->security($type));
		$name = $this->security($name);
		
		if( !in_array($type, ["DATABASE", "TABLE"]) ) {
			throw new Exception("Drop type must be Database or Table");
		}
		
		$query = "DROP $type IF EXISTS $name";
		
		return $this->query($query);
	}
	
	/**
	 * Drop all tables
	 *
	 * @return void
	 */
	public function dropAllTables()
	{
		$tables = $this->showTables();
		
		foreach($tables as $table) {
			$this->drop("table", $table);
		}
	}
	
	/**
	 * Add a column
	 *
	 * @param string $tableName table name
	 * @param string $columnName column name
	 * @param string $dataType data type
	 *
	 * @return PDO
	 */ 
	public function addColumn(string $tableName, string $columnName, string $dataType)
	{
		$table = $this->security($tableName);
		$column = $this->security($columnName);
		$data = $this->security($dataType);
		
		$query = "alter table {$table}\n";
		$query .= "add column {$column} {$data}";
		
		return $this->query($query);
	}
	
	/**
	 * Add few column
	 *
	 * @param string $tableName table name
	 * @param array $datas columnName and dataType
	 *
	 * @return PDO
	 */
	public function addColumns($tableName, $datas)
	{
		$table = $this->security($tableName);
		
		$query = "alter table {$table}\n";
		
		foreach($datas as $columnName => $dataType) {
			$column = $this->security($columnName);
			$data = $this->security($dataType);
			$query .= "add column {$column} {$data},";
		}
		
		$query = substr($query, 0, strlen($query) - 1);
		
		return $this->query($query);
	}
	
	/**
	 * Get columns
	 *
	 * @param string $table table name
	 *
	 * @return array
	 */
	public function showColumns(string $table)
	{
		$table = $this->security($table);
		
		$stmt = $this->query("DESCRIBE `{$table}`;");
		
		return $stmt->fetchAll(PDO::FETCH_COLUMN);
	}
	
	/**
	 * where
	 *
	 * @param string $field field
	 * @param string $value value
	 * @oaram string $operator operator ( default = "=" )
	 *
	 * @return Datbase
	 */
	public function where(string $field, string $value, string $operator = '=')
	{
		$field = $this->security($field);
		$value = $this->security($value);
		$operator = $this->security($operator);
		
		$this->query_builder->where = "WHERE `{$field}` {$operator} '{$value}'";
		
		return $this;
	}
	
	/**
	 * Find by id
	 *
	 * @param string $id id
	 * @return Database
	 */
	public function find(string $id)
	{
		return $this->where("id", $id, "=");
	}
	
	/**
	 * Execute
	 *
	 * @return array | object PDO response
	 */
	public function execute()
	{
		$query = $this->getQuery();
		
		return $this->query($query);
	}
	
	/**
	 * Get query
	 *
	 * @return string
	 */
	public function getQuery()
	{
		$query_builder = $this->query_builder;
		
		$table = $query_builder->table;
		$mode = $query_builder->mode;
		$where = $query_builder->where;
		
		if( $mode == "select" ) {
			$select = $query_builder->select;
			$query = "SELECT ";
			
			foreach($select as $key) {
				$key = $this->security($key);
				$query .= "{$key}, ";
			}
			
			$query = substr($query, 0, strlen($query) - 2);
			$query .= " FROM `{$table}` $where";
		}
		
		if( $mode == "delete" ) {
			$query = "DELETE FROM `{$table}`";
			$query .= " $where";
		}
		
		if( $mode == "update" ) {
			$query = "UPDATE `{$table}` SET ";
			foreach($query_builder->update as $key => $value) {
				$value = $this->security($value);
				$query .= "`$key` = '$value', ";
			}
			$query = substr($query, 0, strlen($query) - 2);
			$query .= " $where";
		}
		
		if( $mode == "insert" ) {
			$query = "INSERT INTO `{$table}` SET ";
			foreach($query_builder->insert as $key => $value) {
				$value = $this->security($value);
				$query .= "`$key` = '$value', ";
			}
			$query = substr($query, 0, strlen($query) - 2);
		}
		
		$query .= ";";
		
		return $query;
	}
	
	/**
	 * Show tables
	 *
	 * @return array
	 */
	public function showTables()
	{
		$query = "SHOW TABLES;";
		$stmt = $this->query($query);
		
		$tables = [];
		$tablesArray = $stmt->fetchAll(PDO::FETCH_NUM);
		
		foreach( $tablesArray as $table ) {
			$tables[] = $table[0];
		}
		
		return $tables;
	}
	
	/**
	 * Backup
	 *
	 * @param string | array $tables tables that you want give backup from it
	 * @param string $backup_path backup folder
	 *
	 * @return string backup file name
	 */
	public function backup($tables = '*', string $backup_path = 'backup/')
	{
		if( !is_dir($backup_path) ) {
			mkdir($backup_path);
			file_put_contents($backup_path . "index.php", "<?php http_response_code(403); ?>");
		}
			
		if($tables == '*'){
			$tables = $this->showTables();
		} else {
			$tables = is_array($tables) ? $tables : explode("," , $tables);
		}
		
		if( function_exists("jdate") ) {
			$date = jdate("Y/m/d H:i");
		} else {
			$date = date("Y/m/d H:i");
		}
		
		$sql = "/**" . PHP_EOL;
		$sql .= " * In the name of Allah" . PHP_EOL;
		$sql .= " *" . PHP_EOL;
		$sql .= " * @date $date" . PHP_EOL;
		$sql .= " */" . PHP_EOL . PHP_EOL;
		
		foreach( $tables as $table ) {
			$sql .= "DROP TABLE IF EXISTS $table;";
			
			$result = $this->table($table)->select()->execute();
			$numColumns = $result->columnCount();
			
			$result2 = $this->query("SHOW CREATE TABLE $table");
			$row2 = $result2->fetch(PDO::FETCH_NUM);

			$sql .= "\n\n" . $row2[1] . ";\n\n";

			for( $i = 0; $i < $numColumns; $i++ ) {
				while( $row = $result->fetch(PDO::FETCH_NUM) ){
					$sql .= "INSERT INTO $table VALUES(";
					for($j=0; $j < $numColumns; $j++){
						$row[$j] = addslashes($row[$j]);
						$row[$j] = preg_replace("/\n/","\\n", $row[$j]);
						if (isset($row[$j])) { $sql .= '"'.$row[$j].'"' ; } else { $sql .= '""'; }
						if ($j < ($numColumns-1)) { $sql.= ','; }
					}
					$sql .= ");\n";
				}
			}

			$sql .= "\n\n\n";
		}
				
		$backup_name = md5(hash("sha256", time() . rand())) . ".sql";
		$backup_file = $backup_path . $backup_name;
		
		file_put_contents($backup_file, base64_encode(gzdeflate($sql)));
		chmod($backup_file, 600);
		
		return $backup_file;
	}
	
	/**
	 * Restore backup
	 *
	 * @param string $backup_file backup file address
	 *
	 * @return void
	 */
	public function restoreBackup(string $backup_file)
	{
		if( file_exists($backup_file) ) {
			$backup = gzinflate(base64_decode(file_get_contents($backup_file)));
			$stmt = $this->query($backup);
			unlink($backup_file);
		}
	}
	
	/**
	 * Close
	 *
	 * @return void
	 */
	public function close()
	{
		$this->pdo = null;
		$this->query_builder = null;
	}
}

/**
 * class Table
 */
class Table{
			
	public $query;
	
	/**
	 * Security
	 *
	 * @param string $data data
	 * @return string
	 */
	public function security(string $data)
	{
		return trim(htmlentities(addslashes($data)));
	}
	
	/**
	 * Primary
	 *
	 * @param string $name column name
	 * @return void
	 */
	public function primary(string $name)
	{
		$name = $this->security($name);
		if( !strpos($this->query, "PRIMARY KEY") ) $this->query .= "PRIMARY KEY ({$name}),";
	}
	
	/**
	 * Id
	 *
	 * @return void
	 */
	public function id()
	{
		$this->query .= "id INT NOT NULL AUTO_INCREMENT,";
		$this->primary("id");
	}
	
	/**
	 * Int
	 *
	 * @param string $name column name
	 * @param int $max default (11)
	 *
	 * @return void
	 */
	public function int(string $name, int $max = 11)
	{
		$name = $this->security($name);

		$this->query .= "$name INT($max) NOT NULL,";
	}
	
	/**
	 * Bigint
	 *
	 * @param string $name column name
	 * @param int $max default (11)
	 *
	 * @return void
	 */
	public function bigInt(string $name, int $max = 20)
	{
		$name = $this->security($name);

		$this->query .= "$name bigint($max) NOT NULL,";
	}

	/**
	 * String ( varchar )
	 *
	 * @param string $name column name
	 * @param int $max default (255)
	 *
	 * @return void
	 */
	public function string(string $name, int $max = 255)
	{
		$name = $this->security($name);

		$this->query .= "$name varchar($max) NOT NULL,";
	}

	/**
	 * Text
	 *
	 * @param string $name column name
	 *
	 * @return void
	 */
	public function text(string $name)
	{
		$name = $this->security($name);
	
		$this->query .= "$name TEXT NOT NULL,";
	}
	
	/**
	 * Mediumtext
	 *
	 * @param string $name column name
	 *
	 * @return void
	 */
	 
	public function mediumText(string $name)
	{
		$name = $this->security($name);

		$this->query .= "$name MEDIUMTEXT NOT NULL,";
	}

	/**
	 * Date
	 *
	 * @param string $name column name
	 *
	 * @return void
	 */
	public function date(string $name)
	{
		$name = $this->security($name);

		$this->query .= "$name DATE,";
	}

	/**
	 * Time
	 *
	 * @param string $name column name
	 *
	 * @return void
	 */
	public function time(string $name)
	{
		$name = $this->security($name);

		$this->query .= "$name TIME,";
	}

	/**
	 * Datetime
	 *
	 * @param string $name column name
	 *
	 * @return void
	 */
	public function dateTime(string $name)
	{
		$name = $this->security($name);

		$this->query .= "$name DATETIME,";
	}
	
	/**
	 * Timestamp
	 *
	 * @param string $name column name
	 * @param string value value
	 *
	 * @return void
	 */
	public function timestamp(string $name, string $value)
	{
		$name = $this->security($name);
		$value = $this->security($value);

		$this->query .= "$name TIMESTAMP($value),";
	}
}