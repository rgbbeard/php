<?php
#Prevent users from navigating
if(strpos($_SERVER["REQUEST_URI"], "utils.php") > -1) {
    header("Location: https://www.google.com");
}

function array_delast($target) {
    array_pop($target);
    return $target;
}

function array_exclude($target, $element) {
    $targetPrototype = [];
    for ($x = 0; $x < sizeof($target); $x++) {
        if ($x == $element) {
            continue;
        }
        $targetPrototype[] = $target[$x];
    }
    return $targetPrototype;
}

function relpath(string $file): string {
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
    return $file;
}

function search_file(string $dir, string $file) {
    if (is_dir($dir)) {
        if ($open = opendir($dir)) {
            while (false !== $profiles = readdir($dir)) {
                if ($profiles !== "." && $profiles  !== ". .") {
                    if ($profiles ===  $file) {
                        return $profiles;
                    }
                }
            }
            closedir($open);
        }
    }
    return false;
}

function std2_array($stdclass): array {
    $temp = [];
    foreach($stdclass as $name => $value) {
        if($value instanceof stdClass) {
            $temp[$name] = std2_array($value);
        } else $temp[$name] = $value;
    }
    return $temp;
}

function get_config(string $filename, bool $decode = true) {
    $content = file_get_contents($filename);
    if($decode == true) $content = json_decode($content);
    return $content;
}

function var_name($var) {
    foreach($GLOBALS as $var_name => $value) {
        if($value === $var) {
            var_dump($var_name);
            return $var_name;
        } else return "undefined";
    }
}

function inspect(...$args) {
    for($d = 0;$d<sizeof($args);$d++) {
        $type = gettype($args[$d]);
        if(preg_match("/(boolean)/", $type)) {
            $value = boolval($args[$d]) ? true : false;
        } else $value = $args[$d];
        $name = var_name($args[$d]);
        echo strval("<pre><b><u>Name:</u></b> ".$name.PHP_EOL."<b><u>Type:</u></b> $type".PHP_EOL."<b><u>Value:</u></b> $value</pre>").PHP_EOL;
    }
}

function read_csv(string $filename, $params = [
    "explode_rows" => "\n",
    "explode_fields" => false,
    "sanitize_fields"=> false,
    "columns_names" => []
]):array {
    $csv = file_get_contents($filename);
    $erows = strval($params["explode_rows"]);
    $rows = explode($erows, $csv);
    if($params["explode_fields"] !== false) {
        $tnum = 0;
        $temp = [];
        foreach($rows as $row) {
            $efields = strval($params["explode_fields"]);
            $columns = explode($efields, $row);
            if(sizeof($params["columns_names"]) > 0) {
                for($x = 0;$x<sizeof($params["columns_names"]);$x++) {
                    if($params["sanitize_fields"] !== false && sizeof($params["sanitize_fields"]) > 0) {
                        foreach($params["sanitize_fields"] as $char => $replace) {                            
                            $columns[$x] = preg_replace($char, $replace, $columns[$x]);
                        }
                    }
                    $temp[$tnum][$params["columns_names"][$x]] = $columns[$x];
                }
            } else $temp[$tnum] = $columns[$x];
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

function is_email(string $target): bool {
    return preg_match("/([a\.\--z\_]*[a0-z9]+@)([a-z]+\.)([a-z]{2,6})/", $target) ? true : false;
}

function is_it_phone(string $target, bool $type = false) {
    if($type === true) {
        #Code to detect if it's mobile or phone
    } else return preg_match("/((3|0)([0-9]+){9,})/", $target) ? true : false;
}

function is_it_fcode(string $target): bool {
    return preg_match("/(\D{6})(\d\d)(\D\d\d)(\D\d\d)(\d\D)/", $target) ? true : false;
}

function is_it_postcode(string $target): bool {
    return preg_match("/(\d+){5}/", $target) ? true : false;
}

function is_date(string $target): bool {
    return preg_match("/([\d]+){1,2}[\-|\/]([\d]+){1,2}[\-|\/]([\d]+){4}/", $target) ? true : false;
}

function get_date(): string {
    date_default_timezone_set('UTC');
    return date("d/m/Y");
}

function get_time(): string {
    date_default_timezone_set('UTC');
    return date("G:i:s");
}

function obliviate_sql($target, $resize = {}) {
    $forbidden_words = ["select", "update", "where", "like", "delete", "alter", "date", "username", "password", "or", "and", "not"];

    $target = preg_replace("/[\=|\;|\+|\-|\*|\)|\(|\%|\/]+/", "", $target);

    $words = explode(" ", $target);

    $temp = [];

    foreach($words as $word) {
        if(!in_array($word, $forbidden_words)) {
            $temp[] = $word;
        }
    }

    $temp = implode("", $temp); 

    if(!empty($resize) && (isset($resize["min"]) && is_integer($resize["min"])) && (isset($resize["max"]) && is_integer($resize["max"]))) {
        $temp = substr($temp, $resize["min"], $resize["max"]);
    }

    return $temp;
}
