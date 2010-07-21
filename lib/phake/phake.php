#! /usr/bin/env php
<?php
require_once('/Applications/MAMP/htdocs/pharen/lang.php');
Lexical::$scopes['phake'] = array();
	$__scope_id = Lexical::init_closure("phake", 23);
require_once("path.php");
define("PHAKE_SYSTEM", realpath(dirname(__FILE__)));
define("PHAREN_SYSTEM", path_join(PHAKE_SYSTEM, "../.."));
define("PROJECT_SYSTEM", getcwd());
require_once((PHAREN_SYSTEM . "/pharen.php"));
function proj($name, $attrs){
	$__scope_id = Lexical::init_closure("phake", 24);
	return "Do stuff with project info here";
}

function project_path($f){
	$__scope_id = Lexical::init_closure("phake", 25);
	return (PROJECT_SYSTEM . $f);
}

function is_real_dir($dir){
	$__scope_id = Lexical::init_closure("phake", 26);
	return (("." !== $dir) and (".." !== $dir) and is_dir($dir));
}

function compile_with_flag($flag, $file){
	$__scope_id = Lexical::init_closure("phake", 27);
	set_flag($flag);
	compile_file($file);
	return unset_flag($flag);
}

function is_phn($f){
	$__scope_id = Lexical::init_closure("phake", 28);
$__listAcessTmpVar0 = pathinfo($f);
$__listAcessTmpVar1 = pathinfo($f);
	return (isset($__listAcessTmpVar0["extension"]) and ($__listAcessTmpVar1["extension"] == "phn"));
}

function phake__lambdafunc3($f, $__closure_id){
	$__scope_id = Lexical::init_closure("phake", 30);
	$dir =& Lexical::get_lexical_binding('phake', 29, '$dir', isset($__closure_id)?$__closure_id:0);;
	$file = path_join($dir, $f);
	if(is_real_dir($file)){
		return compile_dir($file);
	}
	else{
		return (compile_file($file, dirname($file)) and print(("Compile: " . $file . "\n")));
	}
}

function compile_dir($dir){
	$__scope_id = Lexical::init_closure("phake", 29);
	Lexical::bind_lexing("phake", 29, '$dir', $dir);


	return filter("is_phn", array("phake__lambdafunc3", Lexical::get_closure_id("phake", $__scope_id)), scandir($dir, 1));
}

compile_with_flag("executable", project_path("/lib/phake/phake.phn"));
compile_file(project_path("/phakefile"));
require(project_path("/phakefile.php"));
if((count($argv) > 1)){
	$__tmpfuncname0 = $argv[1];
	is_string($__tmpfuncname0)?$__tmpfuncname0():$__tmpfuncname0[0]($__tmpfuncname0[1]);
}
else{
	print("Doing nothing\n");
}
