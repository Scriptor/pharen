---
layout: default
title: Pharen Language Reference
---

## Pharen Language Reference ##
### Data Types and Basics ### {#data-types-and-basics}
Since much of the reference relies on using code examples, the following list of points should be read to ensure the examples are fully understood.
- Comments start with a semicolon and go to the end of the line.
- Numbers (integers, floats, and positive/negative numbers) work the same as in PHP.
- Strings are always double-quoted and are equivalent to PHP's double-quoted strings. All escape characters will work.
- Variables do not have the `$` prefix as they do in PHP (unless a variable is used as a function name).
- Constants should not have any lower-case letters.
- Dashes are legal characters in names, they are converted to underscores in the resulting PHP code.

{% highlight clojure %}
; All comments are one-line comments
1 2.0 -3
"Simple string with a newline.\n"
"String with a $variable."
myvariable function-name2
SOME-CONSTANT
{% endhighlight %}

### Expressions ### {#expressions}
An expression is anything that results in a value. Almost everything in Pharen is an expression and every Pharen program is created by mixing and combining simpler expressions to create more sophisticated ones. The datatypes, variables, and constants mentioned above are some of the most basic expressions in Pharen and are called `atomic` values. On a higher level, Pharen follows two rules:

- Every function call is an expression consisting of the function name and its arguments, all enclosed in parentheses.
- *Everything* looks like a function call, even if-statements and function definitions.

{% highlight clojure %}
; A basic function call, my-function is passed a string and a variable
(my-function "argument 1" argument-2)
; Expressions can be used anywhere that atomic values can be used.
(my-function (another-function "argument 1") argument-2)
{% endhighlight %}

### Infix Operators ### {#infix-operators}
Infix operations, such as number addition and string concatenation, follow the looks-like-function-call rule.

{% highlight clojure %}
; Perform some math
(+ 15
    (* 4 5)
    (- 10 3))

; Join two strings
(. "Hello, " "world!")
{% endhighlight %}

The full list of infix operators:
"+", "-", "\*", ".", "/", "and", "or", "=", "=&", "<", ">", "<=", ">=", "===", "==", "!=", "!=="

### Defining variables ### {#defining-variables}
To define a single variable in the current scope, use the `def` construct.

{% highlight clojure %}
(def name "Arthur Dent")
(print name)
{% endhighlight %}

To create one ore more variables inside an entirely new scope, use `let`.

{% highlight clojure %}
(let [full-name "Arthur Dent",
      answer 42]

    (print (. "Full name: " full-name))
    (print (. "Answer: " answer)))
{% endhighlight %}

### Lists ### {#lists}
Pharen's lists are sequences of items. They are the equivalent of arrays in PHP without explicitly set keys. Lists can contain any data type. For example, lists of strings, numbers, or strings *and* numbers are all valid. Unlike PHP, lists are created using literals instead of the `array` construct.

{% highlight clojure %}
; A list containing the numbers from one through 5
[1 2 3 4 5]

; List literals can be used to hard-code data, such as a list of URLs
["http://google.com" "http://wikipedia.org" "http://zombo.com"]
{% endhighlight %}

#### Ranges #### {#ranges}
To quickly create lists of numbers you can use the range syntax.

{% highlight clojure %}
; All the integers from 1 to 1000, inclusive
[1 .. 1000]
{% endhighlight %}

Ranges can also be defined with a step to go from one number to the next. The step is the difference between the first and second numbers.

{% highlight clojure %}
; All even numbers from 1 to 1000
[2 4 .. 1000]
{% endhighlight %}

### Dictionaries ### {#dictionaries}
Dictionaries encapsulate the mapping side of PHP arrays. They work just like associative arrays, a key (of type integer or string) can map to any value. They are created using braces.

{% highlight clojure %}
; A mapping of urls to actions in a web app
{"/" "IndexController",
 "/blog" "BlogController",
 "/code" "CodeController"}
{% endhighlight %}

The commas act as white-space and make the code more readable, the code would be just as functional without them. `dict` simply looks at every two nodes inside it as a key-value pair. 

Integer keys can be set explicitly, but the dictionary is still maintained in the order you define it in. 

{% highlight clojure %}
{5 "Five",
 2 "Two",
 3 "Three"}
{% endhighlight %}
Iterating through and printing the values in this example would print "Five" "Two" "Three".

### Accessing elements from lists and dictionaries ### {#accessing-elements}
Lists and dictionaries are accessed using the same form. Prefix the name of the collection with a colon and pass the index/key as an argument.

