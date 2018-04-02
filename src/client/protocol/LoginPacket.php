<?php

namespace client\protocol;

use pocketmine\network\mcpe\protocol\LoginPacket as PMLoginPacket;

class LoginPacket extends PMLoginPacket{

	public $protocol;
	public $chainData;
	public $clientData;

	public function encode(){
		parent::encode();
		$this->putInt($this->protocol);
		$webToken = $this->encodeJWT(json_encode($this->chainData));
		$chainData = json_encode(["chain" => [$webToken, $webToken, $webToken]]);
		$this->putLInt(strlen($chainData));
		$this->put($chainData);
		$clientData = $this->encodeJWT(json_encode($this->clientData));
		$this->putLInt(strlen($clientData));
		$this->put($clientData);
	}

	private function encodeJWT(string $jwt){
		return "none." . base64_encode($jwt) . ".none";
	}
}