<?php
require_once('/Users/aarti/pharen/lang.php');
use Pharen\Lexical as Lexical;
use \Seq as Seq;
use \FastSeq as FastSeq;
Lexical::$scopes['_Users_aarti_pharen_test_tmpliterals'] = array();
check(8, (4 * 2));
check("abc", ("a" . "bc"));
check(TRUE, TRUE);
check(\PharenVector::create_from_array(array(1, 2, 3)), \PharenVector::create_from_array(array(1, 2, 3)));
check(\PharenVector::create_from_array(array("list", "of", "strings")), \PharenVector::create_from_array(array("list", "of", "strings")));
check(hashify(array(1 => 2, "foo" => "bar")), hashify(array("foo" => "bar", 1 => 2)));
check('\strstr', "\\strstr");
check('\foo_bar_baz', "\\foo_bar_baz");
