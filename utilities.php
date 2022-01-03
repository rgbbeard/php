<?php
/*
/ Minimum PHP version 7.x
/ Using PHP version 8.0.1
/ Author - Davide
/ Git - github.com/rgbbeard/
*/

define("TIMEZONE_ROME", "Europe/Rome");
define("TIMEZONE_UTC", "UTC");

function contains(string $container, $target) {
    return strpos($container, $target) > -1 ? true : false;
}

function is_declared($variable, bool $check_non_empty_content = false) {
    if($check_non_empty_content) {
        return isset($variable) && !empty($variable);
    }
    
    return isset($variable);
}

function array_delast(array $target) {
    array_pop($target);
    return $target;
}

function array_exclude(array $target, $element) {
    $temp = [];
    for ($x = 0; $x < sizeof($target); $x++) {
        if ($x == $element) {
            continue;
        }
        $temp[] = $target[$x];
    }
    
    return $temp;
}

function relpath(string $file, bool $isLocalhost = false, string $localhostBase = ""): string {
    $current_url = $_SERVER["REQUEST_URI"];
    $specified_page = preg_match("/(\.php)/", $current_url);
    $has_parameters = preg_match("/(\?)/", $current_url);
    $current_folder = basename($current_url);
    $sanitized_url = $current_url;
    #Remove parameters
    if ($has_parameters == true) {
        $sanitized_url = explode("?", $current_url)[0];
    }
    #This is the main directory
    if ($sanitized_url == "/") {
        #Nothing to do
    }
    #One or more levels above the main directory at the index page
    elseif ($sanitized_url !== "/" && !empty($current_folder) && $specified_page == false) {
        #Remove the base directory slash
        $sanitized_url = substr($sanitized_url, 1, strlen($sanitized_url) - 1);
        $folders = explode("/", $sanitized_url);
        $tree_length = sizeof($folders);
        for ($x = 0; $x < $tree_length; $x++) {
            if (!empty($folders[$x])) {
                $file = "../{$file}";
            }
        }
    }
    #One or more levels above the main directory
    elseif ($current_url !== "/" && !empty($current_folder) && $specified_page == true) {
        #Remove the base directory slash
        $sanitized_url = substr($sanitized_url, 1, strlen($sanitized_url) - 1);
        $folders = explode("/", $sanitized_url);
        $tree_length = sizeof($folders);
        #Remove page name
        $folders = array_delast($folders);
        for ($x = 0; $x < $tree_length; $x++) {
            if (!empty($folders[$x])) {
                $file = "../{$file}";
            }
        }
    }
    if($isLocalhost) {
        $localhostBase = defined("localhostBase") ? localhostBase : $localhostBase; 
        $file = $localhostBase . $file;
    }
    
    return $file;
}

function std2_array($stdclass): array {
    $temp = [];
    foreach($stdclass as $name => $value) {
        if($value instanceof stdClass) {
            $temp[$name] = std2_array($value);
        } else {
            $temp[$name] = $value;
        }
    }
    
    return $temp;
}

function get_config(string $filename, bool $decode = true) {
    $content = file_get_contents($filename);
    
    if($decode == true) {
        $content = json_decode($content);
    }
    
    return $content;
}

function var_name($var) {
    foreach($GLOBALS as $var_name => $value) {
        if($value === $var) {
            var_dump($var_name);
            return $var_name;
        }
        
        return "undefined";
    }
}

function inspect(...$args) {
    for($d = 0;$d<sizeof($args);$d++) {
        $type = gettype($args[$d]);
        
        if(preg_match("/(boolean)/", $type)) {
            $value = boolval($args[$d]) ? true : false;
        } else {
            $value = $args[$d];
        }
        
        $name = var_name($args[$d]);
        echo strval("<pre><b><u>Name:</u></b> ".$name.PHP_EOL."<b><u>Type:</u></b> $type".PHP_EOL."<b><u>Value:</u></b> $value</pre>").PHP_EOL;
    }
}

function read_csv(string $filename, $params = [
    "explode_rows" => "\n",
    "explode_fields" => false,
    "sanitize_fields"=> [
        "/\'/" => "\'",
        "/\"/" => "\"",
        "/\;/" => "\;"
    ],
    "columns_names" => array()
]):array {
    $csv = "";
    
    try {
        $csv = file_get_contents($filename);
    } catch(Exception $e) {
        print_r($e->getMessage());
    }

    $erows = strval($params["explode_rows"]);
    $rows = explode($erows, $csv);

    if(isset($params["explode_fields"]) && gettype($params["explode_fields"]) == "string") {
        $tnum = 0;
        $temp = array();
       
        foreach($rows as $row) {
            $efields = strval($params["explode_fields"]);
            $columns = explode($efields, $row);
            
            if(isset($params["columns_names"]) && gettype($params["columns_names"]) == "array" && sizeof($params["columns_names"]) > 0) { #Assign columns names
                for($x = 0;$x<sizeof($params["columns_names"]);$x++) {
                    if(isset($params["sanitize_fields"]) && $params["sanitize_fields"] !== false && gettype($params["sanitize_fields"]) == "array" && sizeof($params["sanitize_fields"]) > 0) {
                        foreach($params["sanitize_fields"] as $char => $replace) {                            
                            $columns[$x] = @preg_replace($char, $replace, $columns[$x]);
                        }
                    }
                    $temp[$tnum][$params["columns_names"][$x]] = $columns[$x];
                }
            } else { #Just put columns in the array as they are
                $temp[$tnum] = $columns;
            }
            
            $tnum++;
        }
        
        return $temp;
    }
    
    return $rows;
}

