#!/bin/sh

LOCALE=../locale
LANG=ja

msgfmt -o $LOCALE/$LANG/LC_MESSAGES/pukiwiki.mo pukiwiki.po
msgfmt -o $LOCALE/$LANG/LC_MESSAGES/add.mo add.ja.po
msgfmt -o $LOCALE/$LANG/LC_MESSAGES/article.mo article.po
msgfmt -o $LOCALE/$LANG/LC_MESSAGES/back.mo back.ja.po
msgfmt -o $LOCALE/$LANG/LC_MESSAGES/comment.mo comment.po
msgfmt -o $LOCALE/$LANG/LC_MESSAGES/deleted.mo deleted.po
msgfmt -o $LOCALE/$LANG/LC_MESSAGES/freeze.mo freeze.po
msgfmt -o $LOCALE/$LANG/LC_MESSAGES/interwiki.mo interwiki.po
msgfmt -o $LOCALE/$LANG/LC_MESSAGES/ls2.mo ls2.po
msgfmt -o $LOCALE/$LANG/LC_MESSAGES/navi.mo navi.po
msgfmt -o $LOCALE/$LANG/LC_MESSAGES/newpage.mo newpage.po
msgfmt -o $LOCALE/$LANG/LC_MESSAGES/memo.mo memo.po
msgfmt -o $LOCALE/$LANG/LC_MESSAGES/search.mo search.po
msgfmt -o $LOCALE/$LANG/LC_MESSAGES/source.mo source.po
msgfmt -o $LOCALE/$LANG/LC_MESSAGES/tracker.mo tracker.po
msgfmt -o $LOCALE/$LANG/LC_MESSAGES/unfreeze.mo unfreeze.po
msgfmt -o $LOCALE/$LANG/LC_MESSAGES/vote.mo vote.po
msgfmt -o $LOCALE/$LANG/LC_MESSAGES/versionlist.mo versionlist.po
msgfmt -o $LOCALE/$LANG/LC_MESSAGES/yetlist.mo yetlist.po
