<?php
/*
/ Minimum PHP version 7.x
/ Using PHP version 8.1.1
/ Author - Davide - github.com/rgbbeard/
*/

class MySQL {
	public $connection = null;
	protected $prepare = null;
	public $result = [];
	public $rows = 0;

	public function __construct(string $hostname = "localhost", string $username = "root", string $password = "", string $dbname = "localhost", string $port = "3306") {
		if(empty($this->connection) || !($this->connection instanceof PDO)) {
			return $this->connect($hostname, $username, $password, $dbname, $port);
		}
		return $this->connection;
	}

	public function __destruct() { # No need to close connection manually
		$this->connection = null;
	}

    public static function is_database($connection): bool {
        return ($connection instanceof PDO);
    }

	protected function connect(string $hostname, string $username, string $password, string $dbname, string $port) { # No need to open connection manually
		try {
			$this->connection = new PDO("mysql:host=$hostname;dbname=$dbname;port=$port", $username, $password);
		} catch(PDOException $ce) {
			print_r($ce->getMessage());
		}
		return $this->connection;
	}

	public function execute(string $query) {
		if(!empty($query)) {
			try {
				$this->prepare = $this->connection->prepare($query);
				$sql_exec = $this->prepare->execute();
				if($sql_exec) {
					$this->get_rows();
					if($this->rows > 0) {
						$this->get_result();
					}
				}
				return $sql_exec;
			} catch(Exception $e) {
				print_r($e->getMessage());
			}
		}

		return null;
	}

	public function get_rows() {
		$this->rows = $this->prepare->rowCount();
	}

	public function get_result() {
		$this->result = $this->prepare->fetch(PDO::FETCH_ASSOC);
	}
}
?>
