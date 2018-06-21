<?php
require_once('/Users/aarti/pharen/lang.php');
use Pharen\Lexical as Lexical;
use \Seq as Seq;
use \FastSeq as FastSeq;
Lexical::$scopes['_Users_aarti_pharen_test_tmplang_functions'] = array();

$__condtmpvar12 = Null;
if((1 == 1)){
	$__condtmpvar12 = $x = 2;
}
else{
	$__condtmpvar12 = NULL;
}
check($__condtmpvar12, 2);
check(!((2 == 2)), FALSE);

$__condtmpvar13 = Null;
if((3 == 1)){
	$__condtmpvar13 = 3 == 1;
}
else{
	$__condtmpvar13 = 1 == 2;
}
check($__condtmpvar13, FALSE);

$__condtmpvar14 = Null;
if((1 == 1)){
	$__condtmpvar14 = 1 == 1;
}
else{
	$__condtmpvar14 = 1 == 2;
}
check($__condtmpvar14, TRUE);


$__condtmpvar15 =  Null;
if(!((1 == 1))){
	$__condtmpvar15 = 1 == 1;
}
else if(!((1 == 2))){
	$__condtmpvar15 = 1 == 2;
}
else{
	$__condtmpvar15 = 1 == 2;
}
check($__condtmpvar15, FALSE);


$__condtmpvar16 =  Null;
if(!((1 == 1))){
	$__condtmpvar16 = 1 == 1;
}
else if(!((2 == 2))){
	$__condtmpvar16 = 2 == 2;
}
else{
	$__condtmpvar16 = 2 == 2;
}
check($__condtmpvar16, TRUE);
function _Users_aarti_pharen_test_tmplang_functions__lambdafunc30($__closure_id){
	return (1 + 2);
}


$__tmpfuncname12 = new \PharenLambda("\\_Users_aarti_pharen_test_tmplang_functions__lambdafunc30", Lexical::get_closure_id("_Users_aarti_pharen_test_tmplang_functions", Null));
check($__tmpfuncname12(), 3);
class A{
	public $name = "";
}
$a = new A();
check(($a instanceof A), TRUE);
check(first(\PharenVector::create_from_array(array(0, 1, 2))), 0);
check(rest(\PharenVector::create_from_array(array(0, 1, 2))), \PharenVector::create_from_array(array(1, 2)));
check(pharen_list(1, 2, 3), \PharenVector::create_from_array(array(1, 2, 3)));
check(last(\PharenVector::create_from_array(array(1, 2, 3))), 3);
check(butlast(\PharenVector::create_from_array(array(1, 2, 3))), \PharenVector::create_from_array(array(1, 2)));
check(eq(1, 1), TRUE);
check(eq(1, 2), FALSE);
$a1 = new A();
check(eq($a1, $a1), TRUE);

$__condtmpvar17 = Null;
if(2){
	$x = 2;
	$__condtmpvar17 = $x;
}
else{
	$__condtmpvar17 = FALSE;
}
check($__condtmpvar17, 2);

$__condtmpvar18 = Null;
if(2){
	$x = 2;
	$__condtmpvar18 = $x;
}
else{
	$__condtmpvar18 = NULL;
}
check($__condtmpvar18, 2);

$__condtmpvar19 = Null;
if(!((1 == 2))){
	$__condtmpvar19 = '\a';
}
else{
	$__condtmpvar19 = '\b';
}
check($__condtmpvar19, '\a');

$__condtmpvar20 = Null;
if(!((1 == 2))){
	$__condtmpvar20 = '\a';
}
else{
	$__condtmpvar20 = NULL;
}
check($__condtmpvar20, '\a');
	$__scope_id = Lexical::init_closure("_Users_aarti_pharen_test_tmplang_functions", 320);
$y = 100;
	Lexical::bind_lexing("_Users_aarti_pharen_test_tmplang_functions", 320, '$y', $y);
