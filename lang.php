<?php
require_once('/Applications/MAMP/htdocs/pharen/lexical.php');
Lexical::$scopes['lang'] = array();
define("SYSTEM", dirname(__FILE__));
require_once((SYSTEM . "/lexical.php"));
define("LIB_PATH", (SYSTEM . "/lib/"));
set_include_path((get_include_path() . PATH_SEPARATOR . LIB_PATH));
function first($xs){
	Lexical::$scopes["lang"][1] = array();
	return $xs[0];
}

function first_pair($xs){
	Lexical::$scopes["lang"][2] = array();
	return array_slice($xs, 0, 1);
}

function rest($xs){
	Lexical::$scopes["lang"][3] = array();
	return array_slice($xs, 1);
}

function early($xs){
	Lexical::$scopes["lang"][4] = array();
	return array_slice($xs, 0, -1);
}

function take($x, $xs){
	Lexical::$scopes["lang"][5] = array();
	return array_slice($xs, 0, $x);
}

function drop($x, $xs){
	Lexical::$scopes["lang"][6] = array();
	return array_slice($xs, $x);
}

function cons($x, $xs){
	Lexical::$scopes["lang"][7] = array();
	return array_merge(array($x), $xs);
}

function append($x, $xs){
	Lexical::$scopes["lang"][8] = array();
	return array_merge($xs, array($x));
}

function apply($f, $val){
	Lexical::$scopes["lang"][9] = array();
	return $f($val);
}

function reduce($f, $acc, $xs){
	Lexical::$scopes["lang"][10] = array();
	while(1){
		if(empty($xs)){
				return $acc;
		}
		$__tailrecursetmp0 = $f;
		$__tailrecursetmp1 = $f(first($xs), $acc);
		$__tailrecursetmp2 = rest($xs);
		$f = $__tailrecursetmp0;
		$acc = $__tailrecursetmp1;
		$xs = $__tailrecursetmp2;
	}
}

function reduce_pairs($f, $acc, $xs){
	Lexical::$scopes["lang"][11] = array();
	while(1){
		if(empty($xs)){
				return $acc;
		}
		$__tailrecursetmp0 = $f;
		$__tailrecursetmp1 = $f(each($xs), $acc);
		$__tailrecursetmp2 = rest($xs);
		$f = $__tailrecursetmp0;
		$acc = $__tailrecursetmp1;
		$xs = $__tailrecursetmp2;
	}
}

function lang__lambdafunc0($x, $acc){
	Lexical::$scopes["lang"][13] = array();
	$f =& Lexical::$scopes["lang"][12]['$f'];
	return append($f($x), $acc);
}


function map($f, $xs){
	Lexical::$scopes["lang"][12] = array();
	Lexical::$scopes["lang"][12]['$f'] =& $f;
	return reduce("lang__lambdafunc0", array(), $xs);
}

function lang__lambdafunc1($pair, $acc){
	Lexical::$scopes["lang"][15] = array();
	$f =& Lexical::$scopes["lang"][14]['$f'];
	return append($f($pair[0], $pair[1]), $acc);
}


function map_pairs($f, $pairs){
	Lexical::$scopes["lang"][14] = array();
	Lexical::$scopes["lang"][14]['$f'] =& $f;
	return reduce_pairs("lang__lambdafunc1", array(), $pairs);
}

