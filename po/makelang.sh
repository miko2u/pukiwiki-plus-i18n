#!/bin/sh
msgfmt -o pukiwiki.mo pukiwiki.ja.po add.ja.po article.ja.po back.ja.po memo.ja.po source.ja.po vote.ja.po versionlist.ja.po yetlist.ja.po
cp pukiwiki.mo ../locale/ja/LC_MESSAGES