function _Users_aarti_pharen_test_tmplang_functions__lambdafunc31($x, $__dotimes_result, $__closure_id){
	$y = Lexical::get_lexical_binding('_Users_aarti_pharen_test_tmplang_functions', 320, '$y', isset($__closure_id)?$__closure_id:0);;
	while(1){
		if(($x >= 100)){
					return $__dotimes_result;
		}
								$y = ($x + 100);
				$__dotmpvar3 = check($y, ($x + 100));
			$__dotimes_result = $__dotmpvar3;
		$__tailrecursetmp0 = inc($x);
		$x = $__tailrecursetmp0;
	}
}


$__tmpfuncname15 = new \PharenLambda("\\_Users_aarti_pharen_test_tmplang_functions__lambdafunc31", Lexical::get_closure_id("_Users_aarti_pharen_test_tmplang_functions", $__scope_id));
$__tmpfuncname15(0, NULL);
check(zero__question(0), TRUE);
check(zero__question(1), FALSE);
check(pos__question(1), TRUE);
check(pos__question(-1), FALSE);
check(neg__question(-1), TRUE);
check(neg__question(1), FALSE);
check(odd__question((100 / 2)), FALSE);
check(even__question((100 / 2)), TRUE);
check(str("a", "b"), "ab");
check(identity(1), 1);
check(inc(1), 2);
check(dec(2), 1);
	$__scope_id = Lexical::init_closure("_Users_aarti_pharen_test_tmplang_functions", 322);
function _Users_aarti_pharen_test_tmplang_functions__lambdafunc32($x, $__closure_id){
	return (1 + $x);
}


function _Users_aarti_pharen_test_tmplang_functions__lambdafunc33($y, $__closure_id){
	return (2 + $y);
}


$fnx = comp(new \PharenLambda("\\_Users_aarti_pharen_test_tmplang_functions__lambdafunc32", Lexical::get_closure_id("_Users_aarti_pharen_test_tmplang_functions", Null)), new \PharenLambda("\\_Users_aarti_pharen_test_tmplang_functions__lambdafunc33", Lexical::get_closure_id("_Users_aarti_pharen_test_tmplang_functions", Null)));
	Lexical::bind_lexing("_Users_aarti_pharen_test_tmplang_functions", 322, '$fnx', $fnx);
check($fnx(1), 4);
check(zero_or_empty__question(1, \PharenVector::create_from_array(array())), TRUE);
check(zero_or_empty__question(0, \PharenVector::create_from_array(array(1, 2))), TRUE);
check(empty__question(\PharenVector::create_from_array(array())), TRUE);
check(empty__question(\PharenVector::create_from_array(array(1))), FALSE);
check(seq__question(\PharenVector::create_from_array(array(1, 2, 3))), TRUE);
check(sequential__question(\PharenVector::create_from_array(array(1, 2, 3))), TRUE);
check(seq__question(seq(\PharenVector::create_from_array(array(1, 2, 3)))), TRUE);
$__listAcessTmpVar2 = hashify(hashify(array('\a' => 1)));
check($__listAcessTmpVar2['\a'], 1);
check(arr(\PharenVector::create_from_array(array(1, 2, 3))), array(1, 2, 3));
check(first(rest(\PharenVector::create_from_array(array(1, 2)))), 2);
check(cons(0, \PharenVector::create_from_array(array(1, 2, 3))), \PharenVector::create_from_array(array(0, 1, 2, 3)));
$__listAcessTmpVar3 = assoc('\a', 1, hashify(array()));
check($__listAcessTmpVar3['\a'], 1);
check(get('\a', hashify(array('\a' => 1))), 1);
check(take(2, \PharenVector::create_from_array(array(0, 1, 2))), \PharenVector::create_from_array(array(0, 1)));
check(drop(2, \PharenVector::create_from_array(array(0, 1, 2))), \PharenVector::create_from_array(array(2)));
check(reverse(\PharenVector::create_from_array(array(1, 2, 3))), \PharenVector::create_from_array(array(3, 2, 1)));
check(interpose("-", \PharenVector::create_from_array(array(1, 2, 3))), \PharenVector::create_from_array(array(1, "-", 2, "-", 3)));
check(partition(3, \PharenVector::create_from_array(array(1, 2, 3, 4, 5, 6, 7, 8, 9))), \PharenVector::create_from_array(array(\PharenVector::create_from_array(array(1, 2, 3)), \PharenVector::create_from_array(array(4, 5, 6)), \PharenVector::create_from_array(array(7, 8, 9)))));
check(interleave(\PharenVector::create_from_array(array(1, 2, 3)), \PharenVector::create_from_array(array(4, 3, 6))), \PharenVector::create_from_array(array(1, 4, 2, 3, 3, 6)));
function _Users_aarti_pharen_test_tmplang_functions__lambdafunc34($a, $b, $__closure_id){
	return ($a + $b);
}


