#!/usr/local/bin/bash

BASEDIR=/home/smutt/www
WGET=/usr/local/bin/wget
OS=bsd #linux || bsd

echo "Retrieving RSSAC002 data"
cd $BASEDIR/RSSAC002-data
git pull

echo "Retrieving rss instance data"
cd $BASEDIR/instance-data
rm $BASEDIR/instance-data/archives/index.html
if [ "$(date +%m)" -eq "01" ] # Delete index.html from last 2 months
then
        find $BASEDIR/instance-data/archives/$(echo "$(date +%Y)-1" | bc) -name "index.html" -delete
        find $BASEDIR/instance-data/archives/$(date +%Y)/01 -name "index.html" -delete
        find $BASEDIR/instance-data/archives/$(echo "$(date +%Y)-1" | bc)/12 -name "index.html" -delete
else
        find $BASEDIR/instance-data/archives/$(date +%Y) -name "index.html" -delete
        find $BASEDIR/instance-data/archives/$(date +%Y)/$(date +%m) -name "index.html" -delete
        find $BASEDIR/instance-data/archives/$(date +%Y)/$(printf '%02d' $(echo "$(date +%m)-1" | bc)) -name "index.html" -delete
fi
$WGET --no-verbose --no-host-directories --max-redirect=0 --recursive --https-only --no-clobber --no-parent https://root-servers.org/archives/ 2>&1

echo "Serializing RSSAC002 and instance data"
cd $BASEDIR/rssac002-web-api
./prep_data.php

echo "Generating static HTML for charts"
cd $BASEDIR/rssac002-charts
./gen_html.php
