<?php

namespace client;

use client\protocol\LoginPacket;
use client\protocol\RequestChunkRadiusPacket;

use client\utils\Address;

use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\DisconnectPacket;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\network\mcpe\protocol\PlayStatusPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\FullChunkDataPacket;

use pocketmine\utils\Terminal;
use pocketmine\utils\UUID;

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

	public function addConnection(Address $address){
		$this->connections[] = new ClientConnection($this, $address);
	}

	public function handlePacket(ClientConnection $connection, Packet $packet){
		echo "[Receive]" . get_class($packet) . PHP_EOL;
		switch(get_class($packet)){
			case UNCONNECTED_PONG::class:
				$connection->setStatus(ClientConnection::STATUS_CONNECTED);
				echo "[Status]Connected to server." . PHP_EOL;
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
				$pk = new OPEN_CONNECTION_REQUEST_1();
				$pk->mtuSize = self::DEFAULT_MTU;
				$connection->sendPacket($pk);
				break;
			case OPEN_CONNECTION_REPLY_1::class:
				echo "[ServerID]" . $packet->serverID . PHP_EOL;
				echo "[Security]" . $packet->security . PHP_EOL;
				echo "[MTU]" . $packet->mtuSize . PHP_EOL;
				$pk = new OPEN_CONNECTION_REQUEST_2();
				$pk->serverAddress = $connection->getAddress()->getIp();
				$pk->serverPort = $connection->getAddress()->getPort();
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
				$pk->address = $connection->getAddress()->getIp();
				$pk->port = $connection->getAddress()->getPort();
				$pk->systemAddresses = $addresses;
				$pk->sendPing = mt_rand(1,100);
				$pk->sendPong = mt_rand(1,100);
				$connection->sendEncapsulatedPacket($pk);

				$connection->setStatus(ClientConnection::STATUS_CONNECTED_RAKNET);

				$uuid = UUID::fromRandom();
				$skin = zlib_decode(file_get_contents(__DIR__ . "/skin/skin.dat"));

				$pk = new LoginPacket();
				$pk->protocol = ProtocolInfo::CURRENT_PROTOCOL;
				$pk->chainData = ["extraData" => [
									"displayName" => $this->name,
									"identity" => $uuid->toString(),
									"XUID" => mt_rand(1000000000000000, 9999999999999999)
									],
									"identityPublicKey" => "MHYwEAYHKoZIzj0CAQYFK4EEACIDYgAE7nnZpCfxmCrSwDdBv7eBXXMtKhroxOriEr3hmMOJAuw/ZpQXj1K5GGtHS4CpFNttd1JYAKYoJxYgaykpie0EyAv3qiK6utIH2qnOAt3VNrQYXfIZJS/VRe3Il8Pgu9CB"
				];
				$pk->clientData = ["ClientRandomId" =>"123456789",
									"ServerAddress" => "127.0.0.1",
									"DeviceModel" => "",
									"DeviceOS" => 1,
									"SkinId" => "Standard_Custom",
									"SkinData" => base64_encode($skin),
									"SkinGeometryName" => "",
									"SkinGeometry" => "",
									"UIProfile" => 1,
									"LanguageCode" => "en_US",
									"GameVersion" => "1.1.0.4",
									"CapeData" => "",
									"GuiScale" => 0
									
				];
				$pk->header = ["alg" => "ES384",
								"x5u" => "MHYwEAYHKoZIzj0CAQYFK4EEACIDYgAE8ELkixyLcwlZryUQcu1TvPOmI2B7vX83ndnWRUaXm74wFfa5f/lwQNTfrLVHa2PmenpGI6JhIMUJaWZrjmMj90NoKNFSNBuKdm8rYiXsfaz3K36x/1U26HpG0ZxK/V1V"
				];

				$pk = $connection->compressBatch($pk);
				
				$connection->sendEncapsulatedPacket($pk, 2);

				$connection->setStatus(ClientConnection::STATUS_LOGINED);

/*
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
		echo "[Receive]" . get_class($pk) . PHP_EOL;
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
			case DisconnectPacket::class:
				echo "[DisconnectPacket]" . $pk->message . PHP_EOL;
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