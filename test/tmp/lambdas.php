<?php
require_once('/Users/aarti/pharen/lang.php');
use Pharen\Lexical as Lexical;
use \Seq as Seq;
use \FastSeq as FastSeq;
Lexical::$scopes['_Users_aarti_pharen_test_tmplambdas'] = array();
function _Users_aarti_pharen_test_tmplambdas__lambdafunc22($n, $__closure_id){
	return ($n * 2);
}


check(arr(map(new \PharenLambda("\\_Users_aarti_pharen_test_tmplambdas__lambdafunc22", Lexical::get_closure_id("_Users_aarti_pharen_test_tmplambdas", Null)), \PharenVector::create_from_array(array(1, 2, 3)))), \PharenVector::create_from_array(array(2, 4, 6)));
function apply_test($f, $n){
	$__scope_id = Lexical::init_closure("_Users_aarti_pharen_test_tmplambdas", 268);
	Lexical::bind_lexing("_Users_aarti_pharen_test_tmplambdas", 268, '$f', $f);
	return $f($n);
}

function _Users_aarti_pharen_test_tmplambdas__lambdafunc23($n, $__closure_id){
	return ($n . " bar");
}


check(apply_test(new \PharenLambda("\\_Users_aarti_pharen_test_tmplambdas__lambdafunc23", Lexical::get_closure_id("_Users_aarti_pharen_test_tmplambdas", Null)), "foo"), "foo bar");
function _Users_aarti_pharen_test_tmplambdas__lambdafunc24($__closure_id){
	$s = Lexical::get_lexical_binding('_Users_aarti_pharen_test_tmplambdas', 270, '$s', isset($__closure_id)?$__closure_id:0);;
	return ("Hello " . $s . "!");
}

function greet_generator_test($s){
	$__scope_id = Lexical::init_closure("_Users_aarti_pharen_test_tmplambdas", 270);
	Lexical::bind_lexing("_Users_aarti_pharen_test_tmplambdas", 270, '$s', $s);
	"line1";
	return new \PharenLambda("\\_Users_aarti_pharen_test_tmplambdas__lambdafunc24", Lexical::get_closure_id("_Users_aarti_pharen_test_tmplambdas", $__scope_id));
}



$__tmpfuncname7 = greet_generator_test("Hammurabi");
check($__tmpfuncname7(), "Hello Hammurabi!");
function _Users_aarti_pharen_test_tmplambdas__lambdafunc25($__closure_id){
	;
	return "";
}

function _Users_aarti_pharen_test_tmplambdas__lambdafunc26($__closure_id){
	;
	return "foo";
}

function multi_lambdas(){


new \PharenLambda("\\_Users_aarti_pharen_test_tmplambdas__lambdafunc25", Lexical::get_closure_id("_Users_aarti_pharen_test_tmplambdas", Null));
	return new \PharenLambda("\\_Users_aarti_pharen_test_tmplambdas__lambdafunc26", Lexical::get_closure_id("_Users_aarti_pharen_test_tmplambdas", Null));
}



$__tmpfuncname8 = multi_lambdas();
check($__tmpfuncname8(), "foo");
function _Users_aarti_pharen_test_tmplambdas__lambdafunc27($__closure_id){
	return "foobar";
}

function multiple_calls_test(){
	return new \PharenLambda("\\_Users_aarti_pharen_test_tmplambdas__lambdafunc27", Lexical::get_closure_id("_Users_aarti_pharen_test_tmplambdas", Null));
}



multiple_calls_test();
multiple_calls_test();
$__tmpfuncname9 = multiple_calls_test();
check($__tmpfuncname9(), "foobar");
