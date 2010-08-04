<?php
require_once('/Applications/MAMP/htdocs/pharen/lang.php');
Lexical::$scopes['phake'] = array();

require_once("path.php");
define("PHAKE_SYSTEM", realpath(dirname(__FILE__)));
define("PHAREN_SYSTEM", path_join(PHAKE_SYSTEM, "../.."));
define("PROJECT_SYSTEM", getcwd());
require_once((PHAREN_SYSTEM . "/pharen.php"));
function proj($name, $attrs){

	return "Do stuff with project info here";
}

function project_path($f){

	return (PROJECT_SYSTEM . $f);
}

function not_dots($dir){

	return (("." !== $dir) and (".." !== $dir));
}

function compile_with_flag($flag, $file){

	set_flag($flag);
	compile_file($file);
	return unset_flag($flag);
}

function is_phn($f){

$__listAcessTmpVar2 = pathinfo($f);
$__listAcessTmpVar3 = pathinfo($f);
	return (isset($__listAcessTmpVar2["extension"]) and ($__listAcessTmpVar3["extension"] == "phn"));
}

function phake__lambdafunc1($f, $__closure_id){
	$__scope_id = Lexical::init_closure("phake", 29);
	$dir =& Lexical::get_lexical_binding('phake', 28, '$dir', isset($__closure_id)?$__closure_id:0);;
	$file = path_join($dir, $f);
	Lexical::bind_lexing("phake", 29, '$file', $file);
	
	 Null;
	if((not_dots($f) and is_dir($file))){
		return compile_dir($file);
	}
	else if(is_phn($file)){
		return (compile_file($file, dirname($file)) and print(("Compile: " . $file . "\n")));
	}
}

function compile_dir($dir){
	$__scope_id = Lexical::init_closure("phake", 28);
	Lexical::bind_lexing("phake", 28, '$dir', $dir);


	return map(array("phake__lambdafunc1", Lexical::get_closure_id("phake", $__scope_id)), scandir($dir, 1));
}

compile_with_flag("executable", project_path("/lib/phake/phake.phn"));
compile_file(project_path("/phakefile"));
require(project_path("/phakefile.php"));
if((count($argv) > 1)){
	$__tmpfuncname1 = $argv[1];
	is_string($__tmpfuncname1)?$__tmpfuncname1():$__tmpfuncname1[0]($__tmpfuncname1[1]);
}
else{
	print("Doing nothing\n");
}
