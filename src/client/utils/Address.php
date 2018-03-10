<?php

namespace client\utils;

class Address {

	protected $ip;
	protected $port;

	public function __construct(string $ip, int $port){
		$this->ip = $ip;
		$this->port = $port;
	}

	public function setIp(string $ip){
		$this->ip = $ip;
	}

	public function getIp(){
		return $this->ip;
	}

	public function setPort(int $port){
		$this->port = $port;
	}

	public function getPort(){
		return $this->port;
	}
}