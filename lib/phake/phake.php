<?php
require_once('/Applications/MAMP/htdocs/pharen/lang.php');
Lexical::$scopes['phake'] = array();
	$__scope_id = Lexical::init_closure("phake", 45);
require_once("path.php");
define("PHAKE_SYSTEM", realpath(dirname(__FILE__)));
define("PHAREN_SYSTEM", path_join(PHAKE_SYSTEM, "../.."));
define("PROJECT_SYSTEM", getcwd());
require_once((PHAREN_SYSTEM . "/pharen.php"));
function proj($name, $attrs){
	$__scope_id = Lexical::init_closure("phake", 46);
	return "Do stuff with project info here";
}

function project_path($f){
	$__scope_id = Lexical::init_closure("phake", 47);
	return (PROJECT_SYSTEM . $f);
}

function not_dots($dir){
	$__scope_id = Lexical::init_closure("phake", 48);
	return (("." !== $dir) and (".." !== $dir));
}

function compile_with_flag($flag, $file){
	$__scope_id = Lexical::init_closure("phake", 49);
	set_flag($flag);
	compile_file($file);
	return unset_flag($flag);
}

function is_phn($f){
	$__scope_id = Lexical::init_closure("phake", 50);
$__listAcessTmpVar2 = pathinfo($f);
$__listAcessTmpVar3 = pathinfo($f);
	return (isset($__listAcessTmpVar2["extension"]) and ($__listAcessTmpVar3["extension"] == "phn"));
}

function phake__lambdafunc4($f, $__closure_id){
	$__scope_id = Lexical::init_closure("phake", 52);
	$dir =& Lexical::get_lexical_binding('phake', 51, '$dir', isset($__closure_id)?$__closure_id:0);;
	$file = path_join($dir, $f);
	
	 Null;
	if((not_dots($f) and is_dir($file))){
		return compile_dir($file);
	}
	else if(is_phn($file)){
		return (compile_file($file, dirname($file)) and print(("Compile: " . $file . "\n")));
	}
}

function compile_dir($dir){
	$__scope_id = Lexical::init_closure("phake", 51);
	Lexical::bind_lexing("phake", 51, '$dir', $dir);


	return map(array("phake__lambdafunc4", Lexical::get_closure_id("phake", $__scope_id)), scandir($dir, 1));
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
