---
layout: default
title: Pharen Basics
---

## Pharen Basics ##
### Atomic Types ### {#atomic-types}
Strings, numbers, booleans, and null are the main atomic (single value) expressions in Pharen. Strings are always double-quoted
and booleans and null should be in all-caps so that Pharen can recognize them. Otherwise, they are the same 
as in PHP:

{% highlight clojure %}
"This is a string"
1  2.3  -4
TRUE FALSE NULL
{% endhighlight %}

### Comments ### {#comments}
Comments start with a semicolon and continue to the end of the line.
{% highlight clojure %}
; This is a comment
(this is code)
{% endhighlight %}

### Variables ### {#variables}
Variables do not have the `$` prefix unless used as a function name (explained below). To define a single variable
in the current scope, use the `def` keyword:

<div class='code-left'>
{% highlight clojure %}
(def myvar "my value")
(def some-number (* 6 9))
{% endhighlight %}
</div>
<div class='code-right'>
{% highlight perl %}
$myvar = "my value";
$some_number = 6 * 9;
{% endhighlight %}
</div>

To define multiple variables at the same time and and create a _new_ scope for them, use `let`:

{% highlight clojure %}
; Create three variables, with the third variable's value being
; an expression using the two previous variables
(let (
    (greeting "hello")
    (username "visitor")
    (full-text (. greeting " " username "!")))
	
    (echo full-text))
; Output: hello visitor!
{% endhighlight %}

[Scope](scope.html) is much more important in Pharen than in PHP, which is why there is special
keyword just to create variables in a new scope.

##### Superglobals #### {#superglobals}
The `$` keyword is provided for quick access to superglobals such as $_GET and $_POST:

{% highlight clojure %}
; $_POST["username"]
($ post "username")

; $_SERVER["PHP_SELF"]
($ server "PHP_SELF")
{% endhighlight %}

### Function Calls ### {#function-calls}
Function calls are always in prefix notation as in other lisps. The function name is followed by the arguments,
using spaces as delimiters. Everything is then enclosed by parentheses. 

<div class='code-left'>{% highlight clojure %}
(func arg1 arg2 argN)
{% endhighlight %}</div>

<div class='code-right'>{% highlight perl %}
func($arg1, $arg2, $argN);
{% endhighlight %}</div>

If you want to use a variable
as the function name:

{% highlight clojure %}
($funcvar arg1 arg2 argN)
{% endhighlight %}

### Infix Operators ### {#infix-operators}
Infix operators, such as boolean and arithmetic operators, are also used as regular function calls.
For boolean operators, `and` and `or` are used instead of `&&` and `||`.
In the resulting PHP, the operator is interspersed between its arguments:

<div class='code-left'>
{% highlight clojure %}
(+  2
    (* 4 2)
    (- 5 6)))

(or value1
    (and value2 (some-func)))
{% endhighlight %}
</div>
<div class='code-right'>
{% highlight perl %}
(2 + (4 * 2) + (5 - 6)))

($value1 or ($value2 and some_func()))
{% endhighlight %}
</div>

One of the advantages of prefix notation is the lack of precedence rules; everything is already
enclosed in parentheses. The parentheses are kept in the resulting PHP code to keep the intended precedence order.