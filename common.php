<?php 
/**
 * ci sono problemi con gli arrays
 * che hanno C/BLOBs come valori
 */
function czpbakbgqu1nnl6m(...$items) {
    $debug = "<pre style='background-color:#f003;padding:10px;'>";

    foreach($items as $item) {
        $value = print_r($item, true);

        if(is_bool($item)) {
            $debug .= "bool(" . (intval($value) ? "true" : "false") . ")\n";
        } elseif(is_numeric($item)) {
            $debug .= "number(" . $value . ")\n";
        } elseif(is_array($item)) {
            $debug .=  "array(\n" . replace(
                [
                    "{" => "{\n",
                    "}" => "\n}",
                    "[" => "[\n",
                    "]" => "\n]",
                    ",\"" => ",\n\"",
                    ",{" => ",\n{",
                    ",[" => ",\n["
                ],
                json_encode($item)
            ) . "\n)\n";
        } elseif(is_null($item)) {
            $debug .= "null\n";
        } elseif(is_string($item)) {
            $debug .= "string(\"" . $value . "\")\n";
        } elseif(is_stdclass($item)) {
            $debug .=  "stdClass(\n" . replace(
                [
                    "{" => "{\n",
                    "}" => "\n}",
                    "[" => "[\n",
                    "]" => "\n]",
                    ",\"" => ",\n\"",
                    ",{" => ",\n{",
                    ",[" => ",\n["
                ],
                json_encode(std2_array($item))
            ) . "\n)\n";
        } else {
            $debug .= $value . "\n";
        }
    }

    $debug .= "</pre>";

    return $debug;
}

/**
 * returns the call stack
 * 
 * @return string
 */
function trace() {
    $backtrace_array = array_reverse(debug_backtrace());
    $backtrace = "<pre style='background-color:#f0f3;padding:10px;'>";
    foreach($backtrace_array as $stack => $trace) {
        $file = @$trace["file"];
        $function = @$trace["function"];
        $line = @$trace["line"];
        $class = @$trace["class"];

        $tmp = "";

        if($file || $class) {
            $tmp .= "<i>";

            if($file) {
                $tmp .= "$file";
            }

            if($file && $class) {
                $tmp .= "/$class";
            } elseif($class) {
                $tmp .= "$class";
            }

            $tmp .= "</i>->";
        }

        if($function) {
            $tmp .= "<b>$function</b>";
        }

        if($line) {
            $tmp .= " at line $line";
        }

        $backtrace .= "<p style='margin:0;padding:0;font-size:13px;'>[$stack] $tmp</p>\n";
    }
    $backtrace .= "</pre>";
    return $backtrace;
}

/**
 * dumps only
 * includes trace
 * 
 * @param mixed $items
 */
function dump(...$items) {
    $debug = czpbakbgqu1nnl6m(...$items);
    echo $debug . trace();
}

/**
 * dumps without trace
 * 
 * @param mixed $items
 */
function dnt(...$items) {
    $debug = czpbakbgqu1nnl6m(...$items);
    echo $debug;
}

/**
 * dumps and exits the execution
 * 
 * @param mixed $items
 */
function dd(...$items) {
	dump(...$items);
	die();
}

/**
 * dumps and exits the execution
 * does not include trace
 * 
 * @param mixed $items
 */
function ddnt(...$items) {
    dnt(...$items);
    die();
}

/**
 * same as str_replace
 * but it allows you to replace
 * multiple characters at once
 * 
 * @param array $chars
 * @param string $target
 * @return string
 */
function replace($chars, $target) {
	foreach($chars as $char => $replacement) {
		$target = str_replace($char, $replacement, $target);
	}
	
	return $target;
}

/**
 * per risolvere $target instanceof stdClass -> false
 * quando $target è stdClass
 * 
 * @param mixed $target
 * @return bool
 */
function is_stdclass($target) {
    return !@empty($target) && !is_array($target) && get_class($target) === "stdClass";
}

/**
 * to fix compatibility issues
 * with php < 8
 */
if(!function_exists("str_contains")) {
	/**
     * @param string $container
     * @param mixed $target
	 * @return bool
	 */
	function str_contains($container, $target) {
	    return strpos(strval($container), strval($target)) > -1;
	}
}

if(!function_exists("array_first")) {
    /**
     * @param array $array
     * @return mixed
     */
    function array_first($array) {
        if(is_stdclass($array)) {
            $tmp = std2_array($array);

            $array = $tmp;
        }

        if(!empty($array) && is_array($array)) {
            return array_shift($array);
        }

        return null;
    }
}

/**
 * can be used in place of are_declared
 * 
 * @param mixed $variable
 * @param array|bool $parent
 * @param bool $check_non_empty_content
 * @return bool
 */
