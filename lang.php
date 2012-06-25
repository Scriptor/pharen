<?php
require_once(dirname(__FILE__).'\lexical.php');
use Pharen\Lexical as Lexical;
Lexical::$scopes['lang'] = array();
define("SYSTEM", dirname(__FILE__));
define("LIB_PATH", (SYSTEM . "/lib/"));
set_include_path((get_include_path() . PATH_SEPARATOR . LIB_PATH));
require("sequence.php");
function prn($s){
	return print(($s . "\n"));
}

function zero__question($n){
	return ($n == 0);
}

function pos__question($n){
	return ($n > 0);
}

function neg__question($n){
	return ($n < 0);
}

function str($s1, $s2){
	return ($s1 . $s2);
}

function flip($f, $arg1, $arg2){
	return (is_string($f)?$f($arg2, $arg1):$f[0]($arg2, $arg1, $f[1]));
}

function zero_or_empty__question($n, $xs){
	
	$__condtmpvar0 = Null;
	if(zero__question($n)){
		$__condtmpvar0 = zero__question($n);
	}
	else{
		$__condtmpvar0 = empty__question($xs);
	}
	return $__condtmpvar0;
}

function empty__question($xs){
	
	$__condtmpvar1 = Null;
	if(($xs instanceof PharenEmptyList)){
		$__condtmpvar1 = 		$xs instanceof PharenEmptyList;
	}
	else{
		$__condtmpvar1 = empty($xs);
	}
	return $__condtmpvar1;
}

function seq__question($x){
	
	$__condtmpvar2 = Null;
	if(is_array($x)){
		$__condtmpvar2 = is_array($x);
	}
	else{
		$__condtmpvar2 = 		$x instanceof IPharenSeq;
	}
	return $__condtmpvar2;
}

function seq($x){
	if(($x instanceof IPharenSeq)){
		return $x->seq();
	}
	else{
		return PharenList::seqify($x);
	}
}

function hashify($x){
	if(($x instanceof PharenHashMap)){
		return $x;
	}
	else{
		return new PharenHashMap($x);
	}
}

function lang__lambdafunc1($pair, $hm, $__closure_id){
	return assoc($pair[0], $pair[1], $hm);
}

function hash_from_pairs($pairs){


	return reduce(array("\lang__lambdafunc1", Lexical::get_closure_id("lang", Null)), array(), $pairs);
}

function arr($x){
	
	 Null;
	if(is_array($x)){
		return $x;
	}
	else if(($x instanceof IPharenSeq)){
		return $x->arr();
	}
}

function first_pair($xs){
	return array_slice($xs, 0, 1);
}

function cons($x, $xs){
	return seq($xs)->cons($x);
}

function assoc($key, $val, $hm){
	return hashify($hm)->assoc($key, $val);
}

function take($n, $xs){
	if(zero_or_empty__question($n, $xs)){
		return array();
	}
	else{
		return cons(seq($xs)->first(), take(($n - 1), seq($xs)->rest()));
	}
}

function drop($n, $xs){
	while(1){
		if(zero_or_empty__question($n, $xs)){
				return $xs;
		}
		$__tailrecursetmp0 = ($n - 1);
		$__tailrecursetmp1 = seq($xs)->rest();
		$n = $__tailrecursetmp0;
		$xs = $__tailrecursetmp1;
	}
}

function reverse($xs, $acc=array()){
	while(1){
		if(empty__question($xs)){
				return $acc;
		}
		$__tailrecursetmp0 = seq($xs)->rest();
		$__tailrecursetmp1 = cons(seq($xs)->first(), $acc);
		$xs = $__tailrecursetmp0;
		$acc = $__tailrecursetmp1;
	}
}

function interpose($sep, $xs, $acc=array()){
	if((count($xs) == 1)){
		return array(seq($xs)->first());
	}
	else{
		return cons(seq($xs)->first(), cons($sep, interpose($sep, seq($xs)->rest())));
	}
}

function partition($n, $xs){
	if(empty__question($xs)){
		return $xs;
	}
	else{
		return cons(take($n, $xs), partition($n, drop($n, $xs)));
	}
}

function zip_with($f, $xs, $ys){
		
		$__condtmpvar3 = Null;
		if(empty__question($xs)){
			$__condtmpvar3 = empty__question($xs);
		}
		else{
			$__condtmpvar3 = empty__question($ys);
		}
	if($__condtmpvar3){
		return array();
	}
	else{
		return cons((is_string($f)?$f(seq($xs)->first(), seq($ys)->first()):$f[0](seq($xs)->first(), seq($ys)->first(), $f[1])), zip_with($f, seq($xs)->rest(), seq($ys)->rest()));
	}
}

function seq_join($xs, $glue=""){
	return implode($glue, arr($xs));
}

function lang__lambdafunc4($__closure_id){
	$xs = Lexical::get_lexical_binding('lang', 72, '$xs', isset($__closure_id)?$__closure_id:0);;
	return concat($xs, cycle($xs));
}

function cycle($xs){
	$__scope_id = Lexical::init_closure("lang", 72);
	Lexical::bind_lexing("lang", 72, '$xs', $xs);


	return new PharenLazyList(array("\lang__lambdafunc4", Lexical::get_closure_id("lang", $__scope_id)));
}

