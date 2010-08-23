---
layout: default
title: Bundled Code
---

This page documents some of the libraries that come bundled with a Pharen install.

Function signatures use the following format:

(function-name *type1* arg1 *type2* arg2 ...) 

### lang.phn ### {#lang.phn}
`lang.phn` is automatically included in every Pharen file unless it is compiled with the --no-import-lang option. In the future, this may be dropped, but for now it can be handy. It provides some functions designed to make functional programming easier:

#### first #### {#first}
(first *list* xs)

Returns the first element in a list

#### first-pair #### {#first-pair}
(first *dictionary* xs)

Returns the first element in a dictionary.

#### rest #### {#rest}
(rest *list|dictionary* xs)

Returns everything except the first element in `xs`.

#### early #### {#early}
(early *list|dictionary* xs)

Returns everything except the last element in `xs`.

#### take #### {#take}
(take *int* x *list|dictionary* xs)

Returns the first `x` elements in `xs`.

#### drop #### {#drop}
(drop *int* x *list|dictionary* xs)

Returns everything except the first `x` elements in `xs`.

#### cons #### {#cons}
(cons *any* x *list|dictionary* xs)

Return a new list or dictionary combining `x` and `xs`, with `x` being the first element.

#### append #### {#append}
(append *any* x *list|dictionary* xs)

Return a new list or dictionary combining `xs` and `x`, with `x` being the last element.

#### reduce #### {#reduce}
(reduce *string* f *any* acc *list* xs)

Call `f` while passing it with each element of `xs` and the current value for `acc`. `f` should then returns a new value for `acc`.

#### reduce-pairs #### {#reduce-pairs}
(reduce-pairs *string* f *any* acc *dictionary* xs)

Similar to `reduce`, but works on dictionaries instead.

#### map #### {#map}
(map *string* f *list* xs)

Calls `f` on each element of `xs` and uses the return values to create a new list.

#### map-pairs #### {#map-pairs}
(map-pairs *string* f *dictionary* xs)

Similar to `map`, but takes in and returns a dictionary instead.

#### filter #### {#filter}
(filter *string* f *list* xs)

Takes a list and calls a function on each element. If the function returns true, the element is added to the returned list.

### phake ### {#phake}
Phake is Pharen's ad-hoc build system. It is not very featureful, but has enough to automate some basic tasks, all using Pharen as the scripting language. Create a file called `phakefile` in the project directory and create some tasks using the `task` macro. Then, from inside the project directory, run `phake your-task`, replacing `your-task` with whichever one you want to run. Here is a simple example:

{% highlight clojure %}
(task "example" "An example task."
      (print "This is part of the task's body. Everything in the body is run when the task is run."))

(task "compile" "Compile a file."
      (compile-file (project-path "/myfile.phn")))
{% endhighlight %}

Save this file as `phakefile` in any directory. Then, from inside that directory on the command line run `phake example`. It will first print out `Running example: An example task`. This initialization message comes from the first and second arguments to `task`. Any arguments after that are part of the body, which is run after the initialization.

The second task in the code gives a taste of what Phake would actually be used for. When this task is run, it will look for a file called `myfile.phn` in the project directory and compile it (using the Pharen compiler). The `compile-file` function simply takes a file path and compiles it. It is actually used by the main compiler itself. `project-path` takes a path and prepends the path to the current project's directory to it. It then returns an absolute path to the file.

For a somewhat more realistic example of a phakefile, check out [the one Pharen uses](http://github.com/Scriptor/pharen/blob/master/phakefile).

Here is a list of functions to use with Phake:

#### task #### {#task}
(task *string* name *string* desc *any* &body)

Creates a task that can be run from the command-line using the `phake` tool. By convention, `name` should be one word while `desc` should briefly describe what the task does. After that, any number of expressions can follow which will be run if the task is called.

#### project-path #### {#project-path}
(project-path *string* f)

Builds an absolute path to a resource inside the project directory by prepending the project's path to the resource's path. For example, if your project was located in `/projects/example', calling `(project-path "/file")` would return `/projects/example/file`.

#### compile-dir #### {#compile-dir}
(compile-dir *string* dir *string* \[compile-func #compile-file\])

Recursively compiles all files with the `.phn` extension inside the `dir` directory. An optional compile function can also be passed, by default it uses `compile-file`.

#### compile-file #### {#compile-file}
(compile-file *string* fname *string* \[output-dir NULL\])

Compiles a Pharen file. If an `output-dir` is provided, the generated PHP code will be placed in that directory. Technically, this function is part of the Pharen compiler itself and not Phake.

#### compile-except #### {#compile-except}
(compile-except *string* except *string* file *string* \[output-dir NULL\])

Compiles the Pharen script at path `file` unless it matches the path `except`. A handy use for this is first creating a partial by only supplying the `except` path. Then, this partial can be used by `compile-dir` to compile all files except a certain one. `compile-except` also takes an optional argument specifying an output directory.