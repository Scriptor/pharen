<?php
require_once('/Applications/MAMP/htdocs/pharen/lang.php');
Lexical::$scopes['path'] = array();
function path__lambdafunc0($chunk, $acc){
	Lexical::$scopes["path"][2] = array();
	
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
	Lexical::$scopes["path"][1] = array();
	return reduce("path__lambdafunc0", array(), $chunks);
}
function path_normalize($path){
	Lexical::$scopes["path"][3] = array();
	return implode("/", path_normalize_array(explode("/", $path)));
}
function path_join($paths){
	Lexical::$scopes["path"][4] = array();

	$paths = array_slice(func_get_args(), 0);
	return path_normalize(implode("/", $paths));
}
