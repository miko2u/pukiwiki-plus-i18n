@echo off

@rem xgettext --from-code=UTF-8 -k_ -o %1.pot ../../plugin/%1.inc.php
xgettext -k_ -o %1.pot ../../plugin/%1.inc.php
