<?php
require_once('/Applications/MAMP/htdocs/pharen/lang.php');
Lexical::$scopes['test'] = array();
function check($expr, $expected){
	Lexical::init_closure("test", 1);
	if(($expr == $expected)){
		return TRUE;
	}
	else{
		$bt = debug_backtrace();
		error("Test failed on line: ", $bt[0]["line"], " in ", $bt[0]["file"]);
		return FALSE;
	}
}

function describe($msg, $func){
	Lexical::init_closure("test", 2);
	echo(("Running tests for: " . $msg . "\n"));
	return is_string($func)?$func($func[1]):$func[0]($func[1]);
}

function it($msg, $func){
	Lexical::init_closure("test", 3);
	if($func){
		return print(("Test passed:\t " . $msg . "\n"));
	}
	else{
		return print(("Test failed:\t " . $msg . "\n"));
	}
}

