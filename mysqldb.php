<?php
namespace Platypus\Database;

use \Exception;
use \PDO;
use \PDOException;
use \PDOStatement;

class MySQL {
	protected ?PDO $connection = null;
	protected static bool $localhost = false;
	protected ?PDOStatement $prepare = null;
	
	protected array $params = [];
	public array $result = [];
	public int $rows = 0;

	public function __construct(
		string $hostname = "mdb",
		string $username = "admin",
		string $password = "admin",
		string $dbname = "serjeant_lite",
		string $port = "3306"
	) {
		if(empty($this->connection) || !($this->connection instanceof PDO)) {
			if(self::$localhost) {
				$hostname = "127.0.0.1";
				$username = "root";
				$password = "root";
				$dbname = "mysql";
				$port = "3306";
			}
			return $this->connect($hostname, $username, $password, $dbname, $port);
		}
		return $this->connection;
	}

	public function __destruct() {
		# No need to close connection manually
		$this->connection = null;
		$this->clear();
	}

	public static function is_localhost(bool $localhost = false) {
		self::$localhost = $localhost;
	}

	public function is_connected(): bool {
		return ($this->connection instanceof PDO);
	}

	protected function connect(
		string $hostname,
		string $username,
		string $password,
		string $dbname,
		string $port
	): ?PDO {
		# No need to open connection manually
		try {
			$this->connection = new PDO("mysql:host=$hostname;dbname=$dbname;port=$port", $username, $password);
			$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch(PDOException $ce) {
			print_r($ce->getMessage());
		}
		return $this->connection;
	}
	
	public function set_parameters(array $parameters) {
		foreach($parameters as $p => $v) {
			$this->params[$p] = $v;
		}
	}
	
	public function set_parameter(string $parameter, ?string $value = null) {
		if(!empty($value)) {
			$this->params[$parameter] = $value;
		} else {
			$this->params[] = $parameter;
		}
	}
	
	/**
	 * reference https://www.php.net/manual/en/pdo.constants.php
	 * @param PDOStatement $statement
	 * @return void
	 */
	protected function bind_named(PDOStatement $statement) {
		if(!empty($this->params)) {
			foreach($this->params as $param => $value) {
				$type = PDO::PARAM_STR;
				
				switch(gettype($value)) {
					case "boolean":
						$type = PDO::PARAM_BOOL;
						break;
					case "integer":
						$type = PDO::PARAM_INT;
						break;
					case "NULL":
						$type = PDO::PARAM_NULL;
						break;
					case "array":
						$value = implode(", ", $value);
						break;
				}
				
				$statement->bindValue($param, $value, $type);
			}
		}
	}

	public function execute(string $query): bool {
		if(!empty($query)) {
			try {
				
				$this->prepare = $this->connection->prepare($query);
				$this->bind_named($this->prepare);
				
				$sql_exec = $this->prepare->execute($this->params);
				
				if($sql_exec) {
					$this->get_rows();
					if($this->rows > 0) {
						while($sql_result = $this->get_result()) {
							$this->result[] = $sql_result;
						}
					}
				}
				
				$this->clear_params();
				return $sql_exec;
			} catch(Exception $e) {
				print_r($e->getMessage());
			}
		}
		return false;
	}

	public function clear_result() {
		$this->result = [];
		$this->rows = 0;
	}
	
	public function clear_statement() {
		$this->prepare = null;
	}
	
	public function clear_params() {
		$this->params = [];
	}
	
	public function clear() {
		$this->clear_statement();
		$this->clear_params();
		$this->clear_result();
	}

	public function get_rows(): int {
		$this->rows = $this->prepare->rowCount();
		return $this->rows;
	}

	public function get_result() {
		return $this->prepare->fetch(PDO::FETCH_ASSOC);
	}
	
	public function get_bound_params(): array {
		return $this->params;
	}
}
