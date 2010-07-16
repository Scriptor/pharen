<?php
require_once('/Applications/MAMP/htdocs/pharen/lang.php');
Lexical::$scopes['phakefile'] = array();
	$__scope_id = Lexical::init_closure("phakefile", 5);
proj("Pharen", array("description" => "Lisp -> PHP Compiler", "version" => "0.1.5"));
function test(){
	$__scope_id = Lexical::init_closure("phakefile", 3);
	$body =& Lexical::get_lexical_binding('phakefile', 4, '$body', isset($__closure_id)?$__closure_id:0);;
print(("Running " . "test" . ": " . "Run tests for Pharen compiler" . "\n"));
require((PROJECT_SYSTEM . "/examples/test/pharen_tests.php"));
return TRUE;
}

;
