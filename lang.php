<?php
require_once('/Applications/MAMP/htdocs/pharen/lexical.php');
Lexical::$scopes['lang'] = array();
define("SYSTEM", dirname(__FILE__));
require_once((SYSTEM . "/lexical.php"));
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

function lang__lambdafunc0($val, $acc, $__closure_id){
	$new_val_func =& Lexical::get_lexical_binding('lang', 17, '$new_val_func', isset($__closure_id)?$__closure_id:0);;
	return $acc . (is_string($new_val_func)?$new_val_func($val):$new_val_func[0]($val, $new_val_func[1]));
}

function reduce_concat($new_val_func, $xs){
	$__scope_id = Lexical::init_closure("lang", 17);
	Lexical::bind_lexing("lang", 17, '$new_val_func', $new_val_func);


	return reduce(array("lang__lambdafunc0", Lexical::get_closure_id("lang", $__scope_id)), "", $xs);
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

function lang__lambdafunc1($x, $acc, $__closure_id){
	$f =& Lexical::get_lexical_binding('lang', 20, '$f', isset($__closure_id)?$__closure_id:0);;
	return append((is_string($f)?$f($x):$f[0]($x, $f[1])), $acc);
}

function map($f, $xs){
	$__scope_id = Lexical::init_closure("lang", 20);
	Lexical::bind_lexing("lang", 20, '$f', $f);


	return reduce(array("lang__lambdafunc1", Lexical::get_closure_id("lang", $__scope_id)), array(), $xs);
}

function lang__lambdafunc2($x, $acc, $__closure_id){
	if(f1($x)){
		return cons($x, $acc);
	}
	else{
		return $acc;
	}
}

function filter($f1, $xs){


	return reduce(array("lang__lambdafunc2", Lexical::get_closure_id("lang", Null)), array(), $xs);
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

function lang__lambdafunc3($pair, $acc, $__closure_id){
	$f =& Lexical::get_lexical_binding('lang', 25, '$f', isset($__closure_id)?$__closure_id:0);;
	return append((is_string($f)?$f($pair[0], $pair[1]):$f[0]($pair[0], $pair[1], $f[1])), $acc);
}

function map_pairs($f, $pairs){
	$__scope_id = Lexical::init_closure("lang", 25);
	Lexical::bind_lexing("lang", 25, '$f', $f);


	return reduce_pairs(array("lang__lambdafunc3", Lexical::get_closure_id("lang", $__scope_id)), array(), $pairs);
}

