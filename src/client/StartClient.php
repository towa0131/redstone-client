<?php

namespace client {
	
	require_once "vendor/autoload.php";

	echo "Name : ";
	$name = trim(fgets(STDIN));
	
	echo "ServerIP : ";
	$ip = trim(fgets(STDIN));
	
	echo "Server Port : ";
	$port = trim(fgets(STDIN));
	
	$client = new MCPEClient($name);
	$client->addConnection($ip, (int)$port);
	
	while(true){
	    $client->tick();
	    usleep(1000);
	}
}