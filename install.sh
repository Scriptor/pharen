#!/bin/bash
pushd `dirname $0` > /dev/null
pharen_dir=`pwd`
popd > /dev/null
echo $pharen_dir
if [ ! -e "/usr/local/bin/pharen" ]
then
    ln -s $pharen_dir/bin/pharen /usr/local/bin/pharen
    echo "Pharen successfully installed."
else
    echo "There is already a file called 'pharen' in /usr/local/bin. Maybe it's already installed?"
fi
