<?php

namespace client\protocol;

use pocketmine\network\mcpe\protocol\LoginPacket as PMLoginPacket;

use pocketmine\utils\BinaryStream;

class LoginPacket extends PMLoginPacket{

	public $protocol;
	public $chainData;
	public $clientData;

	public $header;

	public function encode(){
		parent::encode();
		$this->putInt($this->protocol);
		$bin = new BinaryStream();
		$webToken = $this->encodeJWT(json_encode($this->chainData));
		$chainData = json_encode(["chain" => [$webToken, $webToken, $webToken]]);
		$bin->putLInt(strlen($chainData));
		$bin->put($chainData);
		$clientData = $this->encodeJWT(json_encode($this->clientData));
		$bin->putLInt(strlen($clientData));
		$bin->put($clientData);
		$this->putString($bin->getBuffer());
	}

	private function encodeJWT(string $jwt){
		//TODO
		return base64_encode(json_encode($this->header)) . "." . base64_encode($jwt) . ".none";
	}
}