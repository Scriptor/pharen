<?php
namespace pharen\debug;
function convert_line_num($line_map, $errline){
    $pharen_line = 1;
    foreach($line_map as $php_line=>$ph_line){
        if($php_line >= $errline){
            $pharen_line = $ph_line;
            break;
        }
    }
    return $pharen_line;
}

function generate_pharen_err($file, $line, $msg){
    return "$msg in $file:$line\n";
}

function error_handler($errno, $errstr, $errfile, $errline, $errctx){
    $line_map = get_line_map();
    $pharen_file = basename($errfile, ".php").".phn";
    $pharen_line = convert_line_num($line_map, $errline);
    echo generate_pharen_err($pharen_file, $pharen_line, $errstr);
    return True;
}
set_error_handler('pharen\debug\error_handler');
