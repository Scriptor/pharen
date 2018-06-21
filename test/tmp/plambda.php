<?php
require_once('/Users/aarti/pharen/lang.php');
use Pharen\Lexical as Lexical;
use \Seq as Seq;
use \FastSeq as FastSeq;
Lexical::$scopes['_Users_aarti_pharen_test_tmpplambda'] = array();
$__scope_id = Lexical::init_closure("_Users_aarti_pharen_test_tmpplambda", 277);
$x = 100;
Lexical::bind_lexing("_Users_aarti_pharen_test_tmpplambda", 277, '$x', $x);
$y = 100;
Lexical::bind_lexing("_Users_aarti_pharen_test_tmpplambda", 277, '$y', $y);
$sum = function ($a, $b){
	$x = Lexical::get_lexical_binding('_Users_aarti_pharen_test_tmpplambda', 277, '$x', isset($__closure_id)?$__closure_id:0);;
	$y = Lexical::get_lexical_binding('_Users_aarti_pharen_test_tmpplambda', 277, '$y', isset($__closure_id)?$__closure_id:0);;
	return ($a + $b + $x + $y);
}

;
Lexical::bind_lexing("_Users_aarti_pharen_test_tmpplambda", 277, '$sum', $sum);
$sum3 = function ($a, $b){
	return ($a + $b);
}

;
Lexical::bind_lexing("_Users_aarti_pharen_test_tmpplambda", 277, '$sum3', $sum3);
check($sum(1, 2), 203);
check($sum3(1, 2), 3);
$sum10 = function ($x){
	return ($x + 10);
}

;
Lexical::bind_lexing("_Users_aarti_pharen_test_tmpplambda", 277, '$sum10', $sum10);
check($sum10(100), 110);
$summer = 100;
Lexical::bind_lexing("_Users_aarti_pharen_test_tmpplambda", 277, '$summer', $summer);
check(array_map(function ($n){
	$summer = Lexical::get_lexical_binding('_Users_aarti_pharen_test_tmpplambda', 277, '$summer', isset($__closure_id)?$__closure_id:0);;
	return ($n + $summer);
}

, arr(\PharenVector::create_from_array(array(1, 2, 3, 4, 5, 6)))), \PharenVector::create_from_array(array(101, 102, 103, 104, 105, 106)));
function fn_gen_sum($n){
	$__scope_id = Lexical::init_closure("_Users_aarti_pharen_test_tmpplambda", 285);
	Lexical::bind_lexing("_Users_aarti_pharen_test_tmpplambda", 285, '$n', $n);
return function ($x){
	$n = Lexical::get_lexical_binding('_Users_aarti_pharen_test_tmpplambda', 285, '$n', isset($__closure_id)?$__closure_id:0);;
	return ($x + $n);
}

;
}

$sum20 = fn_gen_sum(20);
Lexical::bind_lexing("_Users_aarti_pharen_test_tmpplambda", 277, '$sum20', $sum20);
check($sum20(30), 50);
