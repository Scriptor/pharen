<?php
require_once('C:\wamp\www\pharen\lang.php');
Lexical::$scopes['path'] = array();
function path__lambdafunc7($chunk, $acc, $__closure_id){
	
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


	return reduce(array("path__lambdafunc7", Lexical::get_closure_id("path", Null)), array(), $chunks);
}

function path_normalize($path){
	return implode("/", path_normalize_array(explode("/", $path)));
}

function path_join($paths){

	$paths = array_slice(func_get_args(), 0);
	return path_normalize(implode("/", $paths));
}