{% highlight clojure %}
(def colors ["blue" "orange" "red" "green"])
(def planets {"smallest" "Mercury",
              "gaseous" ["Jupiter" "Saturn" "Uranus" "Neptune"]})

(print (:colors 2))
(print (:planets "smallest"))
(print (:planets "gaseous" 0))
{% endhighlight %}
The above will print red, Mercury, and Jupiter. In the last line, multiple arguments are passed to access nested elements. It is the equivalent of `$planets["gaseous"][0]` in PHP.

List access can also be done directly on literals, unlike in PHP.

{% highlight clojure %}
(print (:["blue" "orange" "green" "red"] 2))
(print (:(dict
    ("red" "apple")
    ("green" "kiwki")) "green"))
{% endhighlight %}

This will print red and kiwi.

### Superglobals ### {#superglobals}
Pharen provides the `$` form to access superglobals such as $_POST and $_SERVER. It takes the type of superglobal and a key as arguments.

{% highlight clojure %}
(def name ($ post "name"))
(def php-self ($ server "PHP_SELF"))
{% endhighlight %}

### Variables as Functions ### {#variables-as-functions}
In PHP, it's possible to use a variable name where you would use a function name, as in `$foo("bar");`, the same can be done in Pharen, as long as you keep the `$` prefix:

{% highlight clojure %}
(def func "implode")
($func ["hello" " " "world"])
{% endhighlight %}

This will call the implode function on the array.

#### Function Name Literals #### {#function-name-literals}
Most of the time strings work fine when you need to pass around a function name, like in the example above. However, Pharen will not convert dashes to underscores inside strings. If you wanted to pass the function `my-function` as an argument as a string, you would have to use `"my_function"`. Since this can look inconsistent, Pharen provides the `#` prefix. The following two lines are equivalent:

