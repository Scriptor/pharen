<?php
namespace pharen\tests\types;
require_once('/Users/aarti/pharen/lang.php');
use Pharen\Lexical as Lexical;
use \Seq as Seq;
use \FastSeq as FastSeq;
Lexical::$scopes['_Users_aarti_pharen_test_tmptypes'] = array();
$__scope_id = Lexical::init_closure("_Users_aarti_pharen_test_tmptypes", 346);
function foo($x){
	return $x;
}

$x = 1;
Lexical::bind_lexing("_Users_aarti_pharen_test_tmptypes", 346, '$x', $x);
check(foo($x), 1);
check(foo(foo($x)), 1);
$var1 = "var1";
$var2 = "var2";
$a = 1;
$var3 = "var3";
$var4 = "var4";
check(foo($a), 1);
function foo1(double $x){
	return $x;
}

$y = 1.5;
Lexical::bind_lexing("_Users_aarti_pharen_test_tmptypes", 346, '$y', $y);
check($y, 1.5);
check($y, 1.5);
function foo2($x){
return FALSE;
}


check(FALSE, FALSE);
check(FALSE, FALSE);
