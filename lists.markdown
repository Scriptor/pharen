---
layout: default
title: Lists and Dictionaries
---

## Lists and Dictionaries
### List Literals ### {#list-literals}
A list is just a collection of values of any type. They are equivalent to arrays in PHP that don't have keys explicitly
set by the programmer.
When creating lists, separate each item with a space and enclose everything in square brackets.

<div class='code-left'>
{% highlight clojure %}
; The numbers from 1 to 7
[1 2 "three" "four" 5]

; A list of strings
["foo" "bar" "baz"]

; Lists can be nested in each other
[[1 2] ["abc" "def"]]
{% endhighlight %}
</div>
<div class='code-right'>
{% highlight php %}
<?php
array(1, 2, "three", "four", 5);
 

array("foo", "bar", "baz");

 
array(array(1, 2), array("abc", "def"));
?>
{% endhighlight %}
</div>

### Dictionaries ### {#dictionaries}
Dictionaries are created using the `dict` keyword, followed by key-value pairs enclosed in parentheses.
They are the equivalent to associative arrays where keys *are* explicitly set. Keys can be either integers
or strings and values can be any Pharen expression.

<div class='wide-code'>
	<div class="code-left">
{% highlight clojure %}
(dict
    ("title" "Color of Magic")
    ("author" "Terry Pratchett"))

(dict
    ("functional" ["haskell" "lisp" "scala"])
    ("imperative" ["c" "php" "python"]))
{% endhighlight %}
	</div>
	<div class='code-right'>
{% highlight php %}
<?php
array(
    "title" => "Color of Magic",
    "author" => "Terry Pratchett"
);

array(
    "functional" => array("haskell", "lisp", "scala"),
    "imperative" => array("c", "php", "python")
);
?>
{% endhighlight %}
	</div>
</div>

### Retrieving elements ### {#retrieval}
Items from lists and dictionaries are fetched by their key. For lists, this would be the index
of the item. In the following code a dictionary called `user` and a list called 
{% highlight clojure %}
(define user (dict
	("name" 
{% endhighlight %}