<?php
require_once('C:\pharen/lang.php');
use Pharen\Lexical as Lexical;
Lexical::$scopes['test'] = array();
function check($expr, $expected){
	$__scope_id = Lexical::init_closure("test", 186);
	if(eq($expr, $expected)){
		return TRUE;
	}
	else{
		$bt = debug_backtrace();
	Lexical::bind_lexing("test", 186, '$bt', $bt);
				error_log(("Test failed on line: " . $bt[0]["line"] . " in " . $bt[0]["file"]));
		return FALSE;
	}
}

function describe($msg, $func){
	$__scope_id = Lexical::init_closure("test", 187);
	Lexical::bind_lexing("test", 187, '$func', $func);
	echo(("Running tests for: " . $msg . "\n"));
	return $func();
}

function it($msg, $func){
	if($func){
		return print(("Test passed:\t " . $msg . "\n"));
	}
	else{
		return print(("Test failed:\t " . $msg . "\n"));
	}
}

