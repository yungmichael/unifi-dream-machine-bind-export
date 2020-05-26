<?php

require_once('vendor/autoload.php');
require_once('config.php');

function dnsHeader($fp,$ttl,$nsname,$domain,$refresh,$retry,$expire,$negative,$nsip) {
	fprintf($fp,"%-30s %d\n","\$TTL",$ttl);

	fprintf($fp,"%-30s IN	SOA	%s.%s. root.%s.%s. (\n","@",$nsname,$domain,$nsname,$domain);
	fprintf($fp,"%30s	; Serial\n",time());
	fprintf($fp,"%30s	; Refresh\n",$refresh);
	fprintf($fp,"%30s	; Retry\n",$retry);
	fprintf($fp,"%30s	; Expire\n",$expire);
	fprintf($fp,"%30s)	; Negative Cache TTL\n",$negative);

	fprintf($fp,"\n\n;Name Server Information\n%-30s	IN	NS	%s.%s.\n","@",$nsname,$domain);
        fprintf($fp,"\n\n; IP Address of your domain name server (DNS)\n%-30s IN	A	%s\n\n",$nsname,$nsip);
}

$unifi_connection = new UniFi_API\Client($controlleruser, $controllerpassword, $controllerurl);
$set_debug_mode   = $unifi_connection->set_debug($debug);
$loginresults     = $unifi_connection->login();
$clients_array    = $unifi_connection->list_clients();

if (!($fp = fopen("$bindloc/forward.$domain", 'w'))) {
    return;
}

dnsHeader($fp,$ttl,$nsname,$domain,$refresh,$retry,$expire,$negative,$nsip);

foreach ($clients_array as $client) {
  if (property_exists($client,"hostname")) {
     fprintf($fp,"%-30s IN	A	%s\n",strtolower($client->hostname),$client->ip);
  }
}

fclose($fp);

if (!($fp = fopen("$bindloc/reverse.$domain", 'w'))) {
    return;
}
dnsHeader($fp,$ttl,$nsname,$domain,$refresh,$retry,$expire,$negative,$nsip);
list($ip1,$ip2,$ip3,$ip4) = explode(".",$nsip);

fprintf($fp,";Reverse Lookup for Your DNS Server;\n%-30s      IN      PTR     %s.%s.\n\n\n",$ip4,$nsname,$domain);

foreach ($clients_array as $client) {
  if (property_exists($client,"hostname")) {
     list($ip1,$ip2,$ip3,$ip4) = explode(".",$client->ip);
     fprintf($fp,"%-30s IN	PTR	%s.%s.\n",$ip4,strtolower($client->hostname),$domain);
  }
}
