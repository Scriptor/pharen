<?php
require_once('/Users/aarti/pharen/lang.php');
use Pharen\Lexical as Lexical;
use \Seq as Seq;
use \FastSeq as FastSeq;
Lexical::$scopes['_Users_aarti_pharen_test_tmpmulti'] = array();
	MultiManager::$multis["multi-tst"] = hashify(array());
	function multi_tst($n){
		$__tmpfuncname11 = get_multi("multi-tst", func_get_args());
		return $__tmpfuncname11($n);
	}
	

function _Users_aarti_pharen_test_tmpmulti__lambdafunc28($a, $__closure_id){
	return "int";
}


MultiManager::set_multi("multi-tst", multi_serialize_pattern(\PharenVector::create_from_array(array("int"))), new \PharenLambda("\\_Users_aarti_pharen_test_tmpmulti__lambdafunc28", Lexical::get_closure_id("_Users_aarti_pharen_test_tmpmulti", Null)));
function _Users_aarti_pharen_test_tmpmulti__lambdafunc29($a, $__closure_id){
	return "string";
}


MultiManager::set_multi("multi-tst", multi_serialize_pattern(\PharenVector::create_from_array(array("str"))), new \PharenLambda("\\_Users_aarti_pharen_test_tmpmulti__lambdafunc29", Lexical::get_closure_id("_Users_aarti_pharen_test_tmpmulti", Null)));
check(multi_tst(2), "int");
check(multi_tst("foo"), "string");
