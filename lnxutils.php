<?php
/*
	Minimum PHP version 7.x
	Using PHP version 7.4.3
	Author - Davide - 22/02/2022
	Git - github.com/rgbbeard
*/

function get_user() {
	return shell_exec("echo \$USER");
}

function get_cwd() {
	return shell_exec("pwd");
}

function get_active_processes(bool $remove_header = false) {
	$processes = shell_exec("ps -A");
	$processes = explode("\n", trim($processes));

	$result = [];

	$x = 0;
	foreach($processes as $process) {
		$result[$x] = [];
		$process = preg_replace("/\s+/", ";", $process);
		$process = preg_replace("/^;/", "", $process);
		$process_info = explode(";", $process);

		if(!empty($process_info[0])) {
			$y = 0;
			foreach($process_info as $pi) {
				$result[$x][$y] = $pi;
				$y++;
			}

			$x++;
		}
	}

	if($remove_header) {
		array_shift($result);
	}

	return $result;
}

function find_process(string $process_name = "", bool $remove_header = false) {
	$processes = shell_exec("ps -A | grep $process_name");
	$processes = explode("\n", trim($processes));

	$result = [];

	$x = 0;
	foreach($processes as $process) {
		$result[$x] = [];
		$process = preg_replace("/\s+/", ";", $process);
		$process = preg_replace("/^;/", "", $process);
		$process_info = explode(";", $process);
		
		if(!empty($process_info[0])) {
			$y = 0;
			foreach($process_info as $pi) {
				$result[$x][$y] = $pi;
				$y++;
			}

			$x++;
		}
	}

	if($remove_header) {
		array_shift($result);
	}

	return $result;
}

function process_is_daemon(array $process_info) {
	$cmd = $process_info[3];

	return preg_match("/d$/", explode("/", $cmd)[0]) || preg_match("/d$/", $cmd);
}

function kill_process(int $pid) {
	shell_exec("kill $pid");
}
?>