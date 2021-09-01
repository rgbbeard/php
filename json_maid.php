<?php
# Used to convert json data (instance of stdClass) to associative array
function std2_array($stdclass): array {
    $temp = [];
    foreach($stdclass as $name => $value) {
        if($value instanceof stdClass) {
            $temp[$name] = std2_array($value);
        } else $temp[$name] = $value;
    }
    return $temp;
}

class LocalConnector {
	private $connection = null;
	private string $database = "";

	public function __construct(string $database) {
		// Verify that the given filename is an actual json file
		if(!empty($database) && is_file($database) && preg_match("/(\.json){1}$/", $database)) {
			$this->database = $database;

			$data = file_get_contents($database);

			$this->connection = std2_array($this->from_json($data));
		}
	}

	private function to_json($data) {
		return base64_encode($data);
	}

	private function from_json($data) {
		return base64_decode($data);
	}

	private function save() {
		$data = $this->to_json($this->connection);
		
		try {
			return file_put_contents($this->database, $data) ? true : false;
		} catch(Exception $e) {
			echo $e->getMessage();
		}

		return false;
	}

	public function get_records() {
		return $this->connection["data"];
	}

	public function records_count() {
		return sizeof($this->connection["data"]);
	}

	public function get_user_by_badge(string $badge) {
		$data = $this->get_records();

		foreach($data as $row) {
			if(strval($row["badge"]) === $badge) {
				return $row;
			}
		}

		return null;
	}

	public function delete_record(int $id) {
		$data = $this->get_records();

		try {
			unset($data[strval($id)]);

			return $this->save();
		} catch(Exception $e) {
			echo $e->getMessage();
		}

		return false;
	}

	public function update_record($old_record, $new_record) {
		$data = $this->get_records();

		foreach($data as $row) {
			if($row == $old_record) {
				$row = $new_record;
				return true;
			}
		}

		return false;
	}

	public function put_record($data, bool $ignore_same_badge = false) {
		$can_be_added = true;

		if(!isset($data["badge"]) || empty($data["badge"])) {
			return false;
		} else {
			$data = $this->get_records();
			$records_count = $this->records_count();

			for($x = 1;$x<$records_count+1;$x++) {
				$row = $this->get_records()[strval($x)];

				if($row["badge"] === $data["badge"] && !$ignore_same_badge) {
					$can_be_added = false;
					break;
				}
			}

			if($can_be_added) {
				$uid = strval($records_count+1);

				$this->connection["data"][$uid] = $data;
				return $this->save();
			}
		}

		return $can_be_added;
	}

	public function __destruct() {
		$this->connection = null;
		$this->database = "";
	}
}
?>
