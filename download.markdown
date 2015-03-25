---
layout: page
title: Download and Install
name: download
---

## Downloading and Usage ##

### Getting Pharen ### {#getting-pharen}
You can download the [ZIP file](http://github.com/Scriptor/pharen/archives/master.zip)
from Github.
If you have Git installed, you can also run:

{% highlight python %}
$ git clone git://github.com/Scriptor/pharen.git
{% endhighlight %}

### Installing Pharen ### {#installing-pharen}
After downloading and if needed, extracting, Pharen, `cd` into its directory and run:

<b>On Unix systems:</b>
{% highlight ruby %}
$ sudo ./install.sh
{% endhighlight %}

<b>On Windows:</b>
{% highlight ruby %}
install.bat
{% endhighlight %}

### Using Pharen ### {#using-pharen}
To use the compiler:

{% highlight ruby %}
$ pharen your_file.phn
{% endhighlight %}

This will compile the contents of your_file.phn to your_file.php. Note that `php` should point to
wherever the php executable is located if it's not in your environment path. Also, .phn is the convention
for a Pharen file extension, although you can use your own.

Adding more file names causes each file to be compiled in order:

{% highlight ruby %}
$ pharen your_file.phn file2.phn file3.phn fileN.phn
{% endhighlight %}

Each file is then compiled to the equivalent PHP file, so your\_file.php, file2.php, etc.
The order in which you specify the files is only important when using [partials](/pharen/reference.html#partials)
and [macros](/pharen/reference.html#macros) since the compiler stores information related to those in memory.
If you're not doing either, then you don't have to worry about the order.
