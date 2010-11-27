<?php
require_once('/Applications/MAMP/htdocs/pharen/lang.php');
Lexical::$scopes['repl'] = array();
require_once("path.php");
define("REPL_SYSTEM", realpath(dirname(__FILE__)));
define("PHAREN_SYSTEM", path_join(REPL_SYSTEM, "../"));
require_once((PHAREN_SYSTEM . "/pharen.php"));
function readl(){
	return trim(fgets(STDIN));
}

function evaluate($code){
	return eval(("?>" . compile($code)));
}

function work(){
	while(1){
		fwrite(STDOUT, "pharen> ");
		$code = readl();
		if(($code == "quit")){
				return exit(0);
		}
			fwrite(STDOUT, (evaluate($code) . "\n"));
	}
}

work();
