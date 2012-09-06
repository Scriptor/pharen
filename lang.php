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
	return (1 - $x);
}

function lang__lambdafunc3($arg1, $arg2, $__closure_id){
	$f = Lexical::get_lexical_binding('lang', 69, '$f', isset($__closure_id)?$__closure_id:0);;
	return (is_string($f) || is_callable($f)?$f($arg2, $arg1):$f[0]($arg2, $arg1, $f[1]));
}

function flip($f){
	$__scope_id = Lexical::init_closure("lang", 69);
	Lexical::bind_lexing("lang", 69, '$f', $f);
	return array("\\lang__lambdafunc3", Lexical::get_closure_id("lang", $__scope_id));
}

function lang__lambdafunc4($args, $__closure_id){
	
		$args = array_slice(func_get_args(), 0);
		$rfs = Lexical::get_lexical_binding('lang', 72, '$rfs', isset($__closure_id)?$__closure_id:0);;
	$init = call_user_func_array(first($rfs), $args);
	return reduce("apply", $init, rest($rfs));
}

function comp($fs){

	$fs = array_slice(func_get_args(), 0);


	$rfs = reverse($fs);
		$__scope_id = Lexical::init_closure("lang", 72);
		Lexical::bind_lexing("lang", 72, '$rfs', $rfs);
	return array("\\lang__lambdafunc4", Lexical::get_closure_id("lang", $__scope_id));
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

function lang__lambdafunc5($pair, $hm, $__closure_id){
	return assoc($pair[0], $pair[1], $hm);
}

function hash_from_pairs($pairs){


	return reduce(array("\\lang__lambdafunc5", Lexical::get_closure_id("lang", Null)), array(), $pairs);
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
	$__scope_id = Lexical::init_closure("lang", 104);
	Lexical::bind_lexing("lang", 104, '$f', $f);
		
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
		return cons((is_string($f) || is_callable($f)?$f(first($xs), first($ys)):$f[0](first($xs), first($ys), $f[1])), zip_with($f, rest($xs), rest($ys)));
	}
}

function seq_join($xs, $glue=""){
	return implode($glue, arr($xs));
}

function lang__lambdafunc7($__closure_id){
	$n = Lexical::get_lexical_binding('lang', 106, '$n', isset($__closure_id)?$__closure_id:0);;
	return cons($n, infinity(($n + 1)));
}

function infinity($n=0){
	$__scope_id = Lexical::init_closure("lang", 106);
	Lexical::bind_lexing("lang", 106, '$n', $n);
		

	return new \PharenLazyList(array("\\lang__lambdafunc7", Lexical::get_closure_id("lang", $__scope_id)));


}

function lang__lambdafunc8($__closure_id){
	$x = Lexical::get_lexical_binding('lang', 108, '$x', isset($__closure_id)?$__closure_id:0);;
	return cons($x, repeat($x));
}

function repeat($x){
	$__scope_id = Lexical::init_closure("lang", 108);
	Lexical::bind_lexing("lang", 108, '$x', $x);
		

	return new \PharenLazyList(array("\\lang__lambdafunc8", Lexical::get_closure_id("lang", $__scope_id)));


}

function lang__lambdafunc9($__closure_id){
	$f = Lexical::get_lexical_binding('lang', 110, '$f', isset($__closure_id)?$__closure_id:0);;
	return cons((is_string($f) || is_callable($f)?$f():$f[0]($f[1])), repeatedly($f));
}

function repeatedly($f){
	$__scope_id = Lexical::init_closure("lang", 110);
	Lexical::bind_lexing("lang", 110, '$f', $f);
		

	return new \PharenLazyList(array("\\lang__lambdafunc9", Lexical::get_closure_id("lang", $__scope_id)));


}

function lang__lambdafunc10($__closure_id){
	$init = Lexical::get_lexical_binding('lang', 112, '$init', isset($__closure_id)?$__closure_id:0);;
	$f = Lexical::get_lexical_binding('lang', 112, '$f', isset($__closure_id)?$__closure_id:0);;
	return cons($init, iterate($f, (is_string($f) || is_callable($f)?$f($init):$f[0]($init, $f[1]))));
}

function iterate($f, $init){
	$__scope_id = Lexical::init_closure("lang", 112);
	Lexical::bind_lexing("lang", 112, '$f', $f);
	Lexical::bind_lexing("lang", 112, '$init', $init);
		

	return new \PharenLazyList(array("\\lang__lambdafunc10", Lexical::get_closure_id("lang", $__scope_id)));


}

function lang__lambdafunc11($__closure_id){
	$xs = Lexical::get_lexical_binding('lang', 114, '$xs', isset($__closure_id)?$__closure_id:0);;
	return concat($xs, cycle($xs));
}

function cycle($xs){
	$__scope_id = Lexical::init_closure("lang", 114);
	Lexical::bind_lexing("lang", 114, '$xs', $xs);
		

	return new \PharenLazyList(array("\\lang__lambdafunc11", Lexical::get_closure_id("lang", $__scope_id)));


}

function lang__lambdafunc12($__closure_id){
		$f = Lexical::get_lexical_binding('lang', 116, '$f', isset($__closure_id)?$__closure_id:0);;
		$xs = Lexical::get_lexical_binding('lang', 116, '$xs', isset($__closure_id)?$__closure_id:0);;
	$new_xs = map($f, $xs);
	return concat($xs, cycle_with($f, $new_xs));
}

