#!/bin/bash

# Variables
GITDIR="/tmp/git"
ENGINEAPIGIT="https://github.com/wvulibraries/engineAPI.git"
ENGINEBRANCH="master"
ENGINEAPIHOME="/home/engineAPI"

SERVERURL="/home/timeTracker"
DOCUMENTROOT="public_html"
SITEROOT="/home/timeTracker/public_html/src"


## SERVER CONFIGURATION
echo "install apache"
yum -y install httpd httpd-devel httpd-manual httpd-tools

echo "install mysql"
yum -y install mysql-connector-java mysql-connector-odbc mysql-devel mysql-lib mysql-server
yum -y install mod_auth_kerb mod_auth_mysql mod_authz_ldap mod_evasive mod_perl mod_security mod_ssl mod_wsgi

echo "install php"
yum -y install php php-bcmath php-cli php-common php-gd php-ldap php-mbstring php-mcrypt php-mysql php-odbc php-pdo php-pear php-pear-Benchmark

echo "link php ini that has some custom configuations set"
rm -f /etc/php.ini
ln -s /vagrant/serverConfiguration/php.ini /etc/php.ini

echo "install emacs git"
yum -y install emacs emacs-common emacs-nox
yum -y install git

echo "Modifying Apache Configuration files"
mv /etc/httpd/conf.d/mod_security.conf /etc/httpd/conf.d/mod_security.conf.bak
/etc/init.d/httpd start
chkconfig httpd on

echo "Moving HTTPD Conf Files"
rm -f /etc/httpd/conf/httpd.conf
ln -s /vagrant/serverConfiguration/httpd.conf /etc/httpd/conf/httpd.conf


## Framework Install
echo "Getting Engine API"
mkdir -p $GITDIR
cd $GITDIR
git clone -b $ENGINEBRANCH $ENGINEAPIGIT
git clone https://github.com/wvulibraries/engineAPI-Modules.git

echo "Setting up engine Modules and includes"
mkdir -p $SERVERURL/phpincludes/
ln -s /vagrant/template/* $GITDIR/engineAPI/engine/template/
ln -s $GITDIR/engineAPI-Modules/src/modules/* $GITDIR/engineAPI/engine/engineAPI/latest/modules/
ln -s $GITDIR/engineAPI/engine/ $SERVERURL/phpincludes/


echo "Makes the public folder and syncs it to the application source"
mkdir -p $SERVERURL/$DOCUMENTROOT
ln -s /vagrant/src $SITEROOT
ln -s $SERVERURL/phpincludes/engine/engineAPI/latest $SERVERURL/phpincludes/engine/engineAPI/4.0

echo "Server Configurations for Engine"
rm -f $GITDIR/engineAPI/engine/engineAPI/latest/config/defaultPrivate.php
ln -s /vagrant/serverConfiguration/defaultPrivate.php $GITDIR/engineAPI/engine/engineAPI/latest/config/defaultPrivate.php

mkdir -p $SERVERURL/phpincludes/databaseConnectors/
ln -s /vagrant/serverConfiguration/database.lib.wvu.edu.remote.php $SERVERURL/phpincludes/databaseConnectors/database.lib.wvu.edu.remote.php

echo "Linking to Engine in a place we can use them"
ln -s $SERVERURL $ENGINEAPIHOME
ln -s $GITDIR/engineAPI/public_html/engineIncludes/ $SERVERURL/$DOCUMENTROOT/engineIncludes

echo "Changing permissions on the logs folder and moves the error and access logs to our source code"
chmod a+rx /etc/httpd/logs -R
sudo ln -s /etc/httpd/logs/error_log /vagrant/serverConfiguration/serverlogs/error_log
sudo ln -s /etc/httpd/logs/access_log /vagrant/serverConfiguration/serverlogs/access_log

# MySQL Setup
echo "Settings up MySQL"
/etc/init.d/mysqld start
chkconfig mysqld on

## setup databases
mysql -u root < /tmp/git/engineAPI/sql/vagrantSetup.sql
mysql -u root EngineAPI < /tmp/git/engineAPI/sql/EngineAPI.sql

mysql -u root < /vagrant/SQLFiles/setup.sql
mysql -u root timeTracker < /vagrant/SQLFiles/timeTracker.sql

## restart apache
echo "Finishing by restarting apache"
/sbin/service httpd restart