#!/bin/bash
pharen_dir=$(dirname $(readlink -f $0))
if [ ! -e "/usr/bin/pharen" ]
then
    ln -s $pharen_dir/bin/pharen /usr/bin/pharen
    echo "Pharen successfully installed."
else
    echo "There is already a file called 'pharen' in /usr/bin. Maybe it's already installed?"
fi
