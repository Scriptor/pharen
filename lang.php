<?php
require_once(dirname(__FILE__).'/lexical.php');
use Pharen\Lexical as Lexical;
Lexical::$scopes['lang'] = array();
define("SYSTEM", dirname(__FILE__));
define("LIB_PATH", (SYSTEM . "/lib/"));
set_include_path((get_include_path() . PATH_SEPARATOR . LIB_PATH));
require("sequence.php");
function first($xs){
	return seq($xs)->first();
}

function rest($xs){
	return seq($xs)->rest();
}

function pharen_list($a, $xs){

	$xs = array_slice(func_get_args(), 1);
	return cons($a, $xs);
}

function butlast($xs){
	return take(dec(count($xs)), $xs);
}

function last($xs){
	return $xs[dec(count($xs))];
}

function eq($val1, $val2){
	
	 Null;
	if(($val1 instanceof IPharenComparable)){
		return $val1->eq($val2);
	}
	else if(($val2 instanceof IPharenComparable)){
		return $val2->eq($val1);
	}
	else{
		return ($val1 === $val2);
	}
}

function prn($s){
		print(($s . "\n"));
	return NULL;
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

function odd__question($n){
	return (($n % 2) == 1);
}

function even__question($n){
	return (($n % 2) == 0);
}

function str($s1, $s2){
	return ($s1 . $s2);
}

function identity($x){
	return $x;
}

function inc($x){
	return (1 + $x);
}

function dec($x){
	return ($x - 1);
}

function lang__lambdafunc3($args, $__closure_id){
	$__splatargs = func_get_args();
	$args = array_slice($__splatargs, 0, count($__splatargs) - 1);
	$__closure_id = last($__splatargs);
		$rfs = Lexical::get_lexical_binding('lang', 81, '$rfs', isset($__closure_id)?$__closure_id:0);;
	$init = call_user_func_array(first($rfs), $args);
	return reduce("\\apply", $init, rest($rfs));
}

function comp($fs){

	$fs = array_slice(func_get_args(), 0);
	$rfs = reverse($fs);
		$__scope_id = Lexical::init_closure("lang", 81);
		Lexical::bind_lexing("lang", 81, '$rfs', $rfs);
	return new \PharenLambda("\\lang__lambdafunc3", Lexical::get_closure_id("lang", $__scope_id));
}

function zero_or_empty__question($n, $xs){
		if(zero__question($n)){
		return zero__question($n);
	}
	else{
		return empty__question($xs);
	}

}

function empty__question($xs){
		if((seq($xs) instanceof PharenEmptyList)){
				return (seq($xs) instanceof PharenEmptyList);

	}
	else{
				return !(seq($xs));

	}

}

function seq__question($x){
		return ($x instanceof IPharenSeq);

}

function sequential__question($x){
		if(($x instanceof IPharenSeq)){
				return ($x instanceof IPharenSeq);

	}
	else{
		return is_array($x);
	}

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

function lang__lambdafunc4($pair, $hm, $__closure_id){
	return assoc($pair[0], $pair[1], $hm);
}

function hash_from_pairs($pairs){


	return reduce(new \PharenLambda("\\lang__lambdafunc4", Lexical::get_closure_id("lang", Null)), hashify(array()), $pairs);
}

function force($x){
	return $x->force();
}

function realized__question($x){
	return $x->realized();
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
	if(seq__question($xs)){
		return $xs->cons($x);
	}
	else{
		return seq($xs)->cons($x);
	}
}

function assoc($key, $val, $hm){
	return hashify($hm)->assoc($key, $val);
}

function get($key, $hm){
	if(isset($hm[$key])){
		return $hm[$key];
	}
	else{
		return NULL;
	}
}

function take($n, $xs){
	if(zero_or_empty__question($n, $xs)){
		return \PharenVector::create_from_array(array());
	}
	else{
		return cons(first($xs), take(($n - 1), rest($xs)));
	}
}

function drop($n, $xs){
	while(1){
		if(zero_or_empty__question($n, $xs)){
				return $xs;
		}
		$__tailrecursetmp0 = ($n - 1);
		$__tailrecursetmp1 = rest($xs);
		$n = $__tailrecursetmp0;
		$xs = $__tailrecursetmp1;
	}
}

function reverse($xs, $acc=array()){
	while(1){
		if(empty__question($xs)){
				return $acc;
		}
		$__tailrecursetmp0 = rest($xs);
		$__tailrecursetmp1 = cons(first($xs), $acc);
		$xs = $__tailrecursetmp0;
		$acc = $__tailrecursetmp1;
	}
}

function interpose($sep, $xs, $acc=array()){
	if((count($xs) == 1)){
		return \PharenVector::create_from_array(array(first($xs)));
	}
	else{
		return cons(first($xs), cons($sep, interpose($sep, rest($xs))));
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

function interleave($xs, $ys){
		
		$__condtmpvar0 = Null;
		if(empty__question($xs)){
			$__condtmpvar0 = empty__question($xs);
		}
		else{
			$__condtmpvar0 = empty__question($ys);
		}
	if($__condtmpvar0){
		return \PharenVector::create_from_array(array());
	}
	else{
		return cons(first($xs), cons(first($ys), interleave(rest($xs), rest($ys))));
	}
}

function zip_with($f, $xs, $ys){
	$__scope_id = Lexical::init_closure("lang", 116);
	Lexical::bind_lexing("lang", 116, '$f', $f);
		
		$__condtmpvar1 = Null;
		if(empty__question($xs)){
			$__condtmpvar1 = empty__question($xs);
		}
		else{
			$__condtmpvar1 = empty__question($ys);
		}
	if($__condtmpvar1){
		return \PharenVector::create_from_array(array());
	}
	else{
		return cons($f(first($xs), first($ys)), zip_with($f, rest($xs), rest($ys)));
	}
}

function seq_join($xs, $glue=""){
	return implode($glue, arr($xs));
}

function lang__lambdafunc6($__closure_id){
	$n = Lexical::get_lexical_binding('lang', 118, '$n', isset($__closure_id)?$__closure_id:0);;
	return cons($n, infinity(($n + 1)));
}

function infinity($n=0){
	$__scope_id = Lexical::init_closure("lang", 118);
	Lexical::bind_lexing("lang", 118, '$n', $n);
		

	return new \PharenLazyList(new \PharenLambda("\\lang__lambdafunc6", Lexical::get_closure_id("lang", $__scope_id)));


}

function lang__lambdafunc7($__closure_id){
	$x = Lexical::get_lexical_binding('lang', 120, '$x', isset($__closure_id)?$__closure_id:0);;
	return cons($x, repeat($x));
}

function repeat($x){
	$__scope_id = Lexical::init_closure("lang", 120);
	Lexical::bind_lexing("lang", 120, '$x', $x);
		

	return new \PharenLazyList(new \PharenLambda("\\lang__lambdafunc7", Lexical::get_closure_id("lang", $__scope_id)));


}

function lang__lambdafunc8($__closure_id){
	$f = Lexical::get_lexical_binding('lang', 122, '$f', isset($__closure_id)?$__closure_id:0);;
	return cons($f(), repeatedly($f));
}

function repeatedly($f){
	$__scope_id = Lexical::init_closure("lang", 122);
	Lexical::bind_lexing("lang", 122, '$f', $f);
		

	return new \PharenLazyList(new \PharenLambda("\\lang__lambdafunc8", Lexical::get_closure_id("lang", $__scope_id)));


}

function lang__lambdafunc9($__closure_id){
	$init = Lexical::get_lexical_binding('lang', 124, '$init', isset($__closure_id)?$__closure_id:0);;
	$f = Lexical::get_lexical_binding('lang', 124, '$f', isset($__closure_id)?$__closure_id:0);;
	return cons($init, iterate($f, $f($init)));
}

function iterate($f, $init){
	$__scope_id = Lexical::init_closure("lang", 124);
	Lexical::bind_lexing("lang", 124, '$f', $f);
	Lexical::bind_lexing("lang", 124, '$init', $init);
		

	return new \PharenLazyList(new \PharenLambda("\\lang__lambdafunc9", Lexical::get_closure_id("lang", $__scope_id)));


}

function lang__lambdafunc10($__closure_id){
	$xs = Lexical::get_lexical_binding('lang', 126, '$xs', isset($__closure_id)?$__closure_id:0);;
	return concat($xs, cycle($xs));
}

function cycle($xs){
	$__scope_id = Lexical::init_closure("lang", 126);
	Lexical::bind_lexing("lang", 126, '$xs', $xs);
		

	return new \PharenLazyList(new \PharenLambda("\\lang__lambdafunc10", Lexical::get_closure_id("lang", $__scope_id)));


}

function lang__lambdafunc11($__closure_id){
		$f = Lexical::get_lexical_binding('lang', 128, '$f', isset($__closure_id)?$__closure_id:0);;
		$xs = Lexical::get_lexical_binding('lang', 128, '$xs', isset($__closure_id)?$__closure_id:0);;
	$new_xs = map($f, $xs);
	return concat($xs, cycle_with($f, $new_xs));
}

function cycle_with($f, $xs){
	$__scope_id = Lexical::init_closure("lang", 128);
	Lexical::bind_lexing("lang", 128, '$f', $f);
	Lexical::bind_lexing("lang", 128, '$xs', $xs);
		

	return new \PharenLazyList(new \PharenLambda("\\lang__lambdafunc11", Lexical::get_closure_id("lang", $__scope_id)));


}

function vals($m){
	return array_values(arr($m));
}

function append($x, $xs){
	return array_merge($xs, \PharenVector::create_from_array(array($x)));
}

function apply($f, $args){

	$args = array_slice(func_get_args(), 1);
	
	$__condtmpvar2 = Null;
	if(is_array($f)){
		$__condtmpvar2 = $f[0];
	}
	else{
		$__condtmpvar2 = $f;
	}
	$name = $__condtmpvar2;
	
	$__condtmpvar3 = Null;
	if(sequential__question(last($args))){
		$__condtmpvar3 = concat(butlast($args), last($args));
	}
	else{
		$__condtmpvar3 = $args;
	}
	$args_array = arr($__condtmpvar3);
		if(is_array($f)){
		array_push($args_array, $f[1]);
	}
	else{
		NULL;
	}

	return call_user_func_array($name, $args_array);
}

function lang__lambdafunc12($x, $y, $__closure_id){
	$f = Lexical::get_lexical_binding('lang', 135, '$f', isset($__closure_id)?$__closure_id:0);;
	return $f($y, $x);
}

function flip($f){
	$__scope_id = Lexical::init_closure("lang", 135);
	Lexical::bind_lexing("lang", 135, '$f', $f);
	return new \PharenLambda("\\lang__lambdafunc12", Lexical::get_closure_id("lang", $__scope_id));
}

function lang__lambdafunc14($f, $__closure_id){
	$args = Lexical::get_lexical_binding('lang', 138, '$args', isset($__closure_id)?$__closure_id:0);;
	return apply($f, $args);
}

function lang__lambdafunc13($args, $__closure_id){
	$__splatargs = func_get_args();
	$args = array_slice($__splatargs, 0, count($__splatargs) - 1);
	$__closure_id = last($__splatargs);
	$__scope_id = Lexical::init_closure("lang", 138);
	Lexical::bind_lexing("lang", 138, '$args', $args);
	$fs = Lexical::get_lexical_binding('lang', 137, '$fs', isset($__closure_id)?$__closure_id:0);;




	return map(new \PharenLambda("\\lang__lambdafunc14", Lexical::get_closure_id("lang", $__scope_id)), $fs);
}

function juxt($fs){

	$fs = array_slice(func_get_args(), 0);
	$__scope_id = Lexical::init_closure("lang", 137);
	Lexical::bind_lexing("lang", 137, '$fs', $fs);
	return new \PharenLambda("\\lang__lambdafunc13", Lexical::get_closure_id("lang", $__scope_id));
}

function concat($xs1, $xs2){


	if(empty__question($xs1)){
		return $xs2;
	}
	else{
		return cons(first($xs1), concat(rest($xs1), $xs2));
	}
}

function into($to, $from){
	return reduce("\\cons", $to, $from);
}

function reduce($f, $acc, $xs){
	$__scope_id = Lexical::init_closure("lang", 142);
	Lexical::bind_lexing("lang", 142, '$f', $f);
	while(1){
		if(empty__question($xs)){
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

function reduce_fns($fns, $acc, $xs){
	while(1){
		if(empty__question($xs)){
				return $acc;
		}
			$__tmpfuncname0 = first($fns);
		$__tailrecursetmp0 = rest($fns);
		$__tailrecursetmp1 = $__tmpfuncname0(first($xs), $acc);
		$__tailrecursetmp2 = rest($xs);
		$fns = $__tailrecursetmp0;
		$acc = $__tailrecursetmp1;
		$xs = $__tailrecursetmp2;
	}
}

function lang__lambdafunc15($val, $acc, $__closure_id){
	$new_val_func = Lexical::get_lexical_binding('lang', 144, '$new_val_func', isset($__closure_id)?$__closure_id:0);;
	return ($acc . $new_val_func($val));
}

function reduce_to_str($new_val_func, $xs){
	$__scope_id = Lexical::init_closure("lang", 144);
	Lexical::bind_lexing("lang", 144, '$new_val_func', $new_val_func);


	return reduce(new \PharenLambda("\\lang__lambdafunc15", Lexical::get_closure_id("lang", $__scope_id)), "", $xs);
}

function reduce_pairs($f, $acc, $xs){
	$__scope_id = Lexical::init_closure("lang", 146);
	Lexical::bind_lexing("lang", 146, '$f', $f);
	while(1){
		if(empty__question($xs)){
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

function map($f, $xs){
	$__scope_id = Lexical::init_closure("lang", 147);
	Lexical::bind_lexing("lang", 147, '$f', $f);
	if(empty__question($xs)){
		return $xs;
	}
	else{
		return cons($f(first($xs)), map($f, rest($xs)));
	}
}

function filter($f, $coll){
	if(empty__question($coll)){
		return \PharenVector::create_from_array(array());
	}
	else{
		$x = first($coll);
		$xs = rest($coll);
		if(!($f($x))){
			return filter($f, $xs);
		}
		else{
			return cons($x, filter($f, $xs));
		}
	}
}

function until($f, $xs){
	$__scope_id = Lexical::init_closure("lang", 150);
	Lexical::bind_lexing("lang", 150, '$f', $f);
	while(1){
		
		 Null;
		if(empty__question($xs)){
				return FALSE;
		}
		else if($result = $f(first($xs))){
				return $result;
		}
		$__tailrecursetmp0 = $f;
		$__tailrecursetmp1 = rest($xs);
		$f = $__tailrecursetmp0;
		$xs = $__tailrecursetmp1;
	}
}

function map_indexed($f, $xs, $idx=0){
	$__scope_id = Lexical::init_closure("lang", 151);
	Lexical::bind_lexing("lang", 151, '$f', $f);
	if(empty__question($xs)){
		return $xs;
	}
	else{
		return cons($f(first($xs), $idx), map_indexed($f, rest($xs), inc($idx)));
	}
}

function lang__lambdafunc16($pair, $acc, $__closure_id){
	$f = Lexical::get_lexical_binding('lang', 152, '$f', isset($__closure_id)?$__closure_id:0);;
	return append($f($pair[0], $pair[1]), $acc);
}

function map_pairs($f, $pairs){
	$__scope_id = Lexical::init_closure("lang", 152);
	Lexical::bind_lexing("lang", 152, '$f', $f);


	return reduce_pairs(new \PharenLambda("\\lang__lambdafunc16", Lexical::get_closure_id("lang", $__scope_id)), \PharenVector::create_from_array(array()), $pairs);
}

function repling(){
		
	 Null;
	if(!(defined("PHARENREPLMODE"))){
		return defined("PHARENREPLMODE");
	}
	else if(!(constant("PHARENREPLMODE"))){
		return constant("PHARENREPLMODE");
	}
	else{
		return constant("PHARENREPLMODE");
	}

}

class MultiManager{
	static $multis = NULL;
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
MultiManager::$multis = hashify(array());
function lang__lambdafunc17($val, $__closure_id){
	
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


	return reduce_to_str(new \PharenLambda("\\lang__lambdafunc17", Lexical::get_closure_id("lang", Null)), $vals);
}

function multi_serialize_pattern($pattern){
	return implode(arr($pattern));
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
