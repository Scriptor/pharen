#! /usr/bin/env php
<?php
require_once('/Applications/MAMP/htdocs/pharen/lang.php');
Lexical::$scopes['test'] = array();
	$__scope_id = Lexical::init_closure("test", 35);
function check($expr, $expected){
	$__scope_id = Lexical::init_closure("test", 36);
	if(($expr == $expected)){
		return TRUE;
	}
	else{
		$bt = debug_backtrace();
		error_log(("Test failed on line: " . $bt[0]["line"] . " in " . $bt[0]["file"]));
		return FALSE;
	}
}

function describe($msg, $func){
	$__scope_id = Lexical::init_closure("test", 37);
	echo(("Running tests for: " . $msg . "\n"));
	return is_string($func)?$func():$func[0]($func[1]);
}

function it($msg, $func){
	$__scope_id = Lexical::init_closure("test", 38);
	if($func){
		return print(("Test passed:\t " . $msg . "\n"));
	}
	else{
		return print(("Test failed:\t " . $msg . "\n"));
	}
}

