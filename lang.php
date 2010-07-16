<?php
require_once('/Applications/MAMP/htdocs/pharen/lexical.php');
Lexical::$scopes['lang'] = array();
	$__scope_id = Lexical::init_closure("lang", 0);
define("SYSTEM", dirname(__FILE__));
require_once((SYSTEM . "/lexical.php"));
define("LIB_PATH", (SYSTEM . "/lib/"));
set_include_path((get_include_path() . PATH_SEPARATOR . LIB_PATH));
function first($xs){
	$__scope_id = Lexical::init_closure("lang", 1);
	return $xs[0];
}

function first_pair($xs){
	$__scope_id = Lexical::init_closure("lang", 2);
	return array_slice($xs, 0, 1);
}

function rest($xs){
	$__scope_id = Lexical::init_closure("lang", 3);
	return array_slice($xs, 1);
}

function early($xs){
	$__scope_id = Lexical::init_closure("lang", 4);
	return array_slice($xs, 0, -1);
}

function take($x, $xs){
	$__scope_id = Lexical::init_closure("lang", 5);
	return array_slice($xs, 0, $x);
}

function drop($x, $xs){
	$__scope_id = Lexical::init_closure("lang", 6);
	return array_slice($xs, $x);
}

function cons($x, $xs){
	$__scope_id = Lexical::init_closure("lang", 7);
	return array_merge(array($x), $xs);
}

function append($x, $xs){
	$__scope_id = Lexical::init_closure("lang", 8);
	return array_merge($xs, array($x));
}

function apply($f, $val){
	$__scope_id = Lexical::init_closure("lang", 9);
	return is_string($f)?$f($val):$f[0]($val, $f[1]);
}

function reduce($f, $acc, $xs){
	$__scope_id = Lexical::init_closure("lang", 10);
	while(1){
		if(empty($xs)){
				return $acc;
		}
		$__tailrecursetmp0 = $f;
		$__tailrecursetmp1 = is_string($f)?$f(first($xs), $acc):$f[0](first($xs), $acc, $f[1]);
		$__tailrecursetmp2 = rest($xs);
		$f = $__tailrecursetmp0;
		$acc = $__tailrecursetmp1;
		$xs = $__tailrecursetmp2;
	}
}

function reduce_pairs($f, $acc, $xs){
	$__scope_id = Lexical::init_closure("lang", 11);
	while(1){
		if(empty($xs)){
				return $acc;
		}
		$__tailrecursetmp0 = $f;
		$__tailrecursetmp1 = is_string($f)?$f(each($xs), $acc):$f[0](each($xs), $acc, $f[1]);
		$__tailrecursetmp2 = rest($xs);
		$f = $__tailrecursetmp0;
		$acc = $__tailrecursetmp1;
		$xs = $__tailrecursetmp2;
	}
}

function lang__lambdafunc0($x, $acc, $__closure_id){
	$__scope_id = Lexical::init_closure("lang", 13);
	$f =& Lexical::get_lexical_binding('lang', 12, '$f', isset($__closure_id)?$__closure_id:0);;
	return append(is_string($f)?$f($x):$f[0]($x, $f[1]), $acc);
}

function map($f, $xs){
	$__scope_id = Lexical::init_closure("lang", 12);
	Lexical::bind_lexing("lang", 12, '$f', $f);


	return reduce(array("lang__lambdafunc0", Lexical::get_closure_id("lang", $__scope_id)), array(), $xs);
}

function lang__lambdafunc1($pair, $acc, $__closure_id){
	$__scope_id = Lexical::init_closure("lang", 15);
	$f =& Lexical::get_lexical_binding('lang', 14, '$f', isset($__closure_id)?$__closure_id:0);;
	return append(is_string($f)?$f($pair[0], $pair[1]):$f[0]($pair[0], $pair[1], $f[1]), $acc);
}

function map_pairs($f, $pairs){
	$__scope_id = Lexical::init_closure("lang", 14);
	Lexical::bind_lexing("lang", 14, '$f', $f);


	return reduce_pairs(array("lang__lambdafunc1", Lexical::get_closure_id("lang", $__scope_id)), array(), $pairs);
}

