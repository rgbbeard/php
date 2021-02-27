<?php
class Database {
    private $host;
    private $user;
    private $pass;
    private $dbname;

    protected $connection;
    protected $result;

    public $debugger;

    public function __construct($data = [
        "host"=> "localhost",
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
            $this->result = $result;
            return $result;
        } else return false;
    }

    public function Rows() {
        $rows = mysqli_num_rows($this->result);
        return $rows >= 0 ? $rows : false;
    }

    public function Fetch() {
        $fetch = mysqli_fetch_array($this->result);
        if($fetch !== null) return $fetch; 
        else return false;
    }

    public function __destruct() {
        $close = mysqli_close($this->connection);
    }
}
