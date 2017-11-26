<?php

namespace client\protocol;

class RequestChunkRadiusPacket extends \pocketmine\network\mcpe\protocol\RequestChunkRadiusPacket{

	public $radius;

	public function encode(){
		$this->putVarInt($this->radius);
	}

}