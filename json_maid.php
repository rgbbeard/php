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
			return file_put_contents($this->database, $data) ? true : false;
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

	public function update_record($index, $new_data) {
		$data = $this->get_records();

		foreach($data as $i => $d) {
			if($i === $index) {
				$this->connection["data"][$index] = $new_data;
				return $this->save();
			}
		}

		return false;
	}

	public function get_record($record) {
		return $this->get_records()[$record];
	}

	public function put_record($index, $data) {
		$can_be_added = true;
		$records = $this->get_records();
		$records_count = count($data);

		foreach($records as $i => $d) {
			if($i === $index) {
				$can_be_added = false;
				break;
			}
		}

		if($can_be_added) {
			if(empty($index)) {
				$index = strval($records_count+1);
			}

			$this->connection["data"][$index] = $data;
			return $this->save();
		}

		return $can_be_added;
	}
}
?>