function capitalize(string $target): string {
    $words = explode(" ", $target);
    $temp = [];
    
    foreach($words as $word) {
        $word = strtolower($word);
        $temp[] = ucfirst($word);
    }
    
    return implode(" ", $temp);
}

function is_email(string $target) {
    return preg_match("/([a\.\--z\_]*[a0-z9]+@)([a-z]+\.)([a-z]{2,6})/", $target);
}

function is_it_phone(string $target, bool $type = false) {
    if($type === true) {
        #Code to detect if it's mobile or phone
    }
    
    return preg_match("/((3|0)([0-9]+){9,})/", $target);
}

function is_it_fcode(string $target) {
    return preg_match("/([\D]{6}[0-9]{2}(\D[0-9]{2}){2}\d\D){1}/", $target);
}

function is_it_postcode(string $target) {
    return preg_match("/(\d+){5}/", $target);
}

function is_date(string $target) {
    return preg_match("/([\d]+){1,2}[\-|\/]([\d]+){1,2}[\-|\/]([\d]+){4}/", $target);
}

function set_timezone(string $timezonename = TIMEZONE_UTC) {
    date_default_timezone_set($timezonename);
}

function get_date(string $timezone = TIMEZONE_UTC, string $separator = "/"): string {
    date_default_timezone_set($timezone);
    return date("d{$separator}m{$separator}Y");
}

function get_time(string $timezone = TIMEZONE_UTC, string $separator = ":"): string {
    date_default_timezone_set($timezone);
    return date("G{$separator}i{$separator}s");
}

function is_negative($num = 0) {
	if(!is_numeric($num)) {
		return null;
	}
	
	return preg_match("/^-/", strval($num*-1));
}

function sum(...$args) {
	$temp = [];

	foreach($args as $num) {
		if(is_numeric($num)) {
			$temp[] = $num;
		}
	}

	return array_sum($temp);
}

function sub(bool $to_int = false, ...$args) {
	$temp = @isset($args[0]) && is_numeric($args[0]) ? floatval($args[0]) : 0;

	for($x = 1;$x<sizeof($args);$x++) {
		$num = $args[$x];

		if(is_numeric($num)) {
			if(!preg_match("/^-/", strval($num))) {
				$num *= -1; # Make the number negative
			}
			
			$temp += floatval($num);
		}
	}

	return $to_int ? intval($temp) : $temp;
}

function get_x_year(int $years = 0) {
	$dd = intval(date("j"));
	$mm = intval(date("n"));
	$yy = is_negative($years) ? sub(true, intval(date("Y")), $years) : sum(intval(date("Y")), $years);
	$h = intval(preg_replace("/^0/", "", date("H")));
	$m = intval(preg_replace("/^0/", "", date("i")));
	$s = intval(preg_replace("/^0/", "", date("s")));

	return date("Y", mktime($h, $m, $s, $mm, $dd, $yy));
}

function get_x_month(int $months = 0, bool $get_name = false) {
	$dd = intval(date("j"));
	$mm = intval(date("n"));
	$yy = is_negative($months) ? sub(true, intval(date("Y")), $months) : sum(intval(date("Y")), $months);
	$h = intval(preg_replace("/^0/", "", date("H")));
	$m = intval(preg_replace("/^0/", "", date("i")));
	$s = intval(preg_replace("/^0/", "", date("s")));

	$formatter = $get_name ? "F" : "m";
	$month = date($formatter, mktime($h, $m, $s, $mm, $dd, $yy));

	return $month;
}

function clear_sql_keywords(string $target, array $resize = []): string {
    $forbidden_words = ["select", "update", "where", "like", "delete", "alter", "date", "drop", "use", "or", "and", "not", "use", "=", ";", "+", "-", "*", ")", "(", "%", "/", "]", "[", "{", "}"];

    foreach($forbidden_words as $word) {
        $target = str_replace($word, "", $target);
    }

    if(!empty($resize) && (isset($resize["min"]) && is_integer($resize["min"])) && (isset($resize["max"]) && is_integer($resize["max"]))) {
        $target = substr($target, $resize["min"], $resize["max"]);
    }

    return $target;
}

function simple_hash($rawpw) {
    $salt = ""; #Here goes your hash key
    $saltc = str_split($salt);
    $startc = str_split($rawpw);
    $hash = array();

    for($x = 0;$x<sizeof($startc);$x++) {
        $hash[] = $startc[$x] . $saltc[$x];
    }

    $hashsize = sizeof($hash);

    $hash = implode("", $hash);

    return substr(base64_encode($hash), 0, ($hashsize-2));
}

function timeout_location(string $location, int $timeout = 0) {
    header("refresh: $timeout;url=" . relpath($location));
}

function write_log(string $logfile, $logcontent, bool $userelativepath = false) {
    if($userelativepath) {
        $logfile = relpath($logfile);
    }

    $time = get_time(TIMEZONE_ROME);
    $date = get_date(TIMEZONE_ROME, "-");

    $logcontent = strval("[$date\t $time]\t " . $logcontent . "\n");

    file_put_contents($logfile, $logcontent, FILE_APPEND);
}

########
# BETA #
########
function bin2_ascii(string $bin) {
    $ascii  = "";
    
    for($x = 0;$x<strlen($bin);$x++) {
        $ascii .= chr(intval(substr($bin, $x, 8), 2));
    }
    
    return $ascii;
}
?>
