<?php

namespace client\protocol;

class ResourcePackClientResponsePacket extends \pocketmine\network\mcpe\protocol\ResourcePackClientResponsePacket{

	public $status = 4;
	public $packIds = [];

	public function encode(){
		$this->putByte($this->status);
		$this->putLShort(count($this->packIds));
		foreach($this->packIds as $id){
			$this->putString($id);
		}
	}
	
}