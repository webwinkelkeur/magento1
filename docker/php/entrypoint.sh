#!/bin/bash

set -euo pipefail

ln -sfn /proc/self/fd/2 /var/log/apache2/error.log

mkdir -p /run/apache2
exec httpd -D FOREGROUND -f /etc/apache2/httpd.conf
