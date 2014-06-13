<?php
namespace pharen\debug;

if(!function_exists("pharen\\debug\\convert_line_num")){
    function convert_line_num($line_map, $errline){
        $pharen_line = 1;
        foreach($line_map as $php_line=>$ph_line){
            $pharen_line = $ph_line;
            if($php_line >= $errline){
                break;
            }
        }
        return $pharen_line;
    }
}

if(!function_exists("pharen\\debug\\generate_pharen_err")){
    function generate_pharen_err($msg, $file, $line, $php_line){
        echo "Error: $msg near $file:$line\n";
        return True;
    }
}

if(!function_exists("pharen\\debug\\error_handler")){
    function error_handler($errno, $errstr, $errfile, $errline, $errctx){
        $line_map = get_line_map($errfile);
        $pharen_file = basename($errfile, ".php").".phn";
        $pharen_line = convert_line_num($line_map, $errline);
        return generate_pharen_err($errstr, $pharen_file, $pharen_line, $errfile, $errline);
    }
}

if(!function_exists("pharen\\debug\\get_line_map")){
    function get_line_map($file){
        $dir = dirname($file);
        $base = basename($file, ".php");
        return include($dir."/".$base.".linemap.php");
    }
}

set_error_handler('pharen\debug\error_handler');
