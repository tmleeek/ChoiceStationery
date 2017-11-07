#!/bin/bash
#Weekly cleanup
#source /bin/sinch/lib.sh
INSTALL_PATH="/var/www/vhosts/choicestationery.com/htdocs/magentonew"
HOST="10.0.0.46"
DBNAME="choicestationerycom"
DBUSER="choiceQIdX"
DBPASS="jaequiex"
php -f $INSTALL_PATH/shell/log.php -- clean --days 15
/var/www/vhosts/choicestationery.com/htdocs/magentonew/shell/clear_cache.sh
#MYSQLCHECK="$(mysqlcheck  -h $HOST -u $DBUSER -p$DBPASS --silent --auto-repair $DBNAME)"
mysqlcheck  -h $HOST -u $DBUSER -p$DBPASS --silent --auto-repair $DBNAME
if [ "$(echo ${MYSQLCHECK} | grep --ignore-case -E "fail|error|invalid|denied|insufficient|refused|cannot")" != "" ]; then
        #MYSQLOPTIMIZE=$(mysqlcheck -u ${DBUSER} -p${DBPASS} --silent --optimize $DBNAME)
	mysqlcheck -u ${DBUSER} -p${DBPASS} --silent --optimize $DBNAME
fi