function is_declared($variable, $parent = false, $check_non_empty_content = true) {
	$empty_values = array("null", "0", "false");
	$is_declared = isset($variable) && !@empty($variable);

    if(is_array($variable)) {
        return are_declared($variable, $parent, $check_non_empty_content);
    } else {
    	if($parent && is_array($parent)) {
    		$is_declared = isset($parent[$variable]) && !@empty($parent[$variable]);
    	}

        if(!$check_non_empty_content) {
        	return $parent !== false ? isset($parent[$variable]) : isset($variable);
        }

        if($is_declared) {
        	return $parent !== false ?
        		!in_array(trim(strtolower($parent[$variable])), $empty_values, true) :
        		!in_array(trim(strtolower($variable)), $empty_values, true);
        }
    }

    return $is_declared;
}

/**
 * @param array $variables
 * @param array $parent
 * @param bool $check_non_empty_content
 * @return bool
 */
function are_declared($variables, $parent = false, $check_non_empty_content = true) {
    $empty_values = ["null", "0", "false"];

    foreach ($variables as $variable) {
        if(!isset($parent[$variable])) {
            return false;
        }

        if($check_non_empty_content) {
            $value = $parent[$variable];

            if(empty($value) || in_array(trim(strtolower((string) $value)), $empty_values, true)) {
                return false;
            }
        }
    }

    return true;
}

/**
 * converts a standard class
 * into a usable array
 * 
 * example: json_decode($tmp) -> stdClass
 *          std2_array($stdClass)
 * 
 * @param stdClass $stdClass
 * @return array
 */
function std2_array($stdclass) {
    $temp = array();
    foreach($stdclass as $name => $value) {
        if($value instanceof stdClass) {
            $temp[$name] = std2_array($value);
        } else {
            $temp[$name] = $value;
        }
    }
    
    return $temp;
}

/**
 * @param mixed $var
 * @return string
 */
function clear_input($var) {
    $banned_chars = array("&#60;", "&#62;");

    $var = trim($var);
    $var = stripslashes($var);
    $var = filter_var($var, FILTER_SANITIZE_STRING);
    # rimozione tags html
    $var = strip_tags($var);

    foreach($banned_chars as $c) {
        $var = str_replace($c, "", $var);
    }

    return $var;
}

/**
 * @return string
 */
function get_browser_name() {
	$user_agent = $_SERVER["HTTP_USER_AGENT"];

    if(str_contains($user_agent, 'Edge')) {
        return 'Edge';
    } elseif(str_contains($user_agent, 'Chrome')) {
        return 'Chrome';
    } elseif(str_contains($user_agent, 'Safari')) {
        return 'Safari';
    } elseif(str_contains($user_agent, 'Firefox')) {
        return 'FireFox';
    } elseif(str_contains($user_agent, 'MSIE') || str_contains($user_agent, 'Trident/7')) {
        return 'Internet Explorer';
    } elseif(str_contains($user_agent, 'Opera') || str_contains($user_agent, 'OPR/')) {
        return 'Opera';
    } else {
        return 'Altro';
    }
}

/**
 * same as str_contains
 * but it allows you to check
 * multiple strings at once
 * 
 * @param array $strings
 * @param string $target
 * @return bool
 */
function has($strings, $target) {
    $found = 0;

    if(is_array($strings)) {
        foreach($strings as $s) {
            if(str_contains($target, $s)) {
                $found++;
            }
        }
    } else {
        return str_contains($target, $strings);
    }
    
    return $found > 0;
}

/** 
 * checks the file header
 * 
 * @param string $tmpname
 * @param string $filename
 * @param bool $ignore_extension
 * @return bool
 */
function is_pdf($tmpname, $filename, $ignore_extension = false) {
    if(!file_exists($tmpname)) {
        return false;
    }

    $handle = fopen($tmpname, "rb");
    if(!$handle) {
        return false;
    }

    $first_line = fgets($handle, 20);
    if(preg_match("/\r/", $first_line)) {
        $tmp = preg_split("/\r/", $first_line);
        $first_line = $tmp[0];
    }

    fseek($handle, max(-1024, -filesize($tmpname)), SEEK_END);
    $last_chunk = fread($handle, 1024);

    fclose($handle);

    /**
     * we expect the very first line
     * to be the pdf version used to create the document
     * 
     * NOTE: not all the pdfs have the version on one line
     * 
     * example header: %PDF-1.5
     */
    if(!preg_match("/^%PDF-[0-9]\.[0-9]/", trim($first_line))) {
        return false;
    }

    # looks for %%EOF at the end of the file
    if(!preg_match("/%%EOF/", $last_chunk)) {
        return false;
    }

    if($ignore_extension) {
        return true;
    }

    return preg_match("/\.pdf$/i", $filename);
}

/**
 * @param string $tmpname
 * @param string $filename
 * @param bool $ignore_extension
 * @return bool
 */
