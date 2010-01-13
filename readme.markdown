Pharen is compiler project that compiles a Lisp-inspired language to PHP.
The language is also called Pharen.

It is _very_ alpha and still under heavy development.

Language Tutorial/Overview
=================
### Basics
Everything is surrounded by parentheses. No semicolons are needed to end lines.

Strings are always wrapped in double-quotes:
	"This is a valid string."
	'This is not.'

Variables do not have $'s in front of them, as in PHP. Just use the name by itself.
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
This only works for arrays. Creating dictionaries (associative arrays) is explained later.

Special operators in PHP, like + (plus), - (minus), and = (assignment) are also treated like regular
function calls in Pharen by being treated as infix operators. To add a bunch of numbers, write:
	(+ 1 2 3 4)
To assign that to a variable, just write:
	(= sum
		(+ 1 2 3 4))
This will compile to:
	$sum = 1 + 2 + 3 + 4;
Notice that the expression containing the addition was put on another line and indented.
This breaks up the code and makes it more readable.

The full list of infix operators is: "+", "-", "*", ".", "/", "and", "or", "<", ">", "===", "==", '='.

PHP allows variables to be used in place of the function's name. To do that in Pharen,
you will need to use the dollar sign. For example:
	(= myfunc "sqrt")
	($myfunc 9)
becomes:
	$myfunc = "sqrt";
	$myfunc(9);

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
		(print (. "Hello " name "!")))
becomes:
	function greet($name){
		return (print "Hello " . $name . "!");
	}
Pharen functions automatically return the last expression. However, it won't automatically return expressions inside
the bodies of if-statements. You will have to explicitly do that in those cases:
	(fn greet (name)
		(if (== name "Scriptor")
			(return "You  made Pharen!"))
		(else
			(return (. "Hi " name "!")))
			
Note that PHP's string concatenation operator . (dot), is used as a function when you want to
put a bunch of strings and variables together.

### Experimental
Pharen has two tricks up its sleeve. Micros and partial application. They are both highly
experimental and might probably definitely have bugs.

#### Micros
Basically functions that when called, output their own code instead of the call itself. This can prevent the overhead
of calling a function. Redoing the function definition example from before:
	(micro greet (name)
		(print (. "Hello " name "!")))
	(greet "Arthur Dent")
becomes
	print("Hello " . "Arthur Dent" . "!");

The micro definition itself doesn't output any PHP code. In the future, micros will likely be useful
in loop constructs, which I have yet to implement.

#### Partials
Whenever a Pharen function is called without all the necessary parameters, pharen makes a temporary function
to act as an intermediary. It's similar to how you'd use currying in Haskell.

For example, say you have the following function (that normally takes two numbers) and array:
	(fn add (x y)
		(+ x y))
	(= nums (array 1 2 3))
If you want to add 10 to each of those numbers, do the following (using the map function written in Pharen):
	(map nums (add 10))
which becomes:
	function __partial0($arg0){
		return add_three_nums(10, $arg0);
	}
	map($nums, "__partial0");
	
### lang.phn
lang.phn is a file containing a few basic functions geared towards functional programming
(first, rest, cons, apply, and map). In the future, this file will contain all default functions.

Milestones to Reach
===================
The first goal will be to create a language that more or less covers enough of PHP
to write basic apps.

The second goal will be for Pharen to be a good alternative to using straight PHP. The benefits
will be cleaner syntax and a way to abstract out annoyances in PHP.

The third goal will be to create abstractions for complicated features to be compiled to PHP.
At this point, I hope for Pharen to be more of a Lisp than just PHP with a lot of parentheses.