<?php
namespace pharen\lazy;
require_once('C:\pharen/lang.php');
use Pharen\Lexical as Lexical;
Lexical::$scopes['lazy'] = array();
function lazy__lambdafunc19($__closure_id){
	$coll = Lexical::get_lexical_binding('lazy', 186, '$coll', isset($__closure_id)?$__closure_id:0);;
	$f = Lexical::get_lexical_binding('lazy', 186, '$f', isset($__closure_id)?$__closure_id:0);;
	if(empty__question($coll)){
		return \PharenVector::create_from_array(array());
	}
	else{
		return cons($f(first($coll)), map($f, rest($coll)));
	}
}

function map($f, $coll){
	$__scope_id = Lexical::init_closure("lazy", 186);
	Lexical::bind_lexing("lazy", 186, '$f', $f);
	Lexical::bind_lexing("lazy", 186, '$coll', $coll);
		

	return new \PharenLazyList(new \PharenLambda("pharen\lazy\\lazy__lambdafunc19", Lexical::get_closure_id("lazy", $__scope_id)));


}

function lazy__lambdafunc20($__closure_id){
	$n = Lexical::get_lexical_binding('lazy', 188, '$n', isset($__closure_id)?$__closure_id:0);;
	$coll = Lexical::get_lexical_binding('lazy', 188, '$coll', isset($__closure_id)?$__closure_id:0);;
	if(zero_or_empty__question($n, $coll)){
		return \PharenVector::create_from_array(array());
	}
	else{
		return cons(first($coll), take(($n - 1), rest($coll)));
	}
}

function take($n, $coll){
	$__scope_id = Lexical::init_closure("lazy", 188);
	Lexical::bind_lexing("lazy", 188, '$n', $n);
	Lexical::bind_lexing("lazy", 188, '$coll', $coll);
		

	return new \PharenLazyList(new \PharenLambda("pharen\lazy\\lazy__lambdafunc20", Lexical::get_closure_id("lazy", $__scope_id)));


}

function lazy__lambdafunc21($__closure_id){
	$xs = Lexical::get_lexical_binding('lazy', 190, '$xs', isset($__closure_id)?$__closure_id:0);;
	$ys = Lexical::get_lexical_binding('lazy', 190, '$ys', isset($__closure_id)?$__closure_id:0);;
	if(empty__question($xs)){
		return \PharenVector::create_from_array(array());
	}
	else{
		return cons(first($xs), interleave($ys, rest($xs)));
	}
}

function interleave($xs, $ys){
	$__scope_id = Lexical::init_closure("lazy", 190);
	Lexical::bind_lexing("lazy", 190, '$xs', $xs);
	Lexical::bind_lexing("lazy", 190, '$ys', $ys);
		

	return new \PharenLazyList(new \PharenLambda("pharen\lazy\\lazy__lambdafunc21", Lexical::get_closure_id("lazy", $__scope_id)));


}

function lazy__lambdafunc22($__closure_id){
	$coll = Lexical::get_lexical_binding('lazy', 192, '$coll', isset($__closure_id)?$__closure_id:0);;
	if(empty__question($coll)){
		return \PharenVector::create_from_array(array());
	}
	else{
			$coll = Lexical::get_lexical_binding('lazy', 192, '$coll', isset($__closure_id)?$__closure_id:0);;
			$f = Lexical::get_lexical_binding('lazy', 192, '$f', isset($__closure_id)?$__closure_id:0);;
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
	$__scope_id = Lexical::init_closure("lazy", 192);
	Lexical::bind_lexing("lazy", 192, '$f', $f);
	Lexical::bind_lexing("lazy", 192, '$coll', $coll);
		

	return new \PharenLazyList(new \PharenLambda("pharen\lazy\\lazy__lambdafunc22", Lexical::get_closure_id("lazy", $__scope_id)));


}

