<?php
require_once('C:\pharen\lang.php');
use Pharen\Lexical as Lexical;
Lexical::$scopes['repl'] = array();
require_once("path.php");
define("REPL_SYSTEM", realpath(dirname(__FILE__)));
define("PHAREN_SYSTEM", path_join(REPL_SYSTEM, "../"));
require_once((PHAREN_SYSTEM . "/pharen.php"));
if(function_exists("readline")){
	function prompt($prompt){
		$line = trim(readline($prompt));
		readline_add_history($line);
		return $line;
	}
	
}
else{
	function prompt($prompt){
		fwrite(STDOUT, $prompt);
		return trim(stream_get_line(STDIN, 1024, PHP_EOL));
	}
	
}
function compile_code($code){
	$embedded_code = ("(fn eval-func () " . $code . ")");
	$compiled_code = compile($embedded_code, NULL, "repl_input");
	$no_func_def = str_replace("function eval_func(){", "", $compiled_code);
	$final_code = substr($no_func_def, 0, (strrpos($no_func_def, "}") - 1));
	return $final_code;
}

function work(){
	while(1){
		$code = prompt("pharen> ");
			if(($code == "quit")){
				return exit(0);
			}
				$compiled_code = compile_code($code);
				prn(eval(("?>" . $compiled_code)));
	}
}

compile_lang();
work();
