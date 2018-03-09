<?php

namespace client\protocol;

use pocketmine\network\mcpe\protocol\LoginPacket as PMLoginPacket;

class LoginPacket extends PMLoginPacket{

	public $protocol;
	public $chainData;

	public function encode(){
		parent::encode();
		$this->putInt($this->protocol);
		$string = "";
		$this->putString($string);
	}
}