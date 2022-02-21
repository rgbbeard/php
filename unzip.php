<?php
/*
  Minimum PHP version 7.x
  Using PHP version 8.1.1
  Author - Davide - 21/02/2022
  Git - github.com/rgbbeard
  
  USAGE
    php unzip.php /path/to/file.zip /path/to/destination/folder
*/

if(sizeof($argv) !== 3) {
	throw new Exception("PHPUnzip richiede esattamente 2 parametri:\n1)File da estrarre\n2)Percorso di destinazione");
}

$target_path = preg_replace("/\//", "\\", trim($argv[1]));
$destination_path = preg_replace("/\//", "\\", trim($argv[2]));

if(!preg_match("/\\$/", $destination_path)) {
	$destination_path .= "\\";
}

$file_name = sizeof(explode("\\", $target_path)) > 1 ? end(explode("\\", $target_path)) : $target_path;
$name = explode(".", $file_name)[0];

if(!file_exists($target_path) || !preg_match("/^(.+)(\.zip)$/", $file_name)) {
	throw new Exception("Il file da estrarre non è di un formato valido o è inesistente.\nSono accettati solo i files con estensione .zip.");
}

if(!file_exists($destination_path)) {
	try {
		mkdir($destination_path, 0777, true);
	} catch(Exception $e) {
		print_r($e->getMessage());
		die();
	}
}

$handler = new ZipArchive();

try {
	$open_zip = $handler->open($target_path);
	if($open_zip) {
		$extract_zip = $handler->extractTo($destination_path);

		if(!$extract_zip || !file_exists($destination_path)) {
			die("Sembra che il file non sia stato etratto: il risultato dell'estrazione non è presente nel percorso di destinazione specificato.");
		}

		$handler->close();
		die("Estrazione completata.");
	}
} catch(Exception $e) {
	print_r($e->getMessage());
	die();
}
?>