check(zip_with(new \PharenLambda("\\_Users_aarti_pharen_test_tmplang_functions__lambdafunc34", Lexical::get_closure_id("_Users_aarti_pharen_test_tmplang_functions", Null)), \PharenVector::create_from_array(array(1, 2, 3)), \PharenVector::create_from_array(array(4, 5, 6))), \PharenVector::create_from_array(array(5, 7, 9)));
check(seq_join(\PharenVector::create_from_array(array(1, 2, 3, 4, 5)), ","), "1,2,3,4,5");
check((infinity() instanceof PharenLazyList), TRUE);
check((repeat(\PharenVector::create_from_array(array(1, 2, 3))) instanceof PharenLazyList), TRUE);
function _Users_aarti_pharen_test_tmplang_functions__partial2($arg0, $__closure_id){
	$a = Lexical::get_lexical_binding('_Users_aarti_pharen_test_tmplang_functions', 326, '$a', isset($__closure_id)?$__closure_id:0);;
return ($a + $arg0);
}

function _Users_aarti_pharen_test_tmplang_functions__lambdafunc35($a, $__closure_id){
	$__scope_id = Lexical::init_closure("_Users_aarti_pharen_test_tmplang_functions", 326);
	Lexical::bind_lexing("_Users_aarti_pharen_test_tmplang_functions", 326, '$a', $a);


	return new \PharenLambda('\\_Users_aarti_pharen_test_tmplang_functions__partial2', Lexical::get_closure_id("_Users_aarti_pharen_test_tmplang_functions", $__scope_id));
}


check((repeatedly(new \PharenLambda("\\_Users_aarti_pharen_test_tmplang_functions__lambdafunc35", Lexical::get_closure_id("_Users_aarti_pharen_test_tmplang_functions", Null))) instanceof PharenLazyList), TRUE);
function _Users_aarti_pharen_test_tmplang_functions__partial3($arg0, $__closure_id){
	$a = Lexical::get_lexical_binding('_Users_aarti_pharen_test_tmplang_functions', 328, '$a', isset($__closure_id)?$__closure_id:0);;
return ($a + $arg0);
}

function _Users_aarti_pharen_test_tmplang_functions__lambdafunc36($a, $__closure_id){
	$__scope_id = Lexical::init_closure("_Users_aarti_pharen_test_tmplang_functions", 328);
	Lexical::bind_lexing("_Users_aarti_pharen_test_tmplang_functions", 328, '$a', $a);


	return new \PharenLambda('\\_Users_aarti_pharen_test_tmplang_functions__partial3', Lexical::get_closure_id("_Users_aarti_pharen_test_tmplang_functions", $__scope_id));
}


check((iterate(new \PharenLambda("\\_Users_aarti_pharen_test_tmplang_functions__lambdafunc36", Lexical::get_closure_id("_Users_aarti_pharen_test_tmplang_functions", Null)), 100) instanceof PharenLazyList), TRUE);
check((cycle(\PharenVector::create_from_array(array(1, 2, 3))) instanceof PharenLazyList), TRUE);
function _Users_aarti_pharen_test_tmplang_functions__partial4($arg0, $__closure_id){
	return (1 + $arg0);
}


