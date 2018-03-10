<?php

namespace client {

	use client\utils\Address;
	require_once "vendor/autoload.php";

	echo "Name : ";
	$name = trim(fgets(STDIN));

	echo "Server IP : ";
	$ip = trim(fgets(STDIN));

	echo "Server Port : ";
	$port = (int)trim(fgets(STDIN));

	$client = new MCPEClient($name);
	$client->addConnection(new Address($ip, $port));

	while(true){
		$client->tick();
		usleep(1000);
	}
}