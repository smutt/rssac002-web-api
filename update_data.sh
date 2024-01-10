#!/usr/local/bin/bash

BASEDIR=/var/www/htdocs/rssac002.depht.com

echo "Retrieving RSSAC002 data"
cd $BASEDIR/RSSAC002-data
git pull

echo "Retrieving rss instance data"
cd $BASEDIR/instance-data
rm $BASEDIR/instance-data/archives/index.html
/usr/local/bin/wget --no-verbose --no-host-directories --max-redirect=0 --retry-on-host-error --recursive --https-only --no-clobber --no-parent https://root-servers.org/archives/ 2>&1

echo "Serializing RSSAC002 and instance data"
cd $BASEDIR/rssac002-web-api
./prep_data.php

echo "Generating static HTML for charts"
cd $BASEDIR/rssac002-charts
./gen_html.php
