# unifi-dream-machine-bind-export


Bulit on top of Art-of-WiFi UniFi API client. (https://github.com/Art-of-WiFi/UniFi-API-client)

In the checked out directory:
- Update config.php.template with the information for your UDM.
- Install and run composer: composer require art-of-wifi/unifi-api-client
- Install and configure bind9 on your system.
- Schedule this to be run regularly using crontab


# Installing Dependencies
```sudo apt install composer php-cli php-curl
sudo apt install bind9 bind9utils bind9-doc
```

## Set up Bind (this turtorial is useful: https://www.linuxtechi.com/install-configure-bind-9-dns-server-ubuntu-debian/)

### /etc/bind/named.conf.options
edit the iprange below to match your internal network
```
options {
        directory "/var/cache/bind";
        auth-nxdomain no;    # conform to RFC1035
        // listen-on-v6 { any; };
        listen-on port 53 { localhost; 192.168.0.0/24; };
        allow-query { localhost; 192.168.1.0/24; };
        forwarders { 1.1.1.2; 1.0.0.2; };
        recursion yes;
        dnssec-validation auto;
};
```


### named.conf.local
```
//
// Do any local configuration here
//

// Consider adding the 1918 zones here, if they are not used in your
// organization
//include "/etc/bind/zones.rfc1918";

zone    "mylocaldomainname.local"   {
        type master;
        file    "/etc/bind/mylocaldomainname.local";
 };

zone   "1.168.192.in-addr.arpa"        {
       type master;
       file    "/etc/bind/reverse.mylocaldomainname.local";
 };
```

## Configure the php scripts

Add in your controller user, controller password, controller url, nameserver name, domain name and Name Server ip
```
<?php
$controlleruser     = 'changeme'; // the user name for access to the UniFi Controller
$controllerpassword = 'changeme'; // the password for access to the UniFi Controller
$controllerurl      = 'changeme'; // full url to the UniFi Controller, eg. 'https://22.22.11.11:8443', for UniFi OS-based
                          // controllers a port suffix isn't required, no trailing slashes should be added

$bindloc            = '/etc/bind';
$nsname             = 'changemetodnservername';
$domain             = 'changemetomylocaldomain';
$nsip               = 'changemetomynsip';

$ttl                = 600;
$refresh            = 10800;
$retry              = 3600;
$expire             = 432000;
$negative           = 600;
```

run the script

sudo update.sh

Which if everything is configured collectly, will create the zone files in /etc/bind for you.

Restart and enable bind:

sudo systemctl restart bind9
sudo systemctl enable bind9

I set it up to run every minute to update from cron:

add the following line using sudo crontab -e:
'''
* * * * * /path/to/update.sh
'''
