#!/bin/sh

# LANG=ja_JP
# PO=../$LANG/LC_MESSAGES
# xgettext -k_ -o $PO/pukiwiki.po -j ../../lib/*.php

xgettext -k_ -o pukiwiki.pot ../../lib/*.php
