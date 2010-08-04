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

	return is_string($f)?$f($val):$f[0]($val, $f[1]);
}

function reduce($f, $acc, $xs){

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

	$f =& Lexical::get_lexical_binding('lang', 16, '$f', isset($__closure_id)?$__closure_id:0);;
	return append(is_string($f)?$f($x):$f[0]($x, $f[1]), $acc);
}

function map($f, $xs){
	$__scope_id = Lexical::init_closure("lang", 16);
	Lexical::bind_lexing("lang", 16, '$f', $f);


	return reduce(array("lang__lambdafunc0", Lexical::get_closure_id("lang", $__scope_id)), array(), $xs);
}

function lang__lambdafunc1($x, $__closure_id){

	$f1 =& Lexical::get_lexical_binding('lang', 18, '$f1', isset($__closure_id)?$__closure_id:0);;
	$f2 =& Lexical::get_lexical_binding('lang', 18, '$f2', isset($__closure_id)?$__closure_id:0);;

$__condtmpvar2 = Null;
if(is_string($f1)?$f1($x):$f1[0]($x, $f1[1])){
	$__condtmpvar2 = is_string($f2)?$f2($x):$f2[0]($x, $f2[1]);
}
else{
$__condtmpvar2 = FALSE;
}
	return $__condtmpvar2;
}

function filter($f1, $f2, $xs){
	$__scope_id = Lexical::init_closure("lang", 18);
	Lexical::bind_lexing("lang", 18, '$f1', $f1);
	Lexical::bind_lexing("lang", 18, '$f2', $f2);


	return map(array("lang__lambdafunc1", Lexical::get_closure_id("lang", $__scope_id)), $xs);
}

function for_n($x, $f, $acc){

	while(1){
		if((0 == $x)){
				return $acc;
		}
		$__tailrecursetmp0 = ($x - 1);
		$__tailrecursetmp1 = $f;
		$__tailrecursetmp2 = is_string($f)?$f($acc):$f[0]($acc, $f[1]);
		$x = $__tailrecursetmp0;
		$f = $__tailrecursetmp1;
		$acc = $__tailrecursetmp2;
	}
}

function lang__lambdafunc2($pair, $acc, $__closure_id){

	$f =& Lexical::get_lexical_binding('lang', 21, '$f', isset($__closure_id)?$__closure_id:0);;
	return append(is_string($f)?$f($pair[0], $pair[1]):$f[0]($pair[0], $pair[1], $f[1]), $acc);
}

function map_pairs($f, $pairs){
	$__scope_id = Lexical::init_closure("lang", 21);
	Lexical::bind_lexing("lang", 21, '$f', $f);


	return reduce_pairs(array("lang__lambdafunc2", Lexical::get_closure_id("lang", $__scope_id)), array(), $pairs);
}

