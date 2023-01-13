<?php
$salt = shell_exec('python /home/$USER/utilities/pwgen.py');

if(count($argv) >= 3 && $argv[3]) {
	if($argv[2] === "--salt") {
		$salt = preg_replace("/\s+/", "", $argv[3]);
	}
}

$salt = trim($salt);

function simple_hash($rawpw) {
	global $salt;

    $saltc = str_split($salt);
    $startc = str_split($rawpw);
    $hash = array();

    for($x = 0;$x<count($startc);$x++) {
        $hash[] = $startc[$x] . $saltc[$x];
    }

    $hashsize = count($hash);

    $hash = implode("", $hash);

    return substr(hash("sha256", $hash), 0, ($hashsize-2));
}

if(count($argv) >= 2) {
	echo "Using salt: $salt" . PHP_EOL;

	$rawpw = trim($argv[1]);

	echo "Generated hash: " . simple_hash($rawpw) . PHP_EOL;
}
?>