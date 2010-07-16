<?php
require_once('/Applications/MAMP/htdocs/pharen/lang.php');
Lexical::$scopes['phake'] = array();
	$__scope_id = Lexical::init_closure("phake", 0);
require_once("path.php");
define("PHAKE_SYSTEM", realpath(dirname(__FILE__)));
define("PHAREN_SYSTEM", path_join(PHAKE_SYSTEM, "../.."));
define("PROJECT_SYSTEM", getcwd());
require_once((PHAREN_SYSTEM . "/pharen.php"));
function proj($name, $attrs){
	$__scope_id = Lexical::init_closure("phake", 1);
	return "Do stuff with project info here";
}

compile_file((PHAKE_SYSTEM . "/phake.phn"));
compile_file((PROJECT_SYSTEM . "/phakefile"));
require((PROJECT_SYSTEM . "/phakefile.php"));
if((count($argv) > 1)){
	$__tmpfuncname0 = $argv[1];
	is_string($__tmpfuncname0)?$__tmpfuncname0($__tmpfuncname0[1]):$__tmpfuncname0[0]($__tmpfuncname0[1]);
}
else{
	print("Doing nothing\n");
}
