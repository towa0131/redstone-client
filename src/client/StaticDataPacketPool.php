<?php

namespace client;

use pocketmine\network\mcpe\protocol\UnknownPacket;
use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\utils\Binary;

use raklib\protocol\PONG_DataPacket;

use client\protocol\LoginPacket;

class StaticDataPacketPool {
	private static $packetPool = [];

	private static function registerPacket($class){
		$id = $class::NETWORK_ID;
		StaticDataPacketPool::$packetPool[$id] = $class;
	}

	/**
	 * @param $id
	 *
	 * @return DataPacket
	 */
	public static function getPacketFromPool($id){
		if(empty(StaticDataPacketPool::$packetPool)) StaticDataPacketPool::registerPackets();

		/** @var DataPacket $class */
		$class = StaticDataPacketPool::$packetPool[$id];
		if($class !== null){
			return new $class;
		}
		return null;
	}

	private static function registerPackets(){
		require_once "vendor/autoload.php";//composer autoloader

		StaticDataPacketPool::$packetPool = new \SplFixedArray(256);

		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\LoginPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\PlayStatusPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\ServerToClientHandshakePacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\ClientToServerHandshakePacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\DisconnectPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\ResourcePacksInfoPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\ResourcePackStackPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\ResourcePackClientResponsePacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\TextPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\SetTimePacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\StartGamePacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\AddPlayerPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\AddEntityPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\RemoveEntityPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\AddItemEntityPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\AddHangingEntityPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\TakeItemEntityPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\MoveEntityPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\MovePlayerPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\RiderJumpPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\UpdateBlockPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\AddPaintingPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\ExplodePacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\LevelSoundEventPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\LevelEventPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\BlockEventPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\EntityEventPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\MobEffectPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\UpdateAttributesPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\InventoryTransactionPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\MobEquipmentPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\MobArmorEquipmentPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\InteractPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\BlockPickRequestPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\EntityPickRequestPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\PlayerActionPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\EntityFallPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\HurtArmorPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\SetEntityDataPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\SetEntityMotionPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\SetEntityLinkPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\SetHealthPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\SetSpawnPositionPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\AnimatePacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\RespawnPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\ContainerOpenPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\ContainerClosePacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\PlayerHotbarPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\InventoryContentPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\InventorySlotPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\ContainerSetDataPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\CraftingDataPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\CraftingEventPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\GuiDataPickItemPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\AdventureSettingsPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\BlockEntityDataPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\PlayerInputPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\FullChunkDataPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\SetCommandsEnabledPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\SetDifficultyPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\ChangeDimensionPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\SetPlayerGameTypePacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\PlayerListPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\SimpleEventPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\EventPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\SpawnExperienceOrbPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\ClientboundMapItemDataPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\MapInfoRequestPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\RequestChunkRadiusPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\ChunkRadiusUpdatedPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\ItemFrameDropItemPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\GameRulesChangedPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\CameraPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\BossEventPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\ShowCreditsPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\AvailableCommandsPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\CommandRequestPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\CommandBlockUpdatePacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\CommandOutputPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\UpdateTradePacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\UpdateEquipPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\ResourcePackDataInfoPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\ResourcePackChunkDataPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\ResourcePackChunkRequestPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\TransferPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\PlaySoundPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\StopSoundPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\SetTitlePacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\AddBehaviorTreePacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\StructureBlockUpdatePacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\ShowStoreOfferPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\PurchaseReceiptPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\PlayerSkinPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\SubClientLoginPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\WSConnectPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\SetLastHurtByPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\BookEditPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\NpcRequestPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\PhotoTransferPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\ModalFormRequestPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\ModalFormResponsePacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\ServerSettingsRequestPacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\ServerSettingsResponsePacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\ShowProfilePacket::class);
		StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\SetDefaultGameTypePacket::class);
		//StaticDataPacketPool::registerPacket(\pocketmine\network\mcpe\protocol\BatchPacket::class);
	}

	public static function getPacket($buffer){
		$pid = ord($buffer{0});
		if(($data = StaticDataPacketPool::getPacketFromPool($pid)) === null){
			$data = new UnknownPacket();
			$data->payload = $buffer;
		}
		$data->setBuffer(substr($buffer, 1));

		return $data;
	}
}