<?php
namespace foo;
require_once('/Users/aarti/pharen/lang.php');
use Pharen\Lexical as Lexical;
use \Seq as Seq;
use \FastSeq as FastSeq;
Lexical::$scopes['_Users_aarti_pharen_test_tmpns'] = array();
function test(){
	return 42;
}

namespace foo\bar;
function test(){
	return 100;
}

namespace bar;
function test(){
	return 7;
}

use foo;
check(foo\test(), 42);
check(test(), 7);
namespace baz;
use foo as f;
use foo\bar as fb;
check(f\test(), 42);
check(fb\test(), 100);
