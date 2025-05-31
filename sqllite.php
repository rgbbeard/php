<?php
namespace Database;

use \Exception;
use \SQLite3;
use \SQLite3Stmt;
use \SQLite3Result;
use \SQLite3Exception;

class SQLLite {
    protected const modes_table = [
        "r+" => SQLITE3_OPEN_READWRITE,
        "c" => SQLITE3_OPEN_CREATE,
        "w+" => SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE
    ];

    protected ?SQLite3 $connection = null;
    protected ?SQLite3Stmt $prepare = null;
    protected ?SQLite3Result $sqlresult = null;
    
    protected array $params = [];
    public array|false $result = [];
    public int $rows = 0;

    public function __construct(
        string $dbfilename = "mydb.sqlite",
        string $mode = "r+"
    ) {
        if(
            empty($this->connection) 
            || !($this->connection instanceof SQLite3)
        ) {
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

    public function is_connected(): bool {
        return ($this->connection instanceof SQLite3);
    }

    protected function connect(
        string $dbfilename,
        string $mode = "r+"
    ): ?SQLite3 {
        # No need to open connection manually
        try {
            $m = in_array($mode, self::modes_table) ? 
                self::modes_table[$mode] : self::modes_table["r+"];

            $this->connection = new SQLite3($dbfilename, $m);
        } catch(SQLite3Exception $ce) {
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
     * https://www.php.net/manual/en/sqlite3stmt.bindvalue.php
     * 
     * @param SQLite3Stmt $statement
     * @return void
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
            }
        }
        return false;
    }

    public function dump_sql(): string|false {
        if(!is_null($this->prepare)) {
            try {
                return $this->prepare->getSQL(true);
            } catch(Exception $e) {
                print_r($e->getMessage());
            }
        }
    }
    
    public function get_bound_params(): array {
        return $this->params;
    }

    public function get_rows(): int {
        return count($this->result);
    }

    /**
     * https://www.php.net/manual/en/sqlite3result.fetcharray.php
     */
    public function get_result() {
        try {
            while($result = $this->sqlresult->fetchArray(SQLITE3_ASSOC)) {
                $this->result[] = $result;
            }

            return $this->result;
        } catch(Exception $ignore) {
            return [];
        }
    }
}