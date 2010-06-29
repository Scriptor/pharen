---
layout: default
title: Pharen Tutorial
---

## Prerequisites ##
This tutorial is somewhat Unix-oriented and requires knowledge of using its command-line, so Windows users may need to adapt some of the instructions. However, most of the content of this tutorial should be platform-agnostic.

You will need a server that can handle PHP files and a text editor. If you want language integration you can set your editor to use a plugin for another Lisp. For example, I currently use VimClojure and have it set to treat Pharen files like Clojure files.

## Getting set up ##
First, get Pharen from the [download page](/download.html). Then open up a shell, cd into the directory the Pharen files are located and run:

{% highlight bash %}
./install.sh
{% endhighlight %}

This installs the `pharen` (for the compiler) and `phake` (a build/utility tool) commands so you can use them from anywhere.

Optional: You can also run Pharen's test suite 

## Hello world #
Open a text editor (any should work) and enter the following code:

{% highlight clojure %}
(print "Hello, world!")
{% endhighlight %}
