<?php

namespace client;

//use client\protocol\CLIENT_HANDSHAKE_DataPacket;
use client\protocol\FullChunkDataPacket;
use client\protocol\LoginPacket;
use client\protocol\ResourcePackClientResponsePacket;
use client\protocol\RequestChunkRadiusPacket;
//use client\protocol\OPEN_CONNECTION_REQUEST_2;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\network\mcpe\protocol\LoginStatusPacket;
use pocketmine\network\mcpe\protocol\MessagePacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use raklib\protocol\CLIENT_HANDSHAKE_DataPacket;
use raklib\protocol\CLIENT_CONNECT_DataPacket;
use raklib\protocol\OPEN_CONNECTION_REPLY_1;
use raklib\protocol\OPEN_CONNECTION_REPLY_2;
use raklib\protocol\OPEN_CONNECTION_REQUEST_1;
use raklib\protocol\OPEN_CONNECTION_REQUEST_2;
use raklib\protocol\Packet;
use raklib\protocol\PING_DataPacket;
use raklib\protocol\SERVER_HANDSHAKE_DataPacket;
use raklib\protocol\UNCONNECTED_PONG;

class MCPEClient{
//class MCPEClient implements Tickable{

	private $name;

	/** @var  ClientConnection[] */
	private $connections;

	public function __construct($name = "Steve"){
		$this->name = $name;
		$this->connections = [];
	}

	public function addConnection($ip, $port){
		$this->connections[] = new ClientConnection($this, $ip, $port);
	}

	public function handlePacket(ClientConnection $connection, Packet $packet){
		echo "[Receive] " . get_class($packet) . "\n";
		switch(get_class($packet)){
			case UNCONNECTED_PONG::class:
				$connection->setName($packet->serverName);
				$connection->setIsConnected(true);
				$pk = new OPEN_CONNECTION_REQUEST_1();
				$pk->mtuSize = 1447;
				$connection->sendPacket($pk);
				break;
			case OPEN_CONNECTION_REPLY_1::class:
				$pk = new OPEN_CONNECTION_REQUEST_2();
				$pk->serverAddress = $connection->getIp();
				$pk->serverPort = $connection->getPort();
				$pk->mtuSize = 1447;
				$pk->clientID = 1;
				$connection->sendPacket($pk);
				break;
			case OPEN_CONNECTION_REPLY_2::class:
				$pk = new CLIENT_CONNECT_DataPacket();
				$pk->clientID = 1;
				$pk->sendPing = mt_rand(1,100);
				$connection->sendEncapsulatedPacket($pk);
				break;
			case SERVER_HANDSHAKE_DataPacket::class:
				$addresses = [];
				$addresses[0] = ["127.0.0.1", 0, 4];
				for($i = 1;$i<10;$i++){
					$addresses[$i] = ["0.0.0.0", 0, 4];
				}
				$pk = new CLIENT_HANDSHAKE_DataPacket();
				$pk->address = $connection->getIp();
				$pk->port = $connection->getPort();
				$pk->systemAddresses = $addresses;
				$pk->sendPing = mt_rand(1,100);
				$pk->sendPong = mt_rand(1,100);
				
				$connection->sendEncapsulatedPacket($pk);

				$uuid = "55cec0c2-c024-489f-8405-684e3e355aaf";
				$pk = new LoginPacket();
				$pk->protocol = ProtocolInfo::CURRENT_PROTOCOL;
				$pk->string = "eyJ4NXUiOiJNSFl3RUFZSEtvWkl6ajBDQVFZRks0RUVBQ0lEWWdBRThFTGtpeHlMY3dsWnJ5VVFjdTFUdlBPbUkyQjd2WDgzbmRuV1JVYVhtNzR3RmZhNWZcL2x3UU5UZnJMVkhhMlBtZW5wR0k2SmhJTVVKYVdacmptTWo5ME5vS05GU05CdUtkbThyWWlYc2ZhejNLMzZ4XC8xVTI2SHBHMFp4S1wvVjFWIn0.W10.QUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFB";
				$connection->sendEncapsulatedPacket($pk);
/*
				$pk = new ResourcePackClientResponsePacket();
				$pk->status = 4;
				$pk->packIds = [];
				$connection->sendEncapsulatedPacket($pk);

				$pk = new RequestChunkRadiusPacket();
				$pk->radius = 8;
				$connection->sendEncapsulatedPacket($pk);
*/
				$pk = new PING_DataPacket();
				$pk->pingID = rand(0, 100);
				//$connection->sendEncapsulatedPacket($pk);
				$connection->sendEncapsulatedPacket($pk);
				break;
			default:
				break;
		}
	}
	public function handleDataPacket(ClientConnection $connection, DataPacket $pk){
		echo "[Receive] " . get_class($pk) . "\n";
		switch(get_class($pk)){
			case LoginStatusPacket::class:
				//TODO
				break;
			case StartGamePacket::class:

				break;
			case FullChunkDataPacket::class:
				//print $pk->chunkX . " " . $pk->chunkZ . "\n";
				break;
			case UpdateBlockPacket::class:
				break;
		}
	}
	/**
	 * @return mixed
	 */
	public function getName(){
		return $this->name;
	}

	/**
	 * @param mixed $name
	 */
	public function setName($name){
		$this->name = $name;
	}

	public function tick(){
		foreach($this->connections as $connection){
			$connection->tick();
		}
	}

}