---
layout: default
title: Pharen Reference
---

## Pharen Reference ##
### Data Types and Basics ### {#data-types-and-basics}
Since much of the reference relies on using code examples, the following list of points should be read to ensure the examples are fully understood.
- Comments start with a semicolon and go to the end of the line.
- Numbers are the same as in PHP. Integers, floats, and positive/negative numbers will work as they do in PHP.
- Strings are always double-quoted and are equivalent to PHP's double-quoted strings. All escape characters
as well as variable interpolation will work.
- Variables do not have the `$` prefix as they do in PHP.
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
An expression is anything that returns a value. Everything in Pharen is an expression and every Pharen program is created by mixing and combining simpler expressions to create more sophisticated ones. The datatypes, variables, and constants mentioned above are some of the most basic expressions in Pharen and are called `atomic` values. On a higher level, Pharen follows two rules:

1. Every function call is an expression consisting of the function name and its arguments, all enclosed in parentheses.
2. *Everything* looks like a function call, even if-statements and function definitions.

{% highlight clojure %}
; Here's a basic function call, where my-function is called and passed a string and a variable
(my-function "argument 1" argument-2)
; Since expressions return values, they can be used anywhere that atomic values can be used.
(my-function (another-function "argument") argument-2)
{% endhighlight %}

### Infix Operators ### {#infix-operators}
Infix operations, such as number addition and string concatenation, follow the looks-like-function-call rule.

<div id='code-left'>
{% highlight clojure %}
; Perform some math
(+ 15
    (* 4 5)
    (- 10 3))

; Join two strings
(. "Hello, " "world!")
{% endhighlight %}
</div>
<div id='code-right'>
{% highlight perl %}
15 + (4 * 5) + (10 - 3);
"Hello, " . "world!";
{% endhighlight %}
</div>

### Defining variables ### {#defining-variables}
To define a single variable at a time in the current scope, use the `def` construct.

{% highlight clojure %}
(def name "Arthur Dent")
(print name)
{% endhighlight %}

To create one ore more variables inside an entirely new scope, use `let`.

{% highlight clojure %}
(let (
    (full-name "Arthur Dent")
    (answer 42))

    (print (. "Full name: " full-name))
    (print (. "Answer: " answer)))
{% endhighlight %}

### Lists ### {#lists}
Pharen's lists are sequences of items. They are the equivalent of arrays in PHP without explicitly set keys. Lists can contain any data type as well as hold different data types at the same time. For example, lists of strings, numbers, or strings *and* numbers are all valid. Unlike PHP, lists can be directly created using square brackets instead of the `array` construct used in PHP. Here are some examples of directly creating lists in their raw form, list literals.

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
Dictionaries encapsulate the mapping side of PHP arrays. They work just as associative arrays work in PHP, a key (of type integer or string) can map to any value. The `dict` keyword starts the dicionary, followed by parentheses-enclosed pairs.

<div class='code-left'>
{% highlight clojure %}
; A mapping of urls to actions in a web app
(dict
    ("/" "IndexController")
    ("/blog" "BlogController")
    ("/code" "CodeController"))
{% endhighlight %}
</div>
<div class='code-right'>
{% highlight php %}
<?php
array(
    "/" => "IndexController",
    "/blog" => "BlogController",
    "/code" => "CodeController"
);
?>
{% endhighlight %}
</div>

Integer keys can be set explicitly, but the dictionary is still maintained in the order you define it in. 

<div class='code-left'>
{% highlight clojure %}
(dict
    (5 "Five")
    (2 "Two")
    (3 "Three"))
{% endhighlight %}
</div>
<div class='code-right'>
{% highlight php %}
<?php
array(
    5 => "Five",
    2 => "Two",
    3 => "Three"
);
?>
{% endhighlight %}
</div>
Iterating through and printing the values in this example would print "Five" "Two" "Three".

### Accessing elements from lists and dictionaries ### {#accessing-elements}
Lists and dictionaries are accessed using the same form. Prefix the name of the collection with a colon and pass the index/key as an argument.

<div class='code-left'>
{% highlight clojure %}
(def colors ["blue" "orange" "red" "green"])
(def planets (dict
    ("smallest" "Mercury")
    ("gaseous" ["Jupiter" "Saturn" "Uranus" "Neptune"]))))

(print (:colors 2))
(print (:planets "smallest"))
(print (:(:planets "gaseous") 0))
{% endhighlight %}
</div>
<div class='code-right'>
{% highlight php %}
<?php
$colors = array("blue", "orange", "red", "green");
$planets = array(
    "smallest" => "Mercury",
    "gaseous" => array("Jupiter" "Saturn" "Uranus" "Neptune")
);

print $colors[2];
print $planets["smallest"];
print $planets["gaseous"][0];
{% endhighlight %}
</div>

The above will print red, Mercury, and Jupiter. Notice the way accessing elements in embedded structures is done in the third print expression. Remember that since everything is an expression, you can think of `(:planets "gaseous")` as a function call that returns the value for the key "gaseous". This value happens to be a list, which is passed to the outer list access "function call", this one asking for the value at position 0.

List access can also be done with literals, unlike in PHP.

{% highlight clojure %}
(print (:["blue" "orange" "red" "green"] 2))
(print (:(dict
    ("red" "apple")
    ("green" "kiwki")) "green"))
{% endhighlight %}

This will print red and kiwi. While not always useful, this shows the extent of the everything-as-expression rule in Pharen. These literals are just expressions that return either a list or a dictionary, which is all the list access form needs. They could just as easily be replaced by variables or function calls, as long as those too return a list or a dictionary.
