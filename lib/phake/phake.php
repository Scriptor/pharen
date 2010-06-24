#! /usr/bin/env php
<?php
require_once('/Applications/MAMP/htdocs/pharen/lang.php');
Lexical::$scopes['phake'] = array();
define("PHAKE-SYSTEM", dirname(__FILE__));
define("PHAREN-SYSTEM", dirname(realpath("./..")));
define("PROJECT-SYSTEM", getcwd());
require((PHAREN_SYSTEM . "/pharen.php"));
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
	print("Doing nothing");
}
