<?php
require_once('/Applications/MAMP/htdocs/pharen/lang.php');
Lexical::$scopes['test'] = array();
function check($expr, $expected){
	$__scope_id = Lexical::init_closure("test", 43);
	if(($expr == $expected)){
		return TRUE;
	}
	else{
		$bt = debug_backtrace();
	Lexical::bind_lexing("test", 43, '$bt', $bt);
		error_log(("Test failed on line: " . $bt[0]["line"] . " in " . $bt[0]["file"]));
		return FALSE;
	}
}

function describe($msg, $func){
	echo(("Running tests for: " . $msg . "\n"));
	return is_string($func)?$func():$func[0]($func[1]);
}

function it($msg, $func){
	if($func){
		return print(("Test passed:\t " . $msg . "\n"));
	}
	else{
		return print(("Test failed:\t " . $msg . "\n"));
	}
}

