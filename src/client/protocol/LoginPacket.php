<?php

namespace client\protocol;

class LoginPacket extends \pocketmine\network\mcpe\protocol\LoginPacket{

	public $protocol;
	public $chainData = [];
	public $clientData = [];

	public function encode(){
		parent::encode();
		$this->putInt($this->protocol);
		//TODO
	}
	
	public function encodeJWT(array $data){
		$payload = base64_encode(json_encode($data));
		$fake = base64_encode("fakedata");
		return $fake . "." . $payload . "." . $fake;
	}
}