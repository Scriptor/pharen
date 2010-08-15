---
layout: default
title: Pharen Macro Tutorial
---

## Macro Tutorial
This is a brief guide to learning macros. It attempts to give a basic introduction.	However, macros are a complex feature and the best way to master them is through constant practice.

The first thing you need to understand is that when you call a macro, any arguments passed to it are *not* evaluated immediately. For example:

{% highlight clojure %}
(some-fn (* 2 3))
(some-macro (* 2 3))
{% endhighlight %}

With `some-fn`, the product of 2 and 3 is first calculated, then passed as an argument. With `some-macro`, `(* 2 3)` becomes a list with three elements, `*` and the two numbers. This means the macro can choose what parameters it will evaluate. We can even ignore parameters entirely:

{% highlight clojure %}
(defmacro no-evaluation (expr)
  (print "Nothing is evaluated."))
(no-evaluation (exp 100000 100000))
{% endhighlight %}

Even though we are passing `no-evaluation` a very expensive computation, it will never be performed. All that happens is that a message will be printed during compile-time if this macro is called (macros are executed during compilation).

The second thing to understand about macros is that not only can they take *in* unevaluated code, but they can *return* it as well. This can be done using the single-quote symbol: `'`.

{% highlight clojure %}
(defmacro square-10 ()
  '(* 10 10))
(square-10)
{% endhighlight %}

Quoting an expression is equivalent to parameters not being evaluated, except it is used inside the body of a macro while parameters are unevaluated by default. The call to `square-10` will be directly replaced with `(* 10 10)` when this code is compiled.

Let's create a simplified version of the [when](/pharen/reference.html#when) construct. It will take one test expression and one body expression, which will be evaluated if and only if the test is true. Note that this would not be possible to do with a function since the body expression would be evaluated as soon as the function is called.

{% highlight clojure %}
(defmacro simple-when (test expr)
  '(if ~test
     ~expr
     FALSE))
{% endhighlight %}

First, notice that the `if` expression is quotable just as any other. Next, the unquote  symbol: `~`, is introduced. This is the opposite of quoting and forces the macro to evaluate an otherwise unevaluated parameter. We want the code that is *inside* `test` and `body`. If we did not unquote them, then we simply would end up with variables called `test` and `expr`.

Finally, here is the full implementation of the when macro.

{% highlight clojure %}
(defmacro full-when (test &body)
  '(if ~test
     ~@body
     FALSE))
{% endhighlight %}

Here, we combine splats with macros since when is supposed to take an arbitrary number of body expressions. Since a splat normally creates a list, we break the list up and splice each element in using the splice symbol: `@`. Since we still want the actual contents of `body`, we also have to unquote it.