function lang__lambdafunc6($__closure_id){
		$f = Lexical::get_lexical_binding('lang', 74, '$f', isset($__closure_id)?$__closure_id:0);;
		$xs = Lexical::get_lexical_binding('lang', 74, '$xs', isset($__closure_id)?$__closure_id:0);;
	$new_xs = map($f, $xs);
	return concat($xs, cycle_with($f, $new_xs));
}

function cycle_with($f, $xs){
	$__scope_id = Lexical::init_closure("lang", 74);
	Lexical::bind_lexing("lang", 74, '$f', $f);
	Lexical::bind_lexing("lang", 74, '$xs', $xs);


	return new PharenLazyList(array("\lang__lambdafunc6", Lexical::get_closure_id("lang", $__scope_id)));
}

function vals($m){
	return array_values(arr($m));
}

function append($x, $xs){
	return array_merge($xs, array($x));
}

function apply($f, $val){
	return (is_string($f)?$f($val):$f[0]($val, $f[1]));
}

function concat($xs1, $xs2){
	if(empty__question($xs1)){
		return $xs2;
	}
	else{
		return cons(seq($xs1)->first(), concat(seq($xs1)->rest(), $xs2));
	}
}

function reduce($f, $acc, $xs){
	while(1){
		if(empty__question($xs)){
				return $acc;
		}
		$__tailrecursetmp0 = $f;
		$__tailrecursetmp1 = (is_string($f)?$f(seq($xs)->first(), $acc):$f[0](seq($xs)->first(), $acc, $f[1]));
		$__tailrecursetmp2 = seq($xs)->rest();
		$f = $__tailrecursetmp0;
		$acc = $__tailrecursetmp1;
		$xs = $__tailrecursetmp2;
	}
}

function lang__lambdafunc7($val, $acc, $__closure_id){
	$new_val_func = Lexical::get_lexical_binding('lang', 82, '$new_val_func', isset($__closure_id)?$__closure_id:0);;
	return ($acc . (is_string($new_val_func)?$new_val_func($val):$new_val_func[0]($val, $new_val_func[1])));
}

function reduce_concat($new_val_func, $xs){
	$__scope_id = Lexical::init_closure("lang", 82);
	Lexical::bind_lexing("lang", 82, '$new_val_func', $new_val_func);


	return reduce(array("\lang__lambdafunc7", Lexical::get_closure_id("lang", $__scope_id)), "", $xs);
}

function reduce_pairs($f, $acc, $xs){
	while(1){
		if(empty__question($xs)){
				return $acc;
		}
		$__tailrecursetmp0 = $f;
		$__tailrecursetmp1 = (is_string($f)?$f(each($xs), $acc):$f[0](each($xs), $acc, $f[1]));
		$__tailrecursetmp2 = seq($xs)->rest();
		$f = $__tailrecursetmp0;
		$acc = $__tailrecursetmp1;
		$xs = $__tailrecursetmp2;
	}
}

function map($f, $xs){
	if(empty__question($xs)){
		return $xs;
	}
	else{
		return cons((is_string($f)?$f(seq($xs)->first()):$f[0](seq($xs)->first(), $f[1])), map($f, seq($xs)->rest()));
	}
}

function filter($f, $coll){
	while(1){
		if(empty__question($coll)){
				return $coll;
		}
		else{
			$x = seq($coll)->first();
			$xs = seq($coll)->rest();
				if((is_string($f)?$f($x):$f[0]($x, $f[1]))){
					return cons($x, filter($f, $xs));
				}
		}
		$__tailrecursetmp0 = $f;
		$__tailrecursetmp1 = $xs;
		$f = $__tailrecursetmp0;
		$coll = $__tailrecursetmp1;
	}
}

function until($f, $xs){
	while(1){
		
		 Null;
		if(empty__question($xs)){
				return FALSE;
		}
		else if($result = (is_string($f)?$f(seq($xs)->first()):$f[0](seq($xs)->first(), $f[1]))){
				return $result;
		}
		$__tailrecursetmp0 = $f;
		$__tailrecursetmp1 = seq($xs)->rest();
		$f = $__tailrecursetmp0;
		$xs = $__tailrecursetmp1;
	}
}

function lang__lambdafunc8($pair, $acc, $__closure_id){
	$f = Lexical::get_lexical_binding('lang', 89, '$f', isset($__closure_id)?$__closure_id:0);;
	return append((is_string($f)?$f($pair[0], $pair[1]):$f[0]($pair[0], $pair[1], $f[1])), $acc);
}

function map_pairs($f, $pairs){
	$__scope_id = Lexical::init_closure("lang", 89);
	Lexical::bind_lexing("lang", 89, '$f', $f);


	return reduce_pairs(array("\lang__lambdafunc8", Lexical::get_closure_id("lang", $__scope_id)), array(), $pairs);
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
		return (self::$multis[$multi_name][$pattern] = $f);
	}
	
}
function lang__lambdafunc9($val, $__closure_id){
	
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


	return reduce_concat(array("\lang__lambdafunc9", Lexical::get_closure_id("lang", Null)), $vals);
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

