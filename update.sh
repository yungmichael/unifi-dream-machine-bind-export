#/bin/bash


cd /home/gabe/unifi/dnsmanager

/usr/bin/php create_bind.php > /dev/null 2>&1

/bin/systemctl restart bind9


