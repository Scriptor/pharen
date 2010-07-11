<?php
require_once('/Applications/MAMP/htdocs/pharen/lang.php');
Lexical::$scopes['phakefile'] = array();
proj("Pharen", array("description" => "Lisp -> PHP Compiler", "version" => "0.1.5"));
	function test(){
		Lexical::$scopes["phakefile"][4] = array();
		$body =& Lexical::$scopes["phakefile"][2]['$body'];
		$name =& Lexical::$scopes["phakefile"][2]['$name'];
		$desc =& Lexical::$scopes["phakefile"][2]['$desc'];
	print(("Running " . "test" . ": " . "Run tests for Pharen compiler" . "\n"));
require((PROJECT_SYSTEM . "/examples/test/pharen_tests.php"));
return TRUE;
	}
	
;
