Pharen is compiler project that compiles a Lisp-inspired language to PHP.
The language is also called Pharen.

It is _very_ alpha and still under heavy development.

Language Tutorial/Overview
=================
### Terms
pharen - The actual compiler
Pharen - The language or the project in general
dictionary - Associative array in PHP.

### Basics
Everything is surrounded by parentheses. No semicolons are needed to end lines.

Strings are always wrapped in double-quotes:
	"This is a valid string."
	'This is not.'

Variables do not have $'s in front of them, as in PHP, just use them as is.
However, if you want to use a variable as a function name, you will need to
use the dollar sign. This will be explained later.

Constants are anything that is all upper-case. This will probably change later.

### Function Calls
Basic function calls work much like in other lisps. The general form is:
	(funcname arg1 arg2 arg3...argN)
Each function call is surrounded by parentheses. The first element is the function's name,
and everything after it is an argument passed to the function. For example, the following Pharen code:
	(print "hello world!")
will be compiled to the following PHP code:
	print("hello world!");
Arguments can be function calls themselves, the following Pharen:
	(sqrt (abs -10))
will compile to:
	sqrt(abs(-10));

Since PHP's array creation operator looks like a function call, it fits in nicely with Pharen:
	(array 1 2 3)
becomes
	array(1, 2, 3);
This only works for arrays. Creating dictionaries is explained later.

Special operators in PHP, like + (plus), - (minus), and = (assignment) are also treated like regular
function calls in Pharen by being treated as infix operators. To add a bunch of numbers, write:
	(+ 1 2 3 4)
Note that the function's name, +, is simply interspersed among the arguments.
To assign that to a variable, just write:
	(= sum
		(+ 1 2 3 4))
This will compile to:
	$sum = 1 + 2 + 3 + 4;
Notice that the expression containing the addition was put on another line and indented.
This breaks up the code and makes it more readable.

The full list of infix operators is: "+", "-", "*", ".", "/", "and", "or", "<", ">", "===", "==", '='.

### Special Forms:
Some things that look like function calls in Pharen are completely different things in PHP.
They are implemented as special forms.

#### Creating dictionaries.
Dictionaries start with `dict`, then are followed by key-value pairs enclosed in parentheses.
The key-value pairs are one of the instances where Pharen does not follow the typical function call form.
This is because dict takes in *literals*, which are treated by pharen as collections of elements.
	(= fruits (dict
		("a" "apple")
		("b" "banana")))
		
Becomes:
	$fruits = array("a"=>"apple", "b"=>"banana");
	
#### Accessing array/dictionary elements
Here we use `at`, followed by the variable's name and the index/key:
	(at foo_array 1)
	(at fruits "a")
becomes:
	$foo_array[1];
	$fruits["a"];
	
#### Accessing superglobals
Works similarly to `at`, but uses `$` and you don't put the $_ prefix in front of the superglobal's name.
	($ post "some_key")
	($ server "PHP_SELF")
becomes:
	$_POST["some_key"];
	$_SERVER["PHP_SELF"];

#### If statements
Mostly self-explanatory. 
	(if (== myvar "some value")
		(print "myvar equals 'some value'.")
		(print "Another line because I can."))
	(else if (== myvar "other value")
		(print "myvar equals 'other value'."))
	(else
		(print "I don't know what's in myvar."))
becomes:
	if($myvar == "some value"){
		print("myvar equals 'some value'.");
		print("Another line because I can.");
	}
	else if($myvar == "other value"){
		print "myvar equals 'other value'.";
	}
	else{
		print "I don't know what's in myvar.";
	}
	
#### Function definitions
Starts with `fn`, the function's name, parameter list (also implemented as a literal), and the function's body.
	(fn greet (name)
		(print (. "hello " name "!")))
becomes:
	function greet($name){
		print "hello " . $name . "!";
	}
Note that PHP's string concatenation operator . (dot), is used as a function when you want to
put a bunch of strings and variables together.

The first goal will be to create a language that more or less covers enough of PHP
to write basic apps.

The second goal will be for Pharen to be a good alternative to using straight PHP. The benefits
will be cleaner syntax and a way to abstract out annoyances in PHP.

The third goal will be to create abstractions for complicated features to be compiled to PHP.
At this point, I hope for Pharen to be more of a Lisp than just PHP with a lot of parentheses.