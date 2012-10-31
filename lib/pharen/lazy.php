<?php
namespace pharen\lazy;
require_once('/Users/historium/pharen/lang.php');
use Pharen\Lexical as Lexical;
Lexical::$scopes['lazy'] = array();
function lazy__lambdafunc23($__closure_id){
	$coll = Lexical::get_lexical_binding('lazy', 257, '$coll', isset($__closure_id)?$__closure_id:0);;
	$f = Lexical::get_lexical_binding('lazy', 257, '$f', isset($__closure_id)?$__closure_id:0);;
	if(empty__question($coll)){
		return \PharenVector::create_from_array(array());
	}
	else{
		return cons($f(first($coll)), map($f, rest($coll)));
	}
}

function map($f, $coll){
	$__scope_id = Lexical::init_closure("lazy", 257);
	Lexical::bind_lexing("lazy", 257, '$f', $f);
	Lexical::bind_lexing("lazy", 257, '$coll', $coll);
		

	return new \PharenLazyList(new \PharenLambda("pharen\lazy\\lazy__lambdafunc23", Lexical::get_closure_id("lazy", $__scope_id)));


}

function lazy__lambdafunc24($__closure_id){
	$n = Lexical::get_lexical_binding('lazy', 259, '$n', isset($__closure_id)?$__closure_id:0);;
	$coll = Lexical::get_lexical_binding('lazy', 259, '$coll', isset($__closure_id)?$__closure_id:0);;
	if(zero_or_empty__question($n, $coll)){
		return \PharenVector::create_from_array(array());
	}
	else{
		return cons(first($coll), take(($n - 1), rest($coll)));
	}
}

function take($n, $coll){
	$__scope_id = Lexical::init_closure("lazy", 259);
	Lexical::bind_lexing("lazy", 259, '$n', $n);
	Lexical::bind_lexing("lazy", 259, '$coll', $coll);
		

	return new \PharenLazyList(new \PharenLambda("pharen\lazy\\lazy__lambdafunc24", Lexical::get_closure_id("lazy", $__scope_id)));


}

function lazy__lambdafunc25($__closure_id){
	$xs = Lexical::get_lexical_binding('lazy', 261, '$xs', isset($__closure_id)?$__closure_id:0);;
	$ys = Lexical::get_lexical_binding('lazy', 261, '$ys', isset($__closure_id)?$__closure_id:0);;
	if(empty__question($xs)){
		return \PharenVector::create_from_array(array());
	}
	else{
		return cons(first($xs), interleave($ys, rest($xs)));
	}
}

function interleave($xs, $ys){
	$__scope_id = Lexical::init_closure("lazy", 261);
	Lexical::bind_lexing("lazy", 261, '$xs', $xs);
	Lexical::bind_lexing("lazy", 261, '$ys', $ys);
		

	return new \PharenLazyList(new \PharenLambda("pharen\lazy\\lazy__lambdafunc25", Lexical::get_closure_id("lazy", $__scope_id)));


}

function lazy__lambdafunc26($__closure_id){
	$coll = Lexical::get_lexical_binding('lazy', 263, '$coll', isset($__closure_id)?$__closure_id:0);;
	if(empty__question($coll)){
		return \PharenVector::create_from_array(array());
	}
	else{
			$coll = Lexical::get_lexical_binding('lazy', 263, '$coll', isset($__closure_id)?$__closure_id:0);;
			$f = Lexical::get_lexical_binding('lazy', 263, '$f', isset($__closure_id)?$__closure_id:0);;
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

function filter($f, $coll){
	$__scope_id = Lexical::init_closure("lazy", 263);
	Lexical::bind_lexing("lazy", 263, '$f', $f);
	Lexical::bind_lexing("lazy", 263, '$coll', $coll);
		

	return new \PharenLazyList(new \PharenLambda("pharen\lazy\\lazy__lambdafunc26", Lexical::get_closure_id("lazy", $__scope_id)));


}

