#!/bin/bash
pharen_dir=$(dirname $(readlink -f $0))
if [ ! -e "/usr/local/bin/pharen" ]
then
    ln -s $pharen_dir/bin/pharen /usr/local/bin/pharen
    echo "Pharen successfully installed."
else
    echo "There is already a file called 'pharen' in /usr/local/bin. Maybe it's already installed?"
fi
