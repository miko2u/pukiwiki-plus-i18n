#!/bin/sh
msgfmt -o pukiwiki.mo pukiwiki.po add.po article.po back.po memo.po source.po vote.po versionlist.po yetlist.po
cp pukiwiki.mo ../locale/ja/LC_MESSAGES
