<?php
require_once(dirname(__FILE__).'/lexical.php');
Lexical::$scopes['lang'] = array();
define("SYSTEM", dirname(__FILE__));
define("LIB_PATH", (SYSTEM . "/lib/"));
set_include_path((get_include_path() . PATH_SEPARATOR . LIB_PATH));
function first($xs){
	return $xs[0];
}

function first_pair($xs){
	return array_slice($xs, 0, 1);
}

function rest($xs){
	return array_slice($xs, 1);
}

function early($xs){
	return array_slice($xs, 0, -1);
}

function take($x, $xs){
	return array_slice($xs, 0, $x);
}

function drop($x, $xs){
	return array_slice($xs, $x);
}

function cons($x, $xs){
	return array_merge(array($x), $xs);
}

function append($x, $xs){
	return array_merge($xs, array($x));
}

function apply($f, $val){
	return (is_string($f)?$f($val):$f[0]($val, $f[1]));
}

function reduce($f, $acc, $xs){
	while(1){
		if(empty($xs)){
				return $acc;
		}
		$__tailrecursetmp0 = $f;
		$__tailrecursetmp1 = (is_string($f)?$f(first($xs), $acc):$f[0](first($xs), $acc, $f[1]));
		$__tailrecursetmp2 = rest($xs);
		$f = $__tailrecursetmp0;
		$acc = $__tailrecursetmp1;
		$xs = $__tailrecursetmp2;
	}
}

function lang__lambdafunc1($val, $acc, $__closure_id){
	$new_val_func =& Lexical::get_lexical_binding('lang', 25, '$new_val_func', isset($__closure_id)?$__closure_id:0);;
	return $acc . (is_string($new_val_func)?$new_val_func($val):$new_val_func[0]($val, $new_val_func[1]));
}

function reduce_concat($new_val_func, $xs){
	$__scope_id = Lexical::init_closure("lang", 25);
	Lexical::bind_lexing("lang", 25, '$new_val_func', $new_val_func);


	return reduce(array("lang__lambdafunc1", Lexical::get_closure_id("lang", $__scope_id)), "", $xs);
}

function reduce_pairs($f, $acc, $xs){
	while(1){
		if(empty($xs)){
				return $acc;
		}
		$__tailrecursetmp0 = $f;
		$__tailrecursetmp1 = (is_string($f)?$f(each($xs), $acc):$f[0](each($xs), $acc, $f[1]));
		$__tailrecursetmp2 = rest($xs);
		$f = $__tailrecursetmp0;
		$acc = $__tailrecursetmp1;
		$xs = $__tailrecursetmp2;
	}
}

function lang__lambdafunc2($x, $acc, $__closure_id){
	$f =& Lexical::get_lexical_binding('lang', 28, '$f', isset($__closure_id)?$__closure_id:0);;
	return append((is_string($f)?$f($x):$f[0]($x, $f[1])), $acc);
}

function map($f, $xs){
	$__scope_id = Lexical::init_closure("lang", 28);
	Lexical::bind_lexing("lang", 28, '$f', $f);


	return reduce(array("lang__lambdafunc2", Lexical::get_closure_id("lang", $__scope_id)), array(), $xs);
}

function lang__lambdafunc3($x, $acc, $__closure_id){
	if(f1($x)){
		return cons($x, $acc);
	}
	else{
		return $acc;
	}
}

function filter($f1, $xs){


	return reduce(array("lang__lambdafunc3", Lexical::get_closure_id("lang", Null)), array(), $xs);
}

function for_n($x, $f, $acc){
	while(1){
		if((0 == $x)){
				return $acc;
		}
		$__tailrecursetmp0 = ($x - 1);
		$__tailrecursetmp1 = $f;
		$__tailrecursetmp2 = (is_string($f)?$f($acc):$f[0]($acc, $f[1]));
		$x = $__tailrecursetmp0;
		$f = $__tailrecursetmp1;
		$acc = $__tailrecursetmp2;
	}
}

function until($f, $xs){
	while(1){
		
		 Null;
		if(empty($xs)){
				return FALSE;
		}
		else if($result = (is_string($f)?$f(first($xs)):$f[0](first($xs), $f[1]))){
				return $result;
		}
		$__tailrecursetmp0 = $f;
		$__tailrecursetmp1 = rest($xs);
		$f = $__tailrecursetmp0;
		$xs = $__tailrecursetmp1;
	}
}

function lang__lambdafunc4($pair, $acc, $__closure_id){
	$f =& Lexical::get_lexical_binding('lang', 34, '$f', isset($__closure_id)?$__closure_id:0);;
	return append((is_string($f)?$f($pair[0], $pair[1]):$f[0]($pair[0], $pair[1], $f[1])), $acc);
}

function map_pairs($f, $pairs){
	$__scope_id = Lexical::init_closure("lang", 34);
	Lexical::bind_lexing("lang", 34, '$f', $f);


	return reduce_pairs(array("lang__lambdafunc4", Lexical::get_closure_id("lang", $__scope_id)), array(), $pairs);
}

class MultiManager{
	static $multis = array();
	static function matching_multi_exists($multi_name, $serialized_args){
		return isset(self::$multis[$multi_name][$serialized_args]);
	}
	
	static function get_matching_multi($multi_name, $serialized_args){
		return self::$multis[$multi_name][$serialized_args];
	}
	
	static function set_multi($multi_name, $pattern, $f){
		return self::$multis[$multi_name][$pattern] = $f;
	}
	
}
function lang__lambdafunc5($val, $__closure_id){
	
	 Null;
	if(is_string($val)){
		return "str";
	}
	else if(is_int($val)){
		return "int";
	}
	else if(is_float($val)){
		return "float";
	}
	else if(is_bool($val)){
		return "bool";
	}
	else if(is_array($val)){
		if(isset($val["_multitype"])){
			return $val["_multitype"];
		}
		else{
			return "5";
		}
	}
	else if(is_object($val)){
		return get_class($val);
	}
}

function multi_serialize_args($vals){


	return reduce_concat(array("lang__lambdafunc5", Lexical::get_closure_id("lang", Null)), $vals);
}

function multi_serialize_pattern($pattern){
	return implode($pattern);
}

function get_multi($name, $args){
	$serialized_args = multi_serialize_args($args);
	if(MultiManager::matching_multi_exists($name, $serialized_args)){
		return MultiManager::get_matching_multi($name, $serialized_args);
	}
	else{
		return "No matching pattern";
	}
}

