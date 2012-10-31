<?php
namespace pharen\test;
require_once('/Users/historium/pharen/lang.php');
use Pharen\Lexical as Lexical;
Lexical::$scopes['test'] = array();
function check($expr, $expected, $backtrace_start=0){
	$__scope_id = Lexical::init_closure("test", 208);
	if(eq($expr, $expected)){
		return TRUE;
	}
	else{
		$bt = debug_backtrace();
	Lexical::bind_lexing("test", 208, '$bt', $bt);
				error_log(("Test failed on line: " . $bt[$backtrace_start]["line"] . " in " . $bt[$backtrace_start]["file"]));
		return FALSE;
	}
}

function describe($msg, $func){
	$__scope_id = Lexical::init_closure("test", 209);
	Lexical::bind_lexing("test", 209, '$func', $func);
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