function is_xls($tmpname, $filename, $ignore_extension = false) {
    if(!file_exists($tmpname)) {
        return false;
    }

    $body = file_get_contents($tmpname);

    if(!has(array(
        "[Content_Types].xml",
        "workbook.xml"
    ), $body)) {
        return false;
    }

    if (!has("xl/", $body)) {
        return false;
    }

    if($ignore_extension) {
        return true;
    }

    return boolval(preg_match("/\.xls[x]?$/", $filename));
}

/**
 * @param string $tmpname
 * @param string $filename
 * @param bool $ignore_extension
 * @return bool
 */
function is_doc($tmpname, $filename, $ignore_extension = false) {
    if(!file_exists($tmpname)) {
        return false;
    }

    $body = file_get_contents($tmpname);

    if(!has(array(
        "[Content_Types].xml"
    ), $body)) {
        return false;
    }

    if (!has("word/", $body)) {
        return false;
    }

    if($ignore_extension) {
        return true;
    }

    return boolval(preg_match("/\.doc[x]?$/", $filename));
}

/**
 * hides part of the string.
 * primarily used for privacy reasons
 *
 * example: blur("email@mail.com") -> "e****@mail.com"
 *
 * @param string $target
 * @param int $type
 * @param string $replace
 * @return string
 */
function blur($target, $type = 1, $replace = "*") {
    $tmp = "";
    
    switch($type) {
        # email
        case 1:
            $pieces = explode("@", $target);
            
            $user = $pieces[0];
            $domain = $pieces[1];
            
            $user = substr_replace(
                $user,
                str_repeat($replace, strlen($user)-1),
                1,
                strlen($user)
            );
            
            $tmp = "$user@$domain";
            
            break;
    }
    
    return $tmp;
}

/**
 * @return bool
 */
function isLocalhost() {
    return strpos($_SERVER["SERVER_NAME"], "localhost") === 0;
}

/**
 * @return bool
 */
function isDevelopment() {
    return strpos(getenv('SERVER_APACHE_TYPE'), "dev") === 0;
}

/**
 * @return bool
 */
function isStaging() {
    $env = getenv('SERVER_APACHE_TYPE');
    return $env === 'test' || $env === 'preprod';
}

/**
 * @return bool
 */
function isProduction() {
    $env = getenv('SERVER_APACHE_TYPE');
    return $env === 'prod' && $env === 'production';
}

if(!function_exists("try_utf8_encode")) {
    /** 
     * references:
     * https://www.php.net/manual/en/function.mb-detect-order.php
     * https://www.php.net/manual/en/function.mb-detect-encoding
     * https://www.php.net/manual/en/function.iconv.php
     * 
     * @param string $string
     * @return string|false
     */
    function try_utf8_encode($string) {
        $encoding = mb_detect_encoding($string, mb_detect_order(), true);

        return $encoding ? iconv($encoding, 'UTF-8', $string) : $string;
    }
}

/**
 * alternativa a file_get_contents
 * 
 * @param string $url
 * @param array $params
 * @param array $options
 * @param int $method
 * @param bool $dump
 * @return string
 */
function curl_get_contents(
    $url, 
    $params = array(), 
    $options = array(), 
    $method = 0,
    $dump = false
) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0
    ));

    $headers = array();

    $is_post = $method === CURLOPT_POST;

    if(!$is_post && !empty($params)) {
        $queryString = http_build_query($params);
        $url .= (strpos($url, '?') === false ? '?' : '&') . $queryString;
    }

    if($is_post) {
        curl_setopt($curl, CURLOPT_POST, true);

        if(!empty($params)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        }
    } else {
        curl_setopt($curl, CURLOPT_HTTPGET, true);
    }

    if(!empty($options["headers"])) {
        foreach ($options["headers"] as $header => $value) {
            $headers[] = "$header: $value";
        }
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
    }

    if(!empty($headers)) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    }

    $result = curl_exec($curl);

    if($e = curl_errno($curl)) {
        $m = curl_error($curl);
        var_dump($e, $m);
        die();
    }

    if($dump) {
       ddnt(curl_getinfo($curl)); 
    }

    curl_close($curl);

    return $result;
}

/**
 * @param string $filename
 * @return string
 */
function filename($filename) {
    try {
        return preg_replace("/(\.\w+)$/", "", basename($filename));
    } catch(Exception $ignore) {
        return $filename;
    }
}

/**
 * @param array $a
 * @return array
 */
function array_keys_lower($a) {
    $result = [];

    foreach($a as $key => $value) {
        if(is_array($value)) {
            $value = array_keys_lower($value);
        }

        $result[strtolower((string) $key)] = $value;
    }

    return $result;
}

/**
 * @param mixed $v
 * @return mixed
 */
function get_($v) {
    try {
        return array_keys_lower($_GET)[$v];
    } catch(Exception $ignore) {
        return null;
    }
}

/**
 * @param mixed $v
 * @return mixed
 */
function post_($v) {
    try {
        return array_keys_lower($_POST)[$v];
    } catch(Exception $ignore) {
        return null;
    }
}
