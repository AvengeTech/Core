<?php

namespace core\items;

use core\items\projectile\WindCharge as ProjectileWindCharge;
use pocketmine\entity\Location;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\math\Vector3;
use pocketmine\item\ItemUseResult;
use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;

class WindCharge extends Item{

	public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems) : ItemUseResult{
		$this->pop();
		
		NetworkBroadcastUtils::broadcastPackets($player->getWorld()->getPlayers(), [
			PlaySoundPacket::create(
				"random.bow",
				$player->getPosition()->x,
				$player->getPosition()->y,
				$player->getPosition()->z,
				1.0,
				1.0
			)
		]);

		$entity = new ProjectileWindCharge(
			Location::fromObject(
				$player->getEyePos(), 
				$player->getWorld(), 
				($player->getLocation()->yaw > 180 ? 360 : 0) - $player->getLocation()->yaw, 
				-$player->getLocation()->pitch
			),
			$player
		);
		$entity->setMotion($directionVector);
		$entity->spawnToAll();

		return ItemUseResult::SUCCESS();
	}
}