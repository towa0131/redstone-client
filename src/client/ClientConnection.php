<?php

namespace client;

use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\network\mcpe\protocol\DataPacket as PMDataPacket;
use pocketmine\network\mcpe\protocol\PacketPool;

use pocketmine\utils\BinaryStream;

use raklib\protocol\ACK;
use raklib\protocol\ConnectedPong;
use raklib\protocol\ConnectionRequestAccepted;
use raklib\protocol\Datagram;
use raklib\protocol\DisconnectionNotification;
use raklib\protocol\EncapsulatedPacket;
use raklib\protocol\IncompatibleProtocolVersion;
use raklib\protocol\Packet;
use raklib\protocol\UnconnectedPing;
use raklib\server\UDPServerSocket;

use client\Tickable;

use client\utils\Address;

class ClientConnection extends UDPServerSocket implements Tickable{

	const START_PORT = 49666;

	const STATUS_NONE = 0;
	const STATUS_CONNECTED = 1;
	const STATUS_CONNECTED_RAKNET = 2;
	const STATUS_LOGINED =3;
	const STATUS_JOINED = 4;
	const STATUS_DISCONNECTED = 5;

	private static $instanceId = 0;

	/** @var  MCPEClient */
	private $client;
	/** @var  Address */
	private $address;

	private $name;
	private $clientId;

	private $sequenceNumber;
	private $ackQueue;

	private $lastSendTime;
	private $pingCount;

	private $status;

	public function __construct(MCPEClient $client, Address $address){
		$this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		if(@socket_bind($this->socket, "0.0.0.0", ClientConnection::START_PORT + ClientConnection::$instanceId) === true){
			socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 0);
			$this->setSendBuffer(1024 * 1024 * 8)->setRecvBuffer(1024 * 1024 * 8);
		}
		socket_set_nonblock($this->socket);
		ClientConnection::$instanceId++;

		$this->client = $client;
		$this->address = $address;
		$this->name = "";
		$this->clientId = mt_rand(1000, 9999);

		$this->sequenceNumber = 0;
		$this->ackQueue = [];
		$this->lastSendTime = -1;
		$this->pingCount = 0;

		$this->status = ClientConnection::STATUS_NONE;
	}

	/**
	   * @return string
	   */
	public function getName(){
		return $this->name;
	}

	/**
	   * @param string $name
	   */
	public function setName($name){
		$this->name = $name;
	}

	/**
	   * @param int
	   */
	public function getClientId(){
		return $this->clientId;
	}

	/**
	   * @param int $id
	   */
	public function setClientId($clientId){
		$this->clientId = $clientId;
	}

	public function sendPacket(Packet $packet){
		echo "[Send]" . get_class($packet) . PHP_EOL;
		$this->lastSendTime = time();
		$packet->encode();
		return $this->writePacket($packet->buffer, $this->address->getIp(), $this->address->getPort());
	}

	public function sendEncapsulatedPacket($packet, $messageIndex = null){
		if($packet instanceof Packet || $packet instanceof PMDataPacket) {
			echo "[Send]" . get_class($packet) . PHP_EOL;
			$packet->encode();
			$encapsulated = new EncapsulatedPacket();
			$encapsulated->messageIndex = $messageIndex;
			$encapsulated->reliability = 0;
			$encapsulated->buffer = $packet->buffer;

			$sendPacket = new Datagram();
			$sendPacket->seqNumber = $this->sequenceNumber++;
			$sendPacket->packets[] = $encapsulated->toBinary();

			return $this->sendPacket($sendPacket);
		}else{
			return false;
		}
	}

	public function compressBatch($packet){
		$pk = new BatchPacket();
		$pk->addPacket($packet);
		echo "[Compress]" . get_class($packet) . PHP_EOL;
		return $pk;	
	}

	public function receivePacket(){
		if ($this->readPacket($buffer, $this->address->getIp(), $this->address->getPort()) > 0) {
			if (($packet = StaticPacketPool::getPacketFromPool(ord($buffer{0}))) !== null) {
				$packet->buffer = $buffer;
				$packet->decode();
				if ($packet instanceof Datagram) {
					$this->ackQueue[$packet->seqNumber] = $packet->seqNumber;
				}
				return $packet;
			}
			return $buffer;
		}else{
			return false;
		}
	}

	public function tick(){
		if($this->getStatus() === ClientConnection::STATUS_NONE && $this->lastSendTime !== time()){
			$ping = new UnconnectedPing();
			$ping->pingID = $this->pingCount++;
			$this->sendPacket($ping);
		}
		if(count($this->ackQueue) > 0 && $this->lastSendTime !== time()){
			$ack = new ACK();
			$ack->packets = $this->ackQueue;
			$this->sendPacket($ack);
			$this->ackQueue = [];
		}
		$pk = $this->receivePacket();
		if($pk instanceof Packet){
			if($pk instanceof Datagram){
				foreach($pk->packets as $pk){
					$id = ord($pk->buffer{0});
					if(ConnectionRequestAccepted::$ID === $id){
						$new = new ConnectionRequestAccepted();
						$new->buffer = $pk->buffer;
						$new->decode();
						$this->client->handlePacket($this, $new);
					}elseif(ConnectedPong::$ID === $id){
						$new = new ConnectedPong();
						$new->buffer = $pk->buffer;
						$new->decode();
						$this->client->handlePacket($this, $new);
					}elseif(DisconnectionNotification::$ID === $id){
						$this->setStatus(ClientConnection::STATUS_DISCONNECTED);
						echo "[Status]Disconnected from server." . PHP_EOL;
						echo "Thanks for using!" . PHP_EOL;
						exit(0);
					}elseif(IncompatibleProtocolVersion::$ID === $id){
						$this->setStatus(ClientConnection::STATUS_DISCONNECTED);
						echo "[Status]Incompatible protocol version." . PHP_EOL;
						echo "Thanks for using!" . PHP_EOL;
						exit(0);
					}else{
						$data = StaticPacketPool::getPacket($pk->buffer);
						if($data !== null){
							echo "[Receive]" . get_class($data) . PHP_EOL;
						}else{
							if($this->getStatus() >= ClientConnection::STATUS_CONNECTED_RAKNET){
								$new = new BatchPacket();
								$new->setBuffer($pk->buffer, 0);
								$new->payload = $pk->buffer;
								$new->decode();
								$packets = $new->getPackets();
								foreach($packets as $buf){
									$packet = StaticDataPacketPool::getPacketFromPool(ord($buf{0}));
									$packet->setBuffer($buf, 0);
									$packet->decode();
									$this->client->handleDataPacket($this, $packet);
								}
							}
						}
					}
				}
			}else{
				$this->client->handlePacket($this, $pk);
			}
		}elseif($pk !== false){
			echo $pk . PHP_EOL;
		}
	}

	/**
	   * @return MCPEClient
	   */
	public function getClient(){
		return $this->client;
	}

	/**
	   * @return Address
	   */
	public function getAddress(){
		return $this->address;
	}

	/**
	   * @param int $status
	   */
	public function setStatus($status){
		$this->status = $status;
	}

	/**
	   * @return int
	   */
	public function getStatus(){
		return $this->status;
	}
}