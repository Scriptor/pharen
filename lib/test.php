<?php
require_once('/Applications/MAMP/htdocs/pharen/lang.php');
Lexical::$scopes['test'] = array();
function check($expr, $expected){
	Lexical::$scopes["test"][1] = array();
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
	Lexical::$scopes["test"][2] = array();
	echo(("Running tests for: " . $msg . "\n"));
	return $func();
}
function it($msg, $func){
	Lexical::$scopes["test"][3] = array();
	if($func){
		return print(("Test passed:\t " . $msg . "\n"));
	}
	else{
		return print(("Test failed:\t " . $msg . "\n"));
	}
}
