<?php

namespace client\protocol;

use pocketmine\network\mcpe\protocol\RequestChunkRadiusPacket as PMRequestChunkRadiusPacket;

class RequestChunkRadiusPacket extends PMRequestChunkRadiusPacket{

	public $radius;

	public function encode(){
		$this->putVarInt($this->radius);
	}

}