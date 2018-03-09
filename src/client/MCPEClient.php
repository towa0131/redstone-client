<?php

namespace client;

use client\protocol\LoginPacket;
use client\protocol\RequestChunkRadiusPacket;

use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\network\mcpe\protocol\PlayStatusPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\protocol\FullChunkDataPacket;

use pocketmine\utils\Terminal;

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

class MCPEClient implements Tickable{

	const DEFAULT_MTU = 1465;

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
		echo "[Receive] " . get_class($packet) . PHP_EOL;
		switch(get_class($packet)){
			case UNCONNECTED_PONG::class:
				$rawData = $packet->serverName;
				$data = explode(";", $rawData);
				echo "[Motd]" . Terminal::toANSI($data[1]) . PHP_EOL;
				echo "[Protocol]" . $data[2] . PHP_EOL;
				echo "[Version]" . $data[3] . PHP_EOL;
				echo "[OnlinePlayers]" . $data[4] . PHP_EOL;
				echo "[MaxPlayers]" . $data[5] . PHP_EOL;
				echo "[ServerID]" . $data[6] . PHP_EOL;
				echo "[ServerName]" . $data[7] . PHP_EOL;
				echo "[Gamemode]" . $data[8] . PHP_EOL;
				$connection->setName($rawData);
				$connection->setIsConnected(true);
				$pk = new OPEN_CONNECTION_REQUEST_1();
				$pk->mtuSize = self::DEFAULT_MTU;
				$connection->sendPacket($pk);
				break;
			case OPEN_CONNECTION_REPLY_1::class:
				echo "[ServerID]" . $packet->serverID . PHP_EOL;
				echo "[Security]" . $packet->security . PHP_EOL;
				echo "[MTU]" . $packet->mtuSize . PHP_EOL;
				$pk = new OPEN_CONNECTION_REQUEST_2();
				$pk->serverAddress = $connection->getIp();
				$pk->serverPort = $connection->getPort();
				$pk->mtuSize = self::DEFAULT_MTU;
				$pk->clientID =$connection->getClientId();
				$connection->sendPacket($pk);
				break;
			case OPEN_CONNECTION_REPLY_2::class:
				echo "[MTU]" . $packet->mtuSize . PHP_EOL;
				$pk = new CLIENT_CONNECT_DataPacket();
				$pk->clientID = $connection->getClientId();
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
/*
				$pk = new LoginPacket();
				$pk->protocol = ProtocolInfo::CURRENT_PROTOCOL;
				$pk->string = "eyJ4NXUiOiJNSFl3RUFZSEtvWkl6ajBDQVFZRks0RUVBQ0lEWWdBRThFTGtpeHlMY3dsWnJ5VVFjdTFUdlBPbUkyQjd2WDgzbmRuV1JVYVhtNzR3RmZhNWZcL2x3UU5UZnJMVkhhMlBtZW5wR0k2SmhJTVVKYVdacmptTWo5ME5vS05GU05CdUtkbThyWWlYc2ZhejNLMzZ4XC8xVTI2SHBHMFp4S1wvVjFWIn0.W10.QUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFB";
				$connection->sendEncapsulatedPacket($pk);

				$pk = new RequestChunkRadiusPacket();
				$pk->radius = 8;
				$connection->sendEncapsulatedPacket($pk);
*/
				$pk = new PING_DataPacket();
				$pk->pingID = mt_rand(0, 100);
				$connection->sendEncapsulatedPacket($pk);
				break;
			default:
				break;
		}
	}
	public function handleDataPacket(ClientConnection $connection, DataPacket $pk){
		echo "[Receive] " . get_class($pk) . PHP_EOL;
		switch(get_class($pk)){
			case PlayStatusPacket::class:
				echo "[PlayStatusPacket]" . $pk->status . PHP_EOL;
				break;
			case StartGamePacket::class:
				//TODO
				break;
			case FullChunkDataPacket::class:
				//echo $pk->chunkX . " " . $pk->chunkZ . PHP_EOL;
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