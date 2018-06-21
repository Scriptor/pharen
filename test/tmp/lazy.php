<?php
namespace lazy_tests;
require_once('/Users/aarti/pharen/lang.php');
use Pharen\Lexical as Lexical;
use \Seq as Seq;
use \FastSeq as FastSeq;
Lexical::$scopes['_Users_aarti_pharen_test_tmplazy'] = array();
include_once 'pharen/lazy.php';
use pharen\lazy as lazy;
function _Users_aarti_pharen_test_tmplazy__partial1($arg0, $__closure_id){
	return (2 * $arg0);
}


check(lazy\map(new \PharenLambda('lazy_tests\\_Users_aarti_pharen_test_tmplazy__partial1', Lexical::get_closure_id("_Users_aarti_pharen_test_tmplazy", Null)), \PharenVector::create_from_array(array(1, 2, 3))), \PharenVector::create_from_array(array(2, 4, 6)));
check(lazy\take(3, infinity()), \PharenVector::create_from_array(array(0, 1, 2)));
check(lazy\interleave(\PharenVector::create_from_array(array(1, 2, 3)), \PharenVector::create_from_array(array(4, 5, 6))), \PharenVector::create_from_array(array(1, 4, 2, 5, 3, 6)));
check(lazy\filter('\pos__question', \PharenVector::create_from_array(array(1, -2, 3, -5))), \PharenVector::create_from_array(array(1, 3)));
check(take(5, infinity()), \PharenVector::create_from_array(array(0, 1, 2, 3, 4)));
check(take(3, cycle(\PharenVector::create_from_array(array(1, 2)))), \PharenVector::create_from_array(array(1, 2, 1)));