{% highlight clojure %}
(array-map "my_function" [1 2 3 4])
(array-map #my-function [1 2 3 4])
{% endhighlight %}

### Special Forms ### {#special-forms}
Pharen comes with a few special forms to form the basis for control structures. Unlike PHP's control structures, special forms still count as expressions.

### If ### {#if}
Takes a test expression and two body expressions. If the test evaluates to true, it runs the first body expression, if it is false, the second one will be run.

{% highlight clojure %}
(if (== 3 3)
  (print "Cool, math still works.")
  (print "Math no longer works..."))
{% endhighlight %}

Since `if` is just an expression, you can also embed it into other expressions. The following will have the same behavior as the above code:

{% highlight clojure %}
(print
  (if (== 3 3)
    "Cool, math still works."
    "Math no longer works..."))
{% endhighlight %}

### Do ### {#do}
The main limitation of `if` is that it only runs one expression. You can get around that with `do`, which combines a series of expressions into one.

{% highlight clojure %}
(if (== 3 3)
  (do
    (print "First expression.")
    (print "Second expression."))
  (print "Math no longer works..."))
{% endhighlight %}

### Cond ### {#cond}
`cond` is Pharen's other built-in special form for conditionals. Unlike `if`, it can take any number of test expressions, and each test expression can take any number of body expressions.

{% highlight clojure %}
(cond
  ((== 1 3) (print "This can't be right."))
  ((== 2 3) (print "Nope, this shouldn't show up either."))
  ((== 3 3) (print "That works!")
            (print "And here's another line!"))
  (TRUE (print "Something must have gone wrong.")))
{% endhighlight %}

Notice that each test-body grouping is wrapped in parentheses. Also, `TRUE` is used as a catch-all whose linked body expressions will be run if all other test expressions fail.

### When ## {#when}
`when` gets a quick mention even though it's technically not built-in, instead, it's a [macro](#macro) that's defined in [lang.phn](/pharen/lang). It takes one test expression and a series of body expressions. If the test returns true, everything in the body is evaluated, otherwise, the whole thing automatically returns false. `when` is best used in situations where you only need something to happen if the test expression is true while otherwise you don't care.

{% highlight clojure %}
(when (isset ($ post "submit"))
  (print "The form was successfully submitted.")
  (process-form-data))
{% endhighlight %}

We only want to do something if we detect a form submission, otherwise, the code simply moves on.

### Functions ### {#functions}
The `fn` form is used to create regular ol' lispy functions. The things that make Pharen's functions different from PHP's are:

- The last evaluated expression will automatically be the return value of the function.
- They are expressions, you can even embed them in function calls.
- They are lexically scoped, variables declared in an outer function will be available to inner ones.
- They can be nested inside each other without having to worry about evaluating the same function definition twice.

{% highlight clojure %}
(fn create-user (name password)
  (mysql-query (sprintf "INSERT INTO users VALUES(%s, %s);" (hash-func name) (hash-func password)))
  (printf "New user created with id:%s " (mysql-insert-id)))
{% endhighlight %}

Here is a more complex example showing the different ways you can stretch Pharen functions' features:

{% highlight clojure %}
(fn fact (n)
  (fn fact-iter (n acc)
     (if (<= n 0)
       acc
       (fact-iter (- n 1) (* acc n))))
  (fact-iter n 1))
{% endhighlight %}

This is a factorial function that uses a nested "worker" function to take advantage of [tail recursion](#tre). The outer function's sole purpose is to provide `fact-iter` with a starting value.

Since the `if` expression is the last (and only) expression in `fact-iter`, the Pharen compiler figures out that that's what it needs to return. `if` knows that when it's acting as the return expression, it actually needs to make sure either of its two body children are returned. The same happens when `fact-iter` is actually called inside the body of `fact`.

#### Tail Recursion Elimination #### {#tre}
In the above example, there is a good reason to create a nested function. Notice how the last thing fact-iter is doing is calling itself again, this is known as tail recursion. While normally recursion can be expensive memory-wise, the compiler optimizes this into a while loop. This is called tail recursion elimination (TRE). The result is equivalent to a non-optimized version, but uses constant memory, which frequently means it is faster.

TRE is an important component of Pharen code since it emphasizes immutability and recursion. For example, Pharen's functions for dealing with dictionaries, `reduce-pairs` and `map-pairs` use this technique to maintain some efficiency.

#### More on Lexical Scope #### {#scope}
Pharen follows [lexical scoping](http://en.wikipedia.org/wiki/Scope_%28programming%29#Lexical_scoping) rules like other lisps and unlike PHP. Formally, this means variables can only be accessed at any point at or inside the level it is defined. Practically, this means you can have access to variables created in an outer function from inside a nested function, as well as other useful tricks such as [closures](#closures).

{% highlight clojure %}
(fn outer (a)
  (fn inner (b)
    (+ a b))
  (inner 3))
(outer 4)
{% endhighlight %}

The above will print 7, since `a` is bound to 4 and `b` is bound to 3. Even though `a` is not defined inside the function called `inner`, it is still accessible because `inner` is in the same lexical scope

### Lambdas and Closures ### {#lambdas}
Anonymous functions in Pharen are created using the `lambda` form. They are useful when you simply want to pass along a function as a parameter and don't need to give it a name.

{% highlight clojure %}
(map (lambda (x)
             (* 2 x))
     [1 2 3 4])
{% endhighlight %}

`map` takes a function and a list, calls the function with each item in the list, and returns a new list. So the above code simply returns a new list with double the values of the old one. A lambda keeps things simpler than having to worry about naming a function. Additionally, when combined with lexical scope anonymous functions can enclose lexically scoped variables at the time of its creation. Say

#### Partial application #### {#partials}
If the lambda syntax is too bulky and all you need to do is a simple computation, you can partially apply a function. This means you call a function with fewer than than the minimum number of arguments. The compiler creates another function that remembers the arguments already given and then takes any ungiven ones as its own parameters.

It is best explained by an example, to rewrite the above code with partial application:

{% highlight clojure %}
(map (* 2) [1 2 3 4])
{% endhighlight %}

Since the `*` function needs at least two arguments but is only passed the '2', the compiler creates a new function takes takes one more argument and multiplies that by 2. That one more argument is provided by `map` from each item in the list.

#### Splats #### {#splats}
Sometimes functions need to have an arbitrary number of parameters. In PHP, this would require calling `func_get_args`. Pharen simplifies this by providing splats, created by prepending a parameter name with an ampersand. Notice how the `map` function above takes a list as the second parameter. If you want, you can create a wrapper function that instead takes any number of arguments:

{% highlight clojure %}
(fn my-map (f &xs)
  (map f xs))
{% endhighlight %}

The first parameter is still a function name, but after that any others become part of an implicitly created variable called xs which is then available to you as a list.

### Macros ### {#macros}
With macros, unevaluated code can be used as if it was a data structure that could be processed and manipulated as needed. In Pharen, they are defined using `defmacro`.