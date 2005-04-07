@echo off

set LANG=ja_JP
set PO=../%LANG%/LC_MESSAGES

msgmerge %PO%/%1.po %1.pot -o %PO%/%1.po
