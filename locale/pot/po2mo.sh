#!/bin/sh

LANG=ja_JP
PO=../../locale/$LANG/LC_MESSAGES
msgfmt -o $PO/$1.mo $PO/$1.po

