<?php
namespace client;

use raklib\protocol\MessageIdentifiers;

class StaticPacketPool {

    private static $packetPool = [];
    private static function registerPacket($class, $extraIds = null){
        if($extraIds === null){
            StaticPacketPool::$packetPool[$class::$ID] = new $class;
        }else{
            foreach($extraIds as $extraId){
                StaticPacketPool::$packetPool[$extraId] = new $class;
            }
        }
    }

    /**
     * @param $id
     *
     * @return Packet
     */
    public static function getPacketFromPool($id){
        if(empty(StaticPacketPool::$packetPool)){
        	StaticPacketPool::registerPackets();
        }
        if(isset(StaticPacketPool::$packetPool[$id])){
            return clone StaticPacketPool::$packetPool[$id];
        }

        return null;
    }
    private static function registerPackets(){
        require_once "vendor/autoload.php"; //composer autoloader

        StaticPacketPool::registerPacket(\raklib\protocol\UnconnectedPing::class);
        StaticPacketPool::registerPacket(\raklib\protocol\UnconnectedPingOpenConnections::class);
        StaticPacketPool::registerPacket(\raklib\protocol\ConnectionRequestAccepted::class);
        StaticPacketPool::registerPacket(\raklib\protocol\DisconnectionNotification::class);
        StaticPacketPool::registerPacket(\raklib\protocol\OpenConnectionRequest1::class);
        StaticPacketPool::registerPacket(\raklib\protocol\OpenConnectionReply1::class);
        StaticPacketPool::registerPacket(\raklib\protocol\OpenConnectionRequest2::class);
        StaticPacketPool::registerPacket(\raklib\protocol\OpenConnectionReply2::class);
        StaticPacketPool::registerPacket(\raklib\protocol\UnconnectedPong::class);
        StaticPacketPool::registerPacket(\raklib\protocol\AdvertiseSystem::class);
        StaticPacketPool::registerPacket(\raklib\protocol\IncompatibleProtocolVersion::class);
        StaticPacketPool::registerPacket(\raklib\protocol\ConnectedPing::class);
        StaticPacketPool::registerPacket(\raklib\protocol\ConnectedPong::class);
        StaticPacketPool::registerPacket(\raklib\protocol\NewIncomingConnection::class);
        StaticPacketPool::registerPacket(\raklib\protocol\NACK::class);
        StaticPacketPool::registerPacket(\raklib\protocol\ACK::class);
        StaticPacketPool::registerPacket(\raklib\protocol\Datagram::class, [MessageIdentifiers::ID_RESERVED_3, MessageIdentifiers::ID_RESERVED_4, MessageIdentifiers::ID_RESERVED_5, MessageIdentifiers::ID_RESERVED_6, MessageIdentifiers::ID_RESERVED_7, MessageIdentifiers::ID_RESERVED_8, MessageIdentifiers::ID_RESERVED_9]);
    }

    public static function getPacket($buffer){
        $pid = ord($buffer{0});
        $data = StaticPacketPool::getPacketFromPool($pid);
        if($data === null){
        	return null;
        }
        return $data;
    }
}