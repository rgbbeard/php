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
	$processes = shell_exec("ps -Al");
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
	$processes = shell_exec("ps -Al | grep $process_name");
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
	$ppid = $process_info[4];

	return intval($ppid) === 1;
}

function process_is_system(array $process_info) {
	$ppid = $process_info[4];

	return intval($ppid) === 2;
}

function process_is_vital(array $process_info) {
	$ppid = $process_info[4];

	return intval($ppid) === 0;
}

function kill_process(int $pid) {
	shell_exec("kill $pid");
}
?>
