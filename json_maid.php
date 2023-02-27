<?php
/*
/ Minimum PHP version 7.x
/ Using PHP version 7.3.x
/ Author - Davide - 31/08/2021
*/

class JSONMaid {
	private $connection = null;
	private string $database = "";

	public function __construct(string $database) {
		if(!empty($database) && is_file($database) && preg_match("/(\.json){1}$/", $database)) {
			$this->database = $database;

			$data = file_get_contents($database);

			$data = json_decode($data);

			$this->connection = std2_array($data); # Requires utilities.php
		}
	}

	private function save() {
		$data = json_encode($this->connection);
		
		try {
			return file_put_contents($this->database, $data);
		} catch(Exception $e) {
			echo $e->getMessage();
		}

		return false;
	}

	public function get_records() {
		return $this->connection["data"];
	}

	public function delete_records() {
		$this->connection["data"] = (object) null;

		return $this->save();
	}

	public function records_count() {
		return count($this->connection["data"]);
	}

	public function delete_record($index) {
		unset($this->connection["data"][$index]);

		return $this->save();
	}

	public function update_record($record, $new_data, $new_name = false) {
		$data = $this->get_records();

		$this->connection["data"][$record] = $new_data;

		$this->delete_record($record);

		if(!empty($new_name)) {
			$this->put_record($new_name, $new_data);
		}

		return $this->save();
	}

	public function get_record($record) {
		return $this->get_records()[$record];
	}

	public function put_record($record, $data) {
		$can_be_added = true;
		$records_count = count($data);

		foreach($this->get_records() as $i => $d) {
			if($i === $record) {
				$can_be_added = false;
				break;
			}
		}

		if($can_be_added) {
			$this->connection["data"][$record] = $data;
			return $this->save();
		}

		return $can_be_added;
	}

	public function get_index_of($record) {
		$x = 0;
		foreach($this->get_records() as $i => $d) {
			if($i === $record) {
				return $x;
			}
			$x++;
		}

		return null;
	}
}
?>
