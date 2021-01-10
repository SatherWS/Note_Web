#!/bin/sh

# This script starts apache2 service, creates mysqld directories if empty.
# Usually the commands need to be executed manually in WSL for some reason.

sudo service apache2 start
sudo mkdir -p /var/run/mysqld
sudo chown mysql:mysql /var/run/mysqld
cd /etc/init.d/
sudo ./mysql start

# Test if services are running
sudo service apache2 status
sudo service mysql status
cd ~

# Add the -b option to backup the local mysql database.
#while [ "$1" = "-b" ]; do
#	echo "backing up mysql database..."
#	mysqldump --add-drop-table -uroot -ptoor swoop > swoop_localdata.sql
#	sudo mv swoop_localdata.sql /var/www/html/config
#	echo "back up complete."
#	shift
#done
