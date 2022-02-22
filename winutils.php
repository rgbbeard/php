<?php
/*
  Minimum PHP version 7.x
  Using PHP version 8.1.1
  Author - Davide - 21/02/2022
  Git - github.com/rgbbeard
*/

function get_user() {
	return shell_exec("echo %USERNAME%");
}

function get_cwd() {
	$cwd = shell_exec("echo %cd%");
	return '"' . trim($cwd) . '"';
}

function get_active_processes() {
	$pcs = shell_exec("tasklist /APPS /FO \"CSV\"");
	return str_replace('","', '";"', $pcs);
}

function find_process(string $process_name = "") {
	$pcs = get_active_processes();
	$pcs = explode("\n", $pcs);
	$result = [];
	$x = 0;

	foreach($pcs as $pc) {
		$data = explode(";", $pc);

		if(str_contains($data[0], $process_name)) {
			$result[$x] = [];

			foreach($data as $d) {
				$result[$x][] = str_replace('"', "", $d);
			}

			$x++;
		}
	}

	return $result;
}

function stop_process(int $process_id, bool $force = false) {
	$force = $force ? "/F" : "";

	shell_exec("taskkill $force /PID $process_id");
}

function unzip(string $file_path, string $destination_path) {
	shell_exec("php unzip.php $file_path $destination_path");
}

function move_dir(string $dir_path, string $destination_path) {
	shell_exec("move /Y $dir_path $destination_path");
}

function delete_dir(string $dir_path) {
	shell_exec("rmdir /S /Q $dir_path");
}
?>
