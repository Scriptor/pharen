Pharen is compiler project that compiles a Lisp-inspired language to PHP.

It is _very_ alpha and still under heavy development.

Language Tutorial/Overview
=================
### Usage
	$ php pharen.php pharen_file1.phn pharen_file2.phn ... pharen_fileN.phn

Each file will be compiled in the order given. Order is important when your code
uses micros, which are stored in the compiler's memory.

By convention Pharen files have the .phn extension. The names of the output files
are extracted from the names of the input files.

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

#### Creating lists
Pharen lists are unassociative PHP arrays, when you just have a series of elements. In Pharen,
you can use either vector literals or use the `array` construct as a function.
	[1 2 3 4]
	(array 1 2 3 4)
Are both the same in the end:
	array(1, 2, 3, 4);
	array(1, 2, 3, 4);
	
#### List ranges
To quickly make a range of integers, you can use the range syntax built into list literals. For example,
to quickly make a list containing all integers from 1 to 5 inclusive:
	[1 .. 5]
which becomes
	array(1, 2, 3, 4, 5);
You can also specify the step to be used between each integer in a range by having at least 2 elements before the ..:
	[1 3 .. 7]
becomes:
	array(1, 3, 5, 7);
Note that with steps, the last element you put in may not be in the final array. For example:
	[1 3 .. 6]
will lose the 6 because mathematically, you can't reach it by add 2's to 1:
	array(1, 3, 5);

#### Creating dictionaries
Dictionaries start with `dict`, then are followed by key-value pairs enclosed in parentheses.
The key-value pairs are one of the instances where Pharen does not follow the typical function call form.
This is because dict takes in *literals*, which are treated by pharen as collections of elements.
	(= fruits (dict
		("a" "apple")
		("b" "banana")))
		
Becomes:
	$fruits = array("a"=>"apple", "b"=>"banana");
	
#### Accessing array/dictionary elements
Two syntaxes are available, both compiling to equivalent code. You can use either the `at` function
or use the `:` prefix for the array's name:
	(at foo_array 1)
	(at fruits "a")
	
	(:foo_array 1)
	(:fruits "a")
	
will both compile to:
	$foo_array[1];
	$fruits["a"];
	
#### Accessing superglobals
Works similarly to `at`, but uses `$` and you don't put the $_ prefix in front of the superglobal's name.
	($ post "some_key")
	($ server "PHP_SELF")
becomes:
	$_POST["some_key"];
	$_SERVER["PHP_SELF"];

#### Cond expressions
Similar to `cond` in other lisps, this special form takes in a series of condition-expression pairs.
If a condition evaluates to true, the expression that comes with it is executed.
	(cond
		((test1) (print "Test1 returns true."))
		((test2) (print "Test2" returns true.")))
becomes
	if(test1()){
		print("Test 1 returns true.");
	}
	else if(test2()){
		print("Test 2 returns true.");
	}
A cond expression can also be embedded in other expressions, this is done by using temporary variables
in the resulting PHP code:
	(= result (cond
		((test1) "test1 works.")
		((test2) "test2 works.")))
becomes
	$__condtmpvar0;
	if(test1()){
		$__condtmpvar0 = "test1 works.";
	}else if(test2()){
		$__condtmpvar0 = "test2 works.";
	}
	$result = $__condtmpvar0;

#### If statements
Parellel the equivalent PHP constructs.
The syntax is `if`, `elseif`, or `else` followed by the condition(except for `else`),
followed by any number of expressions to be executed.
	(if (== myvar "some value")
		(print "myvar equals 'some value'.")
		(print "Another line because I can."))
	(elseif (== myvar "other value")
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
Starts with `fn`, followed by the function's name, parameter list (also implemented as a literal), and the function's body.
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