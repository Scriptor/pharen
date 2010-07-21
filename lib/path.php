#! /usr/bin/env php
<?php
require_once('/Applications/MAMP/htdocs/pharen/lang.php');
Lexical::$scopes['path'] = array();
	$__scope_id = Lexical::init_closure("path", 45);
function path__lambdafunc4($chunk, $acc, $__closure_id){
	$__scope_id = Lexical::init_closure("path", 47);
	
	 Null;
	if(empty($chunk)){
		if(empty($acc)){
			return append($chunk, $acc);
		}
		else{
			return $acc;
		}
	}
	else if((".." == $chunk)){
		return early($acc);
	}
	else if(TRUE){
		return append($chunk, $acc);
	}
}

function path_normalize_array($chunks){
	$__scope_id = Lexical::init_closure("path", 46);


	return reduce(array("path__lambdafunc4", Lexical::get_closure_id("path", $__scope_id)), array(), $chunks);
}

function path_normalize($path){
	$__scope_id = Lexical::init_closure("path", 48);
	return implode("/", path_normalize_array(explode("/", $path)));
}

function path_join($paths){
	$__scope_id = Lexical::init_closure("path", 49);

	$paths = array_slice(func_get_args(), 0);
	return path_normalize(implode("/", $paths));
}

