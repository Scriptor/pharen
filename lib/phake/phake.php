#! /usr/bin/env php
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
	return PROJECT_SYSTEM . $f;
}

function not_dots($dir){


$__condtmpvar0 =  Null;
if(!(("." !== $dir))){
$__condtmpvar0 = ("." !== $dir);
}
else if(!((".." !== $dir))){
$__condtmpvar0 = (".." !== $dir);
}
else if(TRUE){
$__condtmpvar0 = (".." !== $dir);
}
	return $__condtmpvar0;
}

function compile_with_flag($flag, $file){
	set_flag($flag);
	compile_file($file);
	return unset_flag($flag);
}

function compile_except($excepts, $file, $output_dir=NULL){

$__condtmpvar1 = Null;
if(!(in_array($file, $excepts))){
	$__condtmpvar1 = compile_file($file);
}
else{
$__condtmpvar1 = FALSE;
}
	return $__condtmpvar1;
}

function is_phn($f){


$__condtmpvar2 =  Null;
if(!(isset($__listAcessTmpVar0["extension"]))){
$__condtmpvar2 = isset($__listAcessTmpVar1["extension"]);
}
else if(!(($__listAcessTmpVar2["extension"] == "phn"))){
$__condtmpvar2 = ($__listAcessTmpVar3["extension"] == "phn");
}
else if(TRUE){
$__condtmpvar2 = ($__listAcessTmpVar4["extension"] == "phn");
}
$__listAcessTmpVar2 = pathinfo($f);
$__listAcessTmpVar3 = pathinfo($f);
$__listAcessTmpVar4 = pathinfo($f);
	return $__condtmpvar2;
}

function phake__lambdafunc7($f, $__closure_id){
	$__scope_id = Lexical::init_closure("phake", 64);
	$dir =& Lexical::get_lexical_binding('phake', 63, '$dir', isset($__closure_id)?$__closure_id:0);;
	$compile_func =& Lexical::get_lexical_binding('phake', 63, '$compile_func', isset($__closure_id)?$__closure_id:0);;
	$file = path_join($dir, $f);
	Lexical::bind_lexing("phake", 64, '$file', $file);
	
	 Null;
	if($__condtmpvar3){


$__condtmpvar3 =  Null;
if(!(not_dots($f))){
$__condtmpvar3 = not_dots($f);
}
else if(!(is_dir($file))){
$__condtmpvar3 = is_dir($file);
}
else if(TRUE){
$__condtmpvar3 = is_dir($file);
}
		return compile_dir($file, $compile_func);
	}
	else if(is_phn($file)){


$__condtmpvar4 =  Null;
if(!((is_string($compile_func)?$compile_func($file, dirname($file)):$compile_func[0]($file, dirname($file), $compile_func[1])))){
$__condtmpvar4 = (is_string($compile_func)?$compile_func($file, dirname($file)):$compile_func[0]($file, dirname($file), $compile_func[1]));
}
else if(!(print(("Compile: " . $file . "\n")))){
$__condtmpvar4 = print(("Compile: " . $file . "\n"));
}
else if(TRUE){
$__condtmpvar4 = print(("Compile: " . $file . "\n"));
}
		return $__condtmpvar4;
	}
}

function compile_dir($dir, $compile_func="compile_file"){
	$__scope_id = Lexical::init_closure("phake", 63);
	Lexical::bind_lexing("phake", 63, '$dir', $dir);
	Lexical::bind_lexing("phake", 63, '$compile_func', $compile_func);


	return map(array("phake__lambdafunc7", Lexical::get_closure_id("phake", $__scope_id)), scandir($dir, 1));
}

compile_with_flag("executable", project_path("/lib/phake/phake.phn"));
compile_file(project_path("/phakefile"));
require(project_path("/phakefile.php"));
if((count($argv) > 1)){
	$__tmpfuncname1 = $argv[1];
	(is_string($__tmpfuncname1)?$__tmpfuncname1():$__tmpfuncname1[0]($__tmpfuncname1[1]));
}
else{
	print("Doing nothing\n");
}
