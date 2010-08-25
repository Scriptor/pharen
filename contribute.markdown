---
layout: default
title: Contributing to Pharen
---

## Contributing to Pharen ##
There are a bunch of ways to help out for people of all experience levels. Also, since the the project is still very young, it will be easy to have a major influence.

You can always drop by in IRC at #pharen on irc.freenode.net if have an idea you want to discuss quickly.

### Using Pharen ### {#using-pharen}
The best way to help out is to simply create a Pharen project or two of your own, and tell others about the experience. Stretch the language to its limits to see what bugs show up. Create some run-of-the-mill applications and find out the best practices for Pharen development. Write a library for something that doesn't exist, or for something that *does* exist but has an API geared to imperative programming in PHP.

Whatever you do, please share your experiences and if possible, the code, so that others can learn and give feedback in return.

### Bugs and Feature Requests ### {#issues}
If you find any bugs or have ideas for new features or better ways to implement something, please post about it in the [issues tracker](http://github.com/scriptor/pharen/issues). Any discussion for those requests can then be centered there.

### Submitting Code ### {#submitting-code}
The Pharen project uses Git to manage all source code and GitHub to host it publicly. It is pretty straightforward to submit your own code. You can go about it in two ways:

#### With Github #### {#with-github}
Although this seems more complicated, it gives you the advantage of having your own copy of Pharen that you can freely experiment with. Additionally, other people can easily see the changes you made before they are integrated into the main repository.

1. [Create a Github account](https://github.com/signup/free) if you don't have one.
2. From the [main project page](http://github.com/scriptor/pharen) click the `Fork` button. This will place a fork of the code in your account. This fork is entirely yours'.
3. Go to your fork's Github page and copy the cloning URL (it should be `http://github.com/<your username>/pharen.git`).
4. Now, from the command-line, type `git clone`, paste in the cloning URL, and hit enter, this will create a local copy of your fork.
5. Make some changes to the code.
6. From inside the fork's directory and for each specific functional change, do the following:
    - `git add` the files that were changed.
    - `git commit -m "Enter a good commit message describing what was changed and why."`
7. Run `git push origin master`. This will take the changes you made to your local copy and update your fork on Github.
8. Go back to the [main project page](http://github.com/scriptor/pharen) and click `Pull Request`. Summarize the changes you made to the code, submit, and I will take care of the rest.

#### Without Github #### {#without-github}
The simpler alternative, although a big issue is that others will not be able to see what you did until after I accept your patch.

This method is best explained by the [Git website](http://git-scm.com). Look under "Cloning and Creating a Patch". Once the patch is created, you can email it to me at tamreenkhan+pharen@gmail.com.

### Writing Tests ### {#writing-tests}
Right now, the Pharen test suite only covers the absolute basics. A very useful way to help is to add new tests that cover more edge cases and any new features that come about:

1. Understand how to [submit code](#submit-code).
2. Tests are located in `examples/test/tests`. You can either edit an existing test file or create a new one.
3. Use the `check` function to test whatever value you got against the value you expected. `(check (. "foo" "bar") "foobar")` will work, while `(check (. "foo "bar") "stuff")` will cause the test to fail.
4. If you created a new file, add it to the list called `tests` in `examples/test/tests/pharen_tests.phn`.

That's it! Running `phake test` from the project's root directory will now also run the newly added test code.