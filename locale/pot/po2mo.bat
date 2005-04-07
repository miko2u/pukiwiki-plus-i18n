@echo off

set LANG=ja_JP
set PO=../../locale/%LANG%/LC_MESSAGES
msgfmt -o %PO%/%1.mo %PO%/%1.po

