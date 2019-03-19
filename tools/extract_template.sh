#!/bin/bash
#!/bin/bash

# Create the pot file
xgettext *.php */*.php -o locales/monitoring.pot -L PHP --add-comments=TRANS --from-code=UTF-8 --force-po \
    --keyword=_n:1,2 --keyword=__s --keyword=__ --keyword=_e --keyword=_x:1c,2 --keyword=_ex:1c,2 --keyword=_sx:1c,2 --keyword=_nx:1c,2,3 --keyword=_sn:1,2

#Update main language
LANG=C msginit --no-translator -i locales/monitoring.pot -l en_GB -o locales/en_GB.po
# Uncomment this line to créate the po files
# msginit -i locales/monitoring.pot -l fr_FR -o locales/fr_FR.po

### for using tx :
##tx set --execute --auto-local -r GLPI.glpipot 'locales/<lang>.po' --source-lang en_GB --source-file locales/glpi.pot
## tx push -s
## tx pull -a


