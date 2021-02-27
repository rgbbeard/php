<?php
require_once "utils.php";

class Database {
    private $host;
    private $user;
    private $pass;
    private $dbname;

    protected $connection;
    protected static $result;

    public $debugger;

    public function __construct($data = [
        "host"=> "hostname",
        "user"=> "username",
        "pass"=> "password",
        "dbname"=> "dbname"
    ]) {
        foreach($data as $d) {
            if(gettype($d) !== "string") throw new Exception("Connection data must be string type.");
        }
        $this->host = $data["host"];
        $this->user = $data["user"];
        $this->pass = $data["pass"];
        $this->dbname = $data["dbname"];
        $this->Connect();
    }

    public function Connect() {
        $connection = mysqli_connect($this->host, $this->user, $this->pass, $this->dbname);
        if($connection == true) {
            $this->connection = $connection;
            return $connection;
        } else return false;
    }

    public function Exec(string $query) {
        $result = mysqli_query($this->connection, $query);
        if($result == true) {
            self::$result = $result;
            return $result;
        } else return false;
    }

    public function Multi(string $multiQuery) {
        $result = mysqli_multi_query($this->connection, $multiQuery);
        return $result == true ? true : false;
    }

    public function Rows() {
        $rows = mysqli_num_rows(self::$result);
        return $rows >= 0 ? $rows : false;
    }

    public static function Fetch($result = null) {
        $data = is_null($result) ? self::$result : $result;
        $fetch = mysqli_fetch_array($data);
        return !is_null($fetch) ? $fetch : false;
    }

    public function __destruct() {
        $close = mysqli_close($this->connection);
        return $close == true ? true : false;
    }
}