function cycle_with($f, $xs){
	$__scope_id = Lexical::init_closure("lang", 116);
	Lexical::bind_lexing("lang", 116, '$f', $f);
	Lexical::bind_lexing("lang", 116, '$xs', $xs);
		

	return new \PharenLazyList(array("\\lang__lambdafunc12", Lexical::get_closure_id("lang", $__scope_id)));


}

function vals($m){
	return array_values(arr($m));
}

function append($x, $xs){
	return array_merge($xs, \PharenVector::create_from_array(array($x)));
}

function apply($f, $val){
	$__scope_id = Lexical::init_closure("lang", 121);
	Lexical::bind_lexing("lang", 121, '$f', $f);
	return (is_string($f) || is_callable($f)?$f($val):$f[0]($val, $f[1]));
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
	return reduce("cons", $to, $from);
}

function reduce($f, $acc, $xs){
	$__scope_id = Lexical::init_closure("lang", 124);
	Lexical::bind_lexing("lang", 124, '$f', $f);
	while(1){
		if(empty__question($xs)){
				return $acc;
		}
		$__tailrecursetmp0 = $f;
		$__tailrecursetmp1 = (is_string($f) || is_callable($f)?$f(first($xs), $acc):$f[0](first($xs), $acc, $f[1]));
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
		$__tailrecursetmp1 = (is_string($__tmpfuncname0) || is_callable($__tmpfuncname0)?$__tmpfuncname0(first($xs), $acc):$__tmpfuncname0[0](first($xs), $acc, $__tmpfuncname0[1]));
		$__tailrecursetmp2 = rest($xs);
		$fns = $__tailrecursetmp0;
		$acc = $__tailrecursetmp1;
		$xs = $__tailrecursetmp2;
	}
}

function lang__lambdafunc13($val, $acc, $__closure_id){
	$new_val_func = Lexical::get_lexical_binding('lang', 126, '$new_val_func', isset($__closure_id)?$__closure_id:0);;
	return ($acc . (is_string($new_val_func) || is_callable($new_val_func)?$new_val_func($val):$new_val_func[0]($val, $new_val_func[1])));
}

function reduce_to_str($new_val_func, $xs){
	$__scope_id = Lexical::init_closure("lang", 126);
	Lexical::bind_lexing("lang", 126, '$new_val_func', $new_val_func);


	return reduce(array("\\lang__lambdafunc13", Lexical::get_closure_id("lang", $__scope_id)), "", $xs);
}

function reduce_pairs($f, $acc, $xs){
	$__scope_id = Lexical::init_closure("lang", 128);
	Lexical::bind_lexing("lang", 128, '$f', $f);
	while(1){
		if(empty__question($xs)){
				return $acc;
		}
		$__tailrecursetmp0 = $f;
		$__tailrecursetmp1 = (is_string($f) || is_callable($f)?$f(each($xs), $acc):$f[0](each($xs), $acc, $f[1]));
		$__tailrecursetmp2 = rest($xs);
		$f = $__tailrecursetmp0;
		$acc = $__tailrecursetmp1;
		$xs = $__tailrecursetmp2;
	}
}

function map($f, $xs){
	$__scope_id = Lexical::init_closure("lang", 129);
	Lexical::bind_lexing("lang", 129, '$f', $f);
	if(empty__question($xs)){
		return $xs;
	}
	else{
		return cons((is_string($f) || is_callable($f)?$f(first($xs)):$f[0](first($xs), $f[1])), map($f, rest($xs)));
	}
}

function filter($f, $coll){
	if(empty__question($coll)){
		return \PharenVector::create_from_array(array());
	}
	else{
		$x = first($coll);
		$xs = rest($coll);
		if(!((is_string($f) || is_callable($f)?$f($x):$f[0]($x, $f[1])))){
			return filter($f, $xs);
		}
		else{
			return cons($x, filter($f, $xs));
		}
	}
}

function until($f, $xs){
	$__scope_id = Lexical::init_closure("lang", 132);
	Lexical::bind_lexing("lang", 132, '$f', $f);
	while(1){
		
		 Null;
		if(empty__question($xs)){
				return FALSE;
		}
		else if($result = (is_string($f) || is_callable($f)?$f(first($xs)):$f[0](first($xs), $f[1]))){
				return $result;
		}
		$__tailrecursetmp0 = $f;
		$__tailrecursetmp1 = rest($xs);
		$f = $__tailrecursetmp0;
		$xs = $__tailrecursetmp1;
	}
}

function lang__lambdafunc14($pair, $acc, $__closure_id){
	$f = Lexical::get_lexical_binding('lang', 133, '$f', isset($__closure_id)?$__closure_id:0);;
	return append((is_string($f) || is_callable($f)?$f($pair[0], $pair[1]):$f[0]($pair[0], $pair[1], $f[1])), $acc);
}

function map_pairs($f, $pairs){
	$__scope_id = Lexical::init_closure("lang", 133);
	Lexical::bind_lexing("lang", 133, '$f', $f);


	return reduce_pairs(array("\\lang__lambdafunc14", Lexical::get_closure_id("lang", $__scope_id)), \PharenVector::create_from_array(array()), $pairs);
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
function lang__lambdafunc15($val, $__closure_id){
	
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


	return reduce_concat(array("\\lang__lambdafunc15", Lexical::get_closure_id("lang", Null)), $vals);
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

