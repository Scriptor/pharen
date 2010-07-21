#! /usr/bin/env php
<?php
require_once('/Applications/MAMP/htdocs/pharen/lang.php');
Lexical::$scopes['phakefile'] = array();
	$__scope_id = Lexical::init_closure("phakefile", 34);
proj("Pharen", array("description" => "Lisp -> PHP Compiler", "version" => "0.1.5"));
function build(){
	$__scope_id = Lexical::init_closure("phakefile", 32);
	$body =& Lexical::get_lexical_binding('phakefile', 33, '$body', isset($__closure_id)?$__closure_id:0);;
print(("Running " . "build" . ": " . "Compiling all project files written in Pharen" . "\n"));
compile_dir(project_path("/lib/"));
compile_with_flag("no-import-lang", "lang.phn");
return compile_with_flag("executable", project_path("/lib/phake/phake.phn"));
}

;
function test(){
	$__scope_id = Lexical::init_closure("phakefile", 32);
	$body =& Lexical::get_lexical_binding('phakefile', 33, '$body', isset($__closure_id)?$__closure_id:0);;
print(("Running " . "test" . ": " . "Running tests for Pharen compiler" . "\n"));
require(project_path("/examples/test/pharen_tests.php"));
return TRUE;
}

;
