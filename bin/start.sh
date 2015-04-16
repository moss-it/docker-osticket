#!/bin/bash
# (C) Campbell Software Solutions 2015
set -e

# Automate installation if using linked MySQL container
if [ ! -z "$MYSQL_PORT" ]; then
  echo Using linked MySQL connection - $MYSQL_PORT
  php /data/bin/install.php
  echo Applying configuration file security
  chmod 644 /data/upload/include/ost-config.php
else
  echo No MySQL linked container detected, manual configuration \& installation will be required.
fi

#Launch supervisor to manage processes
exec /usr/bin/supervisord -c /data/supervisord.conf
