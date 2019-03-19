#!/bin/bash

# Create the pot file
xgettext *.php */*.php -o locales/monitoring.pot -L PHP --add-comments=TRANS --from-code=UTF-8 --force-po \
    --keyword=_n:1,2 --keyword=__s --keyword=__ --keyword=_e --keyword=_x:1c,2 --keyword=_ex:1c,2 --keyword=_sx:1c,2 --keyword=_nx:1c,2,3 --keyword=_sn:1,2

# Update French language
msgmerge --update locales/fr_FR.po locales/monitoring.pot
