<?php
namespace client\protocol;

class LoginStatusPacket extends \pocketmine\network\mcpe\protocol\LoginStatusPacket{
    public function decode(){
        parent::decode();
        $this->status = $this->getInt();
    }

}