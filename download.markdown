---
layout: default
title: Downloading and Usage
---

## Downloading and Using Pharen

You can get Pharen by downloading it [from Github](http://github.com/Scriptor/pharen/archives/master)
and unpacking the tar or zip file to where you want it.

To use the compiler, go to the directory containing the Pharen files and type the following in a command-line:

{% highlight bash %}
php pharen.php your_file.phn
{% endhighlight %}

This will compile the contents of your_file.phn to your_file.php. Note that `php` should point to
wherever the php executable is located if it's not in your environment path. Also, .phn is the convention
for a Pharen file extension, though you can use your own.

Adding more file names causes each file to be compiled in order:

{% highlight bash %}
php pharen.php your_file.phn file2.phn file3.phn fileN.phn
{% endhighlight %}

Each file is then compiled to the equivalent PHP file, so you_file.php, file2.php, etc.
The order in which you specify the files is only important when making [partials](partials.html)
and using [macros](macros.html) since the compiler stores information related to those in memory.
If you're not doing either, then any order is fine.