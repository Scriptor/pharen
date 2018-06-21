<?php
require_once('/Users/aarti/pharen/lang.php');
use Pharen\Lexical as Lexical;
use \Seq as Seq;
use \FastSeq as FastSeq;
Lexical::$scopes['_Users_aarti_pharen_test_tmpphp_interop'] = array();
$__scope_id = Lexical::init_closure("_Users_aarti_pharen_test_tmpphp_interop", 338);
function _Users_aarti_pharen_test_tmpphp_interop__partial10($arg0, $__closure_id){
	return (1 + $arg0);
}


check(array_map(new \PharenLambda('\\_Users_aarti_pharen_test_tmpphp_interop__partial10', Lexical::get_closure_id("_Users_aarti_pharen_test_tmpphp_interop", Null)), arr(\PharenVector::create_from_array(array(1, 2, 3)))), array(2, 3, 4));
function _Users_aarti_pharen_test_tmpphp_interop__lambdafunc39($x, $__closure_id){
	return ($x + 1);
}


check(array_map(new \PharenLambda("\\_Users_aarti_pharen_test_tmpphp_interop__lambdafunc39", Lexical::get_closure_id("_Users_aarti_pharen_test_tmpphp_interop", Null)), arr(\PharenVector::create_from_array(array(1, 2, 3)))), array(2, 3, 4));
function _Users_aarti_pharen_test_tmpphp_interop__partial11($arg0, $__closure_id){
	return (4 > $arg0);
}


check(array_filter(arr(\PharenVector::create_from_array(array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10))), new \PharenLambda('\\_Users_aarti_pharen_test_tmpphp_interop__partial11', Lexical::get_closure_id("_Users_aarti_pharen_test_tmpphp_interop", Null))), array(1, 2, 3));
function _Users_aarti_pharen_test_tmpphp_interop__lambdafunc40($x, $__closure_id){
	return (4 > $x);
}


check(array_filter(arr(\PharenVector::create_from_array(array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10))), new \PharenLambda("\\_Users_aarti_pharen_test_tmpphp_interop__lambdafunc40", Lexical::get_closure_id("_Users_aarti_pharen_test_tmpphp_interop", Null))), array(1, 2, 3));
check((array(1, 2, 3) == array(1, 2, 3)), TRUE);
check(eq(array(1, 2, 3), array(1, 2, 3)), TRUE);
check(eq(\PharenVector::create_from_array(array(1, 2, 3)), \PharenVector::create_from_array(array(1, 2, 3))), TRUE);
check(eq(\PharenVector::create_from_array(array(1, 2, 3)), array(1, 2, 3)), TRUE);
check((\PharenVector::create_from_array(array(1, 2, 3)) == array(1, 2, 3)), FALSE);
	$__scope_id = Lexical::init_closure("_Users_aarti_pharen_test_tmpphp_interop", 343);
$sum = 100;
	Lexical::bind_lexing("_Users_aarti_pharen_test_tmpphp_interop", 343, '$sum', $sum);
function _Users_aarti_pharen_test_tmpphp_interop__lambdafunc41($x, $__closure_id){
	$sum = Lexical::get_lexical_binding('_Users_aarti_pharen_test_tmpphp_interop', 343, '$sum', isset($__closure_id)?$__closure_id:0);;
	return ($x + $sum);
}


check(array_map(new \PharenLambda("\\_Users_aarti_pharen_test_tmpphp_interop__lambdafunc41", Lexical::get_closure_id("_Users_aarti_pharen_test_tmpphp_interop", $__scope_id)), arr(\PharenVector::create_from_array(array(1, 2, 3)))), array(101, 102, 103));
$sum2 = 101;
Lexical::bind_lexing("_Users_aarti_pharen_test_tmpphp_interop", 338, '$sum2', $sum2);
function _Users_aarti_pharen_test_tmpphp_interop__lambdafunc42($x, $__closure_id){
	$sum2 = Lexical::get_lexical_binding('_Users_aarti_pharen_test_tmpphp_interop', 338, '$sum2', isset($__closure_id)?$__closure_id:0);;
	return ($x + $sum2);
}


check(array_map(new \PharenLambda("\\_Users_aarti_pharen_test_tmpphp_interop__lambdafunc42", Lexical::get_closure_id("_Users_aarti_pharen_test_tmpphp_interop", $__scope_id)), arr(\PharenVector::create_from_array(array(1, 2, 3)))), array(102, 103, 104));
