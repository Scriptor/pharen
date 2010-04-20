---
layout: default
title: Introduction to Pharen
---

## Introduction to Pharen ##

Pharen is a compiler that takes a [Lisp](http://en.wikipedia.org/wiki/Lisp_%28programming_language%29)-like
language and turns it into [PHP](http://php.net/) code. This combines Lisp's advantages of uniform syntax and homoiconicity (among others)
and PHP's advantage of...being *everywhere*. Here's some sample Pharen and the equivalent PHP:

<div class='code-left'>
{% highlight clojure %}
(fn greet-person (name)
    (. "Hello " name "!"))

(echo (greet-person "visitor"))
{% endhighlight %}
</div>
<div class='code-right'>
{% highlight php startinline %}
<?php
function greet_person($name){
    return "Hello " . $name . "!";
}

echo greet_person("visitor");
?>
{% endhighlight %}
</div>

This shows some of the differences between the two languages. Variables don't need the `$` prefix,
hyphens in names for functions (and variables) are converted to underscores,
everything, including string concatenation, looks like a function call,
and most things are expressions. The last one means that a function will automatically return the 
last evaluated expression inside it. More will be explained in later sections.

Some of the rationale behind Pharen:
1. Abstract out problems and irregularities in PHP's own parser and the language itself.
2. Compile more sophisticated features, such as closures and macros, to native PHP.
3. Insanity.