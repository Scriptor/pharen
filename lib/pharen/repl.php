<?php
namespace pharen_repl;
require_once('C:\pharen/lang.php');
use Pharen\Lexical as Lexical;
Lexical::$scopes['repl'] = array();
$__scope_id = Lexical::init_closure("repl", 196);
use pharen\path as path;
include_once 'pharen/path.php';
define("REPL_SYSTEM", realpath(dirname(__FILE__)));
define("PHAREN_SYSTEM", path\join(REPL_SYSTEM, "../../"));
require_once((PHAREN_SYSTEM . "/pharen.php"));
$greetings = \PharenVector::create_from_array(array("Maybe solve P v NP!", "Happy Pharening!", "(map) new worlds!", "Maybe solve Hello World!", "Curly fries are delicious and cheap!"));
Lexical::bind_lexing("repl", 196, '$greetings', $greetings);
function starts_with($needle, $haystack){
	return (substr($haystack, 0, strlen($needle)) == $needle);
}

function get_needle($input){
$__listAcessTmpVar0 = readline_info();
	$point = $__listAcessTmpVar0["point"];
	$up_to_point = substr($input, 0, $point);
	$last_pound = strrpos($up_to_point, "#");
	if(!(false__question($last_pound))){
		return substr($up_to_point, inc($last_pound));
	}
	else{
		return $input;
	}
}

	function repl__lambdafunc19($func, $__closure_id){
		return ("#" . $func);
	}
	
function prepend_chars($needle, $funcs){
		if(!(false__question(strpos($needle, "#")))){
	
	
		return map(new \PharenLambda("pharen_repl\\repl__lambdafunc19", Lexical::get_closure_id("repl", Null)), $funcs);
	}
	else{
		return $funcs;
	}

}

function repl__lambdafunc20($func, $__closure_id){
	return str_replace((\RootNode::$ns . "\\"), "", $func);
}

function strip_ns($funcs){


	return map(new \PharenLambda("pharen_repl\\repl__lambdafunc20", Lexical::get_closure_id("repl", Null)), $funcs);
}

function repl__partial0($arg0, $__closure_id){
	$needle = Lexical::get_lexical_binding('repl', 205, '$needle', isset($__closure_id)?$__closure_id:0);;
	return starts_with($needle, $arg0);
}

function pharen_complete_func($input){
		$__scope_id = Lexical::init_closure("repl", 205);
	$all_funcs = get_defined_functions();
	$needle = get_needle($input);
		Lexical::bind_lexing("repl", 205, '$needle', $needle);

	
	$starts_with_input = new \PharenLambda("pharen_repl\\repl__partial0", Lexical::get_closure_id("repl", $__scope_id));
	$internal_matches = prepend_chars($input, filter($starts_with_input, strip_ns($all_funcs["internal"])));
	$user_matches = prepend_chars($input, filter($starts_with_input, strip_ns($all_funcs["user"])));
	return arr(concat($user_matches, $internal_matches));
}

if(function_exists("readline")){
	function prompt($prompt){
		$line = trim(readline($prompt));
		readline_add_history($line);
		return $line;
	}
	
	readline_completion_function("pharen_repl\\pharen_complete_func");
}
else{
	function prompt($prompt){
		fwrite(STDOUT, $prompt);
		return trim(fgets(STDIN));
	}
	
}
function get_prompt($expecting){
	
	$__condtmpvar4 = Null;
	if($expecting){
		$__condtmpvar4 = "... ";
	}
	else{
		$__condtmpvar4 = "> ";
	}
	$suffix = $__condtmpvar4;
		if(\RootNode::$raw_ns){
		$ns = \RootNode::$raw_ns;
		return ($ns . $suffix);
	}
	else{
		return ("pharen" . $suffix);
	}

}

	function repl__lambdafunc21($ns, $__closure_id){
		if((count($ns) == 2)){
			return ("use " . $ns[0] . " as " . $ns[1] . ";\n");
		}
		else{
			return ("use " . $ns[0] . ";\n");
		}
	}
	
function add_uses($code){
	
	$__condtmpvar5 = Null;
	if(get(\RootNode::$ns, \RootNode::$uses)){
		$uses = get(\RootNode::$ns, \RootNode::$uses);
	
	
		$__condtmpvar5 = reduce_to_str(new \PharenLambda("pharen_repl\\repl__lambdafunc21", Lexical::get_closure_id("repl", Null)), $uses);
	}
	else{
		$__condtmpvar5 = "";
	}
	$use_str = $__condtmpvar5;
	return substr_replace($code, $use_str, (strpos($code, ";") + 2), 0);
}

function prn_result($x){
	
	
	$__condtmpvar6 =  Null;
	if((NULL === $x)){
		$__condtmpvar6 = "Null";
	}
	else if((TRUE === $x)){
		$__condtmpvar6 = "True";
	}
	else if((FALSE === $x)){
		$__condtmpvar6 = "False";
	}
	else if(is_object($x)){
		$__condtmpvar6 = Null;
		if(method_exists($x, "__toString")){
			$__condtmpvar6 = $x;
		}
		else{
			$__condtmpvar6 = "<" . get_class($x) . ">";
		}
	}
	else if(is_string($x)){
		$__condtmpvar6 = "\"" . $x . "\"";
	}
	else{
		$__condtmpvar6 = $x;
	}
	return prn($__condtmpvar6);
}

function phpfy_ns($ns){
	return str_replace("-", "_", str_replace(".", "_", $ns));
}

function wrap_compile($code){
	$embedded_code = ("(local *1 " . $code . ") (return *1)");
	$raw_ns = \RootNode::$raw_ns;
	\RootNode::$raw_ns = "";
	return compile($embedded_code, NULL, phpfy_ns($raw_ns), \RootNode::$last_scope);
}

function compile_code($code){
	$compiled_code = wrap_compile($code);
	if($compiled_code){
		$with_uses = add_uses($compiled_code);
		$final_code = $with_uses;
		return $final_code;
	}
	else{
		return FALSE;
	}
}

function intro(){
	$greetings = Lexical::get_lexical_binding('repl', 196, '$greetings', isset($__closure_id)?$__closure_id:0);;
	return prn(("Initialized Pharen REPL. " . $greetings[array_rand(arr($greetings))] . "\n"));
}

function work($previous_code="", $repl_vars=array()){
	while(1){
		$code = ($previous_code . " " . prompt(get_prompt($previous_code)));
			if(($code == "quit")){
				return exit(0);
			}
				$compiled_code = compile_code($code);
				if($compiled_code){
					extract($repl_vars);
					prn_result(eval(("?>" . $compiled_code)));
					$previous_code = "";
				}
				else{
					$previous_code = $code;
				}
		$__tailrecursetmp0 = $previous_code;
		$__tailrecursetmp1 = $repl_vars;
		$previous_code = $__tailrecursetmp0;
		$repl_vars = $__tailrecursetmp1;
	}
}

