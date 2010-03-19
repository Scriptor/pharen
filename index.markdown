---
layout: default
title: Introduction to Pharen
---

## Pharen: The Lisp -> PHP Compiler ##

The Pharen language brings together [Lisp](http://en.wikipedia.org/wiki/Lisp_%28programming_language%29)
and [PHP](http://php.net/). This combines Lisp's advantages of uniform syntax and homoiconicity (macros!)
and PHP's advantages of...being <em>everywhere</em>. Here's some sample Pharen and the equivalent PHP:

<div class='code-left'>
{% highlight clojure %}
(fn greet (name)
    (. "Hello " name "!"))
(echo (greet "visitor"))
{% endhighlight %}
</div>
<div class='code-right'>
{% highlight php startinline %}
<?php
function greet($name){
    return "Hello " . $name . "!";
}
echo greet("visitor");
?>
{% endhighlight %}
</div>

This shows some of the main differences (beyond the parentheses). Variables don't need the `$` prefix,
everything, including string concatenation, looks like a function call,
and most things are expressions. The last bit means that a function will return the last evaluated expression
inside it. More will be explained in later sections.

Some of the rationale behind Pharen:
1. Abstract out problems and irregularities in PHP's own parser and the language itself.
2. Compile more sophisticated features, such as closures and macros, to native PHP.
3. Insanity.