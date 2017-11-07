#!/bin/bash
#Clear Cache
#INSTALL_PATH=${1}
#if [ INSTALL_PATH =="" ]; then echo "No Shop Dir Provided to Clrcache"; exit; fi
rm -rf /var/www/vhosts/choicestationery.com/htdocs/magentonew/var/locks/*
php -f /var/www/vhosts/choicestationery.com/htdocs/magentonew/downloader/mage.php clear-cache
rm -rf /var/www/vhosts/choicestationery.com/htdocs/magentonew/downloader/pearlib/cache/*
rm -rf /var/www/vhosts/choicestationery.com/htdocs/magentonew/downloader/pearlib/download/*
rm -rf /var/www/vhosts/choicestationery.com/htdocs/magentonew/var/cache/*
rm -rf /var/www/vhosts/choicestationery.com/htdocs/magentonew/var/session/*
rm -rf /var/www/vhosts/choicestationery.com/htdocs/magentonew/var/report/*
rm -rf /var/www/vhosts/choicestationery.com/htdocs/magentonew/var/tmp/*
#STATUS="Failed"
#if [ $?==0 ]; then STATUS="Suceeded"
#echo "Clear Cache Task ${STATUS}"
#exit ${?}
