#!/bin/sh

# xgettext --from-code=EUC-JP -k_ -o $1.pot ../../plugin/$1.inc.php
xgettext -k_ -o $1.pot ../../plugin/$1.inc.php

