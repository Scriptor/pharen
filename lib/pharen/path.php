<?php
namespace pharen\path;
require_once('/Users/historium/pharen/lang.php');
use Pharen\Lexical as Lexical;
Lexical::$scopes['path'] = array();
function path__lambdafunc22($chunk, $acc, $__closure_id){
	
	 Null;
	if(empty($chunk)){
		if(empty__question($acc)){
			return cons($chunk, $acc);
		}
		else{
			return $acc;
		}
	}
	else if((".." == $chunk)){
		return rest($acc);
	}
	else{
		return cons($chunk, $acc);
	}
}

function path_normalize_array($chunks){


	return reverse(reduce(new \PharenLambda("pharen\path\\path__lambdafunc22", Lexical::get_closure_id("path", Null)), \PharenVector::create_from_array(array()), $chunks));
}

function path_normalize($path){
	return seq_join(path_normalize_array(explode("/", $path)), "/");
}

function convert_slashes($path){
	return str_replace("\\", "/", $path);
}

function join($paths){

	$paths = seq(array_slice(func_get_args(), 0));
	return path_normalize(convert_slashes(seq_join($paths, "/")));
}

