<?php
require_once('/Users/aarti/pharen/lang.php');
use Pharen\Lexical as Lexical;
use \Seq as Seq;
use \FastSeq as FastSeq;
Lexical::$scopes['_Users_aarti_pharen_test_tmpfunc_calls'] = array();
check(substr("abc", 1), "bc");
check((1 + 2 + 3.1), 6.1);
check(("hello, " . "world"), "hello, world");
check(substr(("foo" . "bar"), 3), "bar");
$__tmpfuncname4 = ("sub" . "str");
check($__tmpfuncname4("abc", 1), "bc");
$foo = "substr";
check($foo("abc", 1), "bc");
