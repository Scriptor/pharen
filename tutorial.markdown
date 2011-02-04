---
layout: default
title: Pharen Tutorial
---

### About this tutorial{#goal}
This tutorial will you get started with Pharen by writing a bare-bones pastebin. Each section is split into a code-centered portion and a more in-depth explanation. Every code sample starts with a comment containing the filename the code should go in. You can initially skip the in-depth stuff to get something up and running fast, then read the rest later. The finished code for the pastebin is available [on Github](http://github.com/scriptor/pastebin).

### Prerequisites {#prerequisites}
This tutorial is somewhat Unix-oriented and requires knowledge of its command-line. For now, there is no installation script for Windows. However, most of it is platform-agnostic.

You will need a text editor and a server that can handle PHP files. If you want language integration you can set your editor to use a plugin for another Lisp. For example, I currently use VimClojure and have it treat Pharen (.phn) files as Clojure files.

### Getting set up {#set-up}
First, get Pharen from the [download page](/pharen/download.html). Then open up a shell, cd into the directory the Pharen files are located and run:

{% highlight bash %}
$ sudo ./install.sh
{% endhighlight %}

This installs the `pharen` command so you can use it from anywhere.

### Hello world {#hello-world}
Open a text editor (any should work) and enter the following code:

{% highlight clojure %}
; hello.phn
; This is a comment and won't show up
(print "Hello, world!")
{% endhighlight %}

Save this in your server's document root directory as `hello.phn`. From inside this directory run the following command to compile it:

{% highlight bash %}
$ pharen hello.phn
{% endhighlight %}

If you start your server and go to [http://localhost/hello.php](http://localhost/hello.php)  you will see "Hello, world!" printed out. Very basic, but it gets the nitty-gritty details are done.

**In depth**: `pharen` invokes the Pharen compiler, which can then take in any number of files and compiles them in order. For now, the compiler expects Pharen files to either have the .phn extension or no extension at all. Output files use the original name but with the .php extension.

In the code itself, the entire expression is inside parentheses. Function calls and almost everything else in Pharen follow the pattern `(func arg1 arg2 argN)`. Read more about [expressions](/pharen/reference.html#expressions) in the reference.

### More complex expressions {#complex-expressions}
How about a little more practice? Go back to `hello.phn` and enter the following code:

{% highlight clojure %}
; hello.phn
(def id ($ get "id"))

(if (< (intval id) 0)
  (print "Invalid id, cannot be less than 0.")
  (print (. "Fetching page with id: " id)))
{% endhighlight %}

Recompile with:
{% highlight bash %}
$ pharen hello.phn
{% endhighlight %}

Now try loading [http://localhost/hello.php?id=8](http://localhost/hello.php?id=8). The page should respond with "Fetching page with id: 8".

**In depth**: The first line defines a variable using `def`, notice how even variable creation looks like a regular function call, with the first argument as the variable's name and the second as the value. `def` also puts the current variable in the lexical scope and can be accessed from functions and manually created nested scopes nested. [More on creating variables](/pharen/reference.html#defining-variables).

`$` is a special shortcut for accessing superglobals, the first parameter is which superglobal to use (GET in this case), while the second is the index. [More on superglobals](/pharen/reference.html#superglobals).

Pharen's `if` expressions work slightly differently from PHP's if statements (although that is what they are compiled into). They take one condition and two body expressions. If the condition is true, the first body expression is run, if the condition is false, the second body expression is run. [More on if expressions](/pharen/reference.html#if).

The last new facet introduced here is how comparison and string concatenation are done. Infix operators such as those for math (+, -, /, \*) and comparison (<, <=, ==, !=, etc...) and string concatenation are treated like function names in Pharen. This means that in the above code, `(intval id)` is checked to see if it's less than 0 while the second print expression concatenates a string with the variable `id`. [More on infix operators](/pharen/reference.html#infix-operators).

### Dynamic pages {#dynamic-pages}
Let's use what we know to write something that will fetch pastes for our pastebin. Create a directory inside your server's document directory called `pastebin`. All project files should from now on be placed inside here. Create a file called `paste.phn` and enter the following code:

{% highlight clojure %}
; pastebin/paste.phn
(require "sql.php")

(sql-connect "your-username" "your-password" "pastebindb")

(fn print-paste (paste)
  (print (. "<h2>" (:paste "title") "</h2>"
     "<p>" (:paste "contents") "</p>")))

(if (isset ($ get "id"))
  (print-paste (sql-fetch-by-id "pastes" ($ get "id")))
  (print "No paste id provided."))
{% endhighlight %}

For now, the MySQL database is assumed. Make sure you replace your-username and your-password. You can either create a pastebindb database or use an existing one. Of course, we can't test this since there's nothing in the database. Let's fix that.

**In depth:** Pharen comes with a couple of tiny, built-in libraries. These files are automatically placed in the include path through `lang.phn`, which is why just typing `sql.php` works.

A function is created using the `fn` form. Notice how there is no explicit return statement. The last evaluated expression, in this case the `print` expression, will be automatically returned. In the big picture, the `print-paste` function packages the display code, so that the main logic doesn't have to worry about dealing with the HTML, just fetching and handing over the data. [More on function definitions](/pharen/reference.html#functions).

The functions `sql-connect` and `sql-fetch-by-id` are from the `sql.php` library. For now, all those functions have the `sql-` prefix, which  later on may be turned into a more full-fledged namespace system. `sql-connect` connects to the MySQL server and selects a database, while `sql-fetch-by-id` sanitizes input for you, which is why we can give it the raw $_GET data.

The `if` expression is fairly straightforward, it fetches the paste if an id is provided and gives an error message otherwise.

### Form handling {#form-handling}
Create a MySQL database called `pastebindb`. Then run the following SQL inside that database (through whatever db front-end you use, maybe PHPMyAdmin).

{% highlight sql %}
CREATE TABLE pastes (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255),
  contents TEXT
);
{% endhighlight %}

Start by creating a new file called `new.phn`. Then use the following code:

{% highlight clojure %}
; pastebin/new.phn
(require "sql.php")
(require "html.php")

(fn print-paste-link (id)
  (print (html-link (. "/pastebin/paste?id=" id) "New paste")))

(fn print-paste-form ()
  (print (html-form "post" ($ server "PHP_SELF")
                    (html-textbox "title")
                    (html-textarea "contents")
                    (html-submit "submit"))))

(sql-connect "your-username" "your-password" "pastebindb")

(if (isset ($ post "submit"))
  (print-paste-link (sql-insert "pastes" 
                                {"title" ($ post "title"),
                                 "contents" ($ post "contents")}))
  (print-paste-form))
{% endhighlight %}

Compile it from inside the `pastebin` directory by running: 
{% highlight bash %}
pharen new.phn
{% endhighlight %}

Now go to [http://localhost/pastebin/new.php](http://localhost/pastebin/new.php). You will see a form. Enter anything you want (don't worry, the values will be automatically sanitized) and submit it. If everything works, you'll get a link. For now, that link won't work but keep this page open anyway while we fix this.

Compile this file. Go back to the open web page where you submitted the form and click the link. It should take you to the page generated by the above code.

**In depth:** As before, we wrapped all the code dealing with HTML into separate functions, one to print a link to a specific paste and one to print the code for a form. We also used the `html.php` library.

Most of the html-* functions behave as you would expect. `html-link` takes a URL and the text to display. `html-textbox`, `html-textarea`, and `html-submit` each just take one argument, the name+id of the element. `html-form` is interesting because it can take any number of elements. The first two arguments provide the request type and URL to submit to. After that, `html-form` can take any number of arguments, whose values become the form's body. This is done by way of [splats](/pharen/reference.html#splats).

After that's all done, we check to see if the current request is a form submission. If it is, we call the `sql-insert` function and it pass it a table name along with a dictionary containing values for the 'title' and 'contents' fields. The values are retrieved from `$_POST` data using the `$` form. Dictionaries are key-value pairs, similar to associative arrays in PHP where you explicitly set the keys. [More on dictionaries](/pharen/reference.html#dictionaries).

### What's next {#whats-next}
That's it for this tutorial. By now you should have a feel programming in Pharen. Some things you can do from here:
* Read the in-depth sections to better understand the code.
* Add more features to the pastebin, maybe editing pastes.
* Learn about cooler features, like [macros](/pharen/reference.html#macros), or [tail recursion elimination](/pharen/reference.html#tre).
* [Contribute](/pharen/contribute.html) to Pharen.
