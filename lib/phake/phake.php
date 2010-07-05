<?php
require_once('/Applications/MAMP/htdocs/pharen/lang.php');
Lexical::$scopes['phake'] = array();
require_once("path.php");
define("PHAKE_SYSTEM", realpath(dirname(__FILE__)));
define("PHAREN_SYSTEM", path_join(PHAKE_SYSTEM, "../.."));
define("PROJECT_SYSTEM", getcwd());
require_once((PHAREN_SYSTEM . "/pharen.php"));
function proj($name, $attrs){
	Lexical::$scopes["phake"][1] = array();
	return "Do stuff with project info here";
}
compile_file((PHAKE_SYSTEM . "/phake.phn"));
compile_file((PROJECT_SYSTEM . "/phakefile"));
require((PROJECT_SYSTEM . "/phakefile.php"));
if((count($argv) > 1)){
	$__tmpfuncname0 = $argv[1];
	$__tmpfuncname0();
}
else{
	print("Doing nothing\n");
}
