<?php
require_once('C:\wamp\www\pharen\lang.php');
Lexical::$scopes['path'] = array();
function path__lambdafunc7($chunk, $acc, $__closure_id){
	
	 Null;
	if(empty($chunk)){
		if(empty__question($acc)){
			return seq($acc)->cons($chunk);
		}
		else{
			return $acc;
		}
	}
	else if((".." == $chunk)){
		return seq($acc)->rest;
	}
	else if(TRUE){
		return seq($acc)->cons($chunk);
	}
}

function path_normalize_array($chunks){


	return reverse(reduce(array("path__lambdafunc7", Lexical::get_closure_id("path", Null)), array(), $chunks));
}

function path_normalize($path){
	return seq_join(path_normalize_array(explode("/", $path)), "/");
}

function convert_slashes($path){
	return str_replace("\\", "/", $path);
}

function path_join($paths){

	$paths = array_slice(func_get_args(), 0);
	return path_normalize(convert_slashes(seq_join($paths, "/")));
}

