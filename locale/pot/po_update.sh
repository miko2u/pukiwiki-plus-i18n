#!/bin/sh

LANG=ja_JP
PO=../../locale/$LANG/LC_MESSAGES

msgmerge $PO/$1.po $1.pot -o $PO/$1.po