check((cycle_with(new \PharenLambda('\\_Users_aarti_pharen_test_tmplang_functions__partial4', Lexical::get_closure_id("_Users_aarti_pharen_test_tmplang_functions", Null)), \PharenVector::create_from_array(array(1, 2, 3))) instanceof PharenLazyList), TRUE);
check(vals(\PharenVector::create_from_array(array(1, 2, 3))), \PharenVector::create_from_array(array(1, 2, 3)));
function _Users_aarti_pharen_test_tmplang_functions__lambdafunc37($a, $b, $__closure_id){
	return ($a + $b);
}


check(apply(new \PharenLambda("\\_Users_aarti_pharen_test_tmplang_functions__lambdafunc37", Lexical::get_closure_id("_Users_aarti_pharen_test_tmplang_functions", Null)), 1, 2), 3);
function _Users_aarti_pharen_test_tmplang_functions__lambdafunc38($a, $b, $__closure_id){
	return \PharenVector::create_from_array(array($a, $b));
}


check(apply(flip(new \PharenLambda("\\_Users_aarti_pharen_test_tmplang_functions__lambdafunc38", Lexical::get_closure_id("_Users_aarti_pharen_test_tmplang_functions", Null))), 1, 2), \PharenVector::create_from_array(array(2, 1)));
function _Users_aarti_pharen_test_tmplang_functions__partial5($arg0, $__closure_id){
	return (1 + $arg0);
}


function _Users_aarti_pharen_test_tmplang_functions__partial6($arg0, $__closure_id){
	return (2 + $arg0);
}


check(apply(juxt(new \PharenLambda('\\_Users_aarti_pharen_test_tmplang_functions__partial5', Lexical::get_closure_id("_Users_aarti_pharen_test_tmplang_functions", Null)), new \PharenLambda('\\_Users_aarti_pharen_test_tmplang_functions__partial6', Lexical::get_closure_id("_Users_aarti_pharen_test_tmplang_functions", Null))), 1), \PharenVector::create_from_array(array(2, 3)));
check(concat(\PharenVector::create_from_array(array(1)), \PharenVector::create_from_array(array(2))), \PharenVector::create_from_array(array(1, 2)));
check(into(\PharenVector::create_from_array(array(3)), \PharenVector::create_from_array(array(1, 2, 3, 4, 5))), \PharenVector::create_from_array(array(5, 4, 3, 2, 1, 3)));
function _Users_aarti_pharen_test_tmplang_functions__partial7($arg0, $__closure_id){
	return (1 + $arg0);
}


check(reduce(new \PharenLambda('\\_Users_aarti_pharen_test_tmplang_functions__partial7', Lexical::get_closure_id("_Users_aarti_pharen_test_tmplang_functions", Null)), 0, \PharenVector::create_from_array(array(1, 2, 3, 4, 5))), 6);
function _Users_aarti_pharen_test_tmplang_functions__partial8($arg0, $__closure_id){
	return (1 + $arg0);
}


check(reduce_to_str(new \PharenLambda('\\_Users_aarti_pharen_test_tmplang_functions__partial8', Lexical::get_closure_id("_Users_aarti_pharen_test_tmplang_functions", Null)), \PharenVector::create_from_array(array(1, 2, 3))), "234");
function _Users_aarti_pharen_test_tmplang_functions__partial9($arg0, $__closure_id){
	return (1 + $arg0);
}


check(map(new \PharenLambda('\\_Users_aarti_pharen_test_tmplang_functions__partial9', Lexical::get_closure_id("_Users_aarti_pharen_test_tmplang_functions", Null)), \PharenVector::create_from_array(array(1, 2, 3))), \PharenVector::create_from_array(array(2, 3, 4)));
