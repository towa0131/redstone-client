<?php

namespace client\protocol;

class LoginPacket extends \pocketmine\network\mcpe\protocol\LoginPacket{

	public $protocol;
	public $string;

	public function encode(){
		parent::encode();
		$this->putInt($this->protocol);
		$this->putString($this->string);
	}
}