<?php
function delast($target)
{
    array_pop($target);
    return $target;
}

function exclude($target, $element)
{
    $targetPrototype = [];
    for ($x = 0; $x < sizeof($target); $x++) {
        if ($x == $element) {
            continue;
        }
        $targetPrototype[] = $target[$x];
    }
    return $targetPrototype;
}

function relpath(string $file): string
{
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
        $folders = delast($folders);
        for ($x = 0; $x < $tree_length; $x++) {
            if (!empty($folders[$x])) {
                $file = "../{$file}";
            }
        }
    }
    return $file;
}

function search_file($dir, $file)
{
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
        if(is_array($value)) {
            $temp[$name] = std2_array($value);
        } else $temp[$name] = $value;
    }
    return $temp;
}

function get_config($filename, $decode = true) {
    $content = file_get_contents($filename);
    if($decode == true) $content = json_decode($content);
    return $content;
}

function var_name($var) {
    foreach($GLOBALS as $var_name => $value) {
        if($value === $var) return $var_name;
        else return "undefined";
    }
}

function inspect(...$args) {
    for($d = 0;$d<sizeof($args);$d++) {
        $type = gettype($args[$d]);
        if(preg_match("/(boolean)/", $type)) {
            $value = boolval($args[$d]) ? true : false;
        } else $value = $args[$d];
        echo strval("<pre><b>Name:</b> ".var_name($args[$d]).PHP_EOL."<b>Type:</b> $type".PHP_EOL."<b>Value:</b> $value</pre>").PHP_EOL;
    }
}