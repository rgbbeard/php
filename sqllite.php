<?php
namespace Database;

use \Exception;
use \SQLite3;
use \SQLite3Stmt;
use \SQLite3Result;
use \SQLite3Exception;

class SQLLite {
    protected const default_dbfilename = "mydb.sqlite";

    protected const modes_table = [
        "r+" => SQLITE3_OPEN_READWRITE,
        "c" => SQLITE3_OPEN_CREATE,
        "w+" => SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE
    ];

    protected ?SQLite3 $connection = null;
    protected SQLite3Stmt|false|null $prepare = null;
    protected ?SQLite3Result $sqlresult = null;
    
    protected array $params = [];
    public array|false $result = [];
    public int $rows = 0;

    /**
     * @param string $dbfilename
     * @param string $mode
     * @return SQLite3|null
     */
    public function __construct(
        string $dbfilename = "",
        string $mode = "r+"
    ) {
        if(
            empty($this->connection) 
            || !($this->connection instanceof SQLite3)
        ) {
            if(empty($dbfilename)) {
                $dbfilename = dirname(__FILE__) 
                    . DIRECTORY_SEPARATOR 
                    . self::default_dbfilename;
            }

            return $this->connect($dbfilename, $mode);
        }

        return $this->connection;
    }

    public function __destruct() {
        # No need to close connection manually
        $this->connection->close();
        $this->connection = null;
        $this->clear();
    }

    public function clear_result() {
        $this->sqlresult = null;
        $this->result = [];
        $this->rows = 0;
    }
    
    public function clear_statement() {
        $this->prepare = null;
    }
    
    public function clear_params() {
        $this->params = [];

        if(!is_null($this->prepare)) {
            $this->prepare->clear();
        }
    }
    
    public function clear() {
        $this->clear_statement();
        $this->clear_params();
        $this->clear_result();
    }

    /**
     * @return bool
     */
    public function is_connected(): bool {
        return ($this->connection instanceof SQLite3);
    }

    /**
     * @param string $dbfilename
     * @param string $mode
     * @return SQLite3|null
     */
    protected function connect(
        string $dbfilename,
        string $mode = "r+"
    ): ?SQLite3 {
        # No need to open connection manually
        try {
            $m = isset(self::modes_table[$mode]) ? 
                self::modes_table[$mode] : self::modes_table["r+"];

            $this->connection = new SQLite3($dbfilename, $m);
        } catch(SQLite3Exception $ce) {
            print_r($ce->getMessage());
        }
        return $this->connection;
    }
    
    /**
     * @param array $parameters
     */
    public function set_parameters(array $parameters) {
        foreach($parameters as $p => $v) {
            $this->params[$p] = $v;
        }
    }
    
    /**
     * @param string $parameter
     * @param string|null $value
     */
    public function set_parameter(string $parameter, ?string $value = null) {
        if(!empty($value)) {
            $this->params[$parameter] = $value;
        } else {
            $this->params[] = $parameter;
        }
    }

    /**
     * https://www.php.net/manual/en/sqlite3stmt.bindvalue.php
     * 
     * @param SQLite3Stmt $statement
     */
    protected function bind_named(SQLite3Stmt $statement) {
        if(!empty($this->params)) {
            foreach($this->params as $param => $value) {
                $type = SQLITE3_TEXT;
                
                switch(gettype($value)) {
                    case "boolean":
                        $type = SQLITE3_INTEGER;
                        break;
                    case "integer":
                        $type = SQLITE3_INTEGER;
                        break;
                    case "double":
                        $type = SQLITE3_FLOAT;
                        break;
                    case "float":
                        $type = SQLITE3_FLOAT;
                        break;
                    case "NULL":
                        $type = SQLITE3_NULL;
                        break;
                    case "array":
                        $value = implode(", ", $value);
                        break;
                }
                
                $statement->bindValue($param, $value, $type);
            }
        }
    }

    /**
     * @param string $query
     * @param array|null $parameters
     * @return bool
     */
    public function execute(string $query, ?array $parameters = []): bool {
        if(!empty($query)) {
            try {
                $this->clear_result();

                # Cannot be used along with set_parameter(s)
                if(!empty($parameters)) {
                    $this->clear_params();
                    $this->set_parameters($parameters);
                }

                $this->prepare = $this->connection->prepare($query);

                if(!$this->prepare) {
                    return false;
                }

                $this->bind_named($this->prepare);
                
                $this->sqlresult = $this->prepare->execute();
                
                if($this->sqlresult !== false) {
                    $this->get_result();
                }

                $this->clear_params();
                $this->clear_statement();

                return (bool) $this->sqlresult;
            } catch(Exception $e) {
                print_r($e->getMessage());
                return false;
            }
        }
    }

    /**
     * @return string|false
     */
    public function dump_sql(): string|false {
        if(!is_null($this->prepare)) {
            try {
                return $this->prepare->getSQL(true);
            } catch(Exception $e) {
                print_r($e->getMessage());
            }
        }
    }
    
    /**
     * @return array
     */
    public function get_bound_params(): array {
        return $this->params;
    }

    /**
     * @return int
     */
    public function get_rows(): int {
        return count($this->result);
    }

    /**
     * https://www.php.net/manual/en/sqlite3result.fetcharray.php
     * 
     * @return array
     */
    public function get_result() {
        try {
            if(!$this->sqlresult) {
                return [];
            }

            while($result = $this->sqlresult->fetchArray(SQLITE3_ASSOC)) {
                $this->result[] = $result;
            }

            return $this->result;
        } catch(Exception $ignore) {
            return [];
        }
    }
}
