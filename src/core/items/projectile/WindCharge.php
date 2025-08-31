<?php

namespace core\items\projectile;

use pocketmine\block\Block;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\projectile\Throwable;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\RayTraceResult;
use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\world\particle\HugeExplodeParticle;

class WindCharge extends Throwable{

	public static function getNetworkTypeId() : string{ return EntityIds::WIND_CHARGE_PROJECTILE; }

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo(0.3125, 0.3125); }

	protected function getInitialDragMultiplier() : float{ return 0; }

	protected function getInitialGravity() : float{ return 0; }

	public function canSaveWithChunk() : bool{ return false; }

	protected function onHit(ProjectileHitEvent $event): void{
		$source = $this->getPosition();

		NetworkBroadcastUtils::broadcastPackets($this->getWorld()->getPlayers(), [
			PlaySoundPacket::create(
				"wind_charge.burst",
				$this->getPosition()->x,
				$this->getPosition()->y,
				$this->getPosition()->z,
				1.0,
				1.0
			)
		]);
		$this->getWorld()->addParticle($this->getPosition(), new HugeExplodeParticle());
		
		$scale = 2.5;
		$minX = (int) floor($source->x - $scale - 1);
		$maxX = (int) ceil($source->x - $scale + 1);
		$minY = (int) floor($source->y - $scale - 1);
		$maxY = (int) ceil($source->y - $scale + 1);
		$minZ = (int) floor($source->z - $scale - 1);
		$maxZ = (int) ceil($source->z - $scale + 1);

		$bound = new AxisAlignedBB($minX, $minY, $minZ, $maxX, $maxY, $maxZ);
		$list = $source->getWorld()->getNearbyEntities($bound);

		foreach($list as $entity){
			$entityPos = $entity->getPosition();
			$distance = $entityPos->distance($source) / $scale;
			$motion = $entityPos->subtractVector($source)->normalize();
			$impact = (1 - $distance) * 1.5;

			if($impact <= 0) continue;

			($distance <= 1) ? $vertical = 0 : $vertical = 0.75;
			$entity->setMotion($entity->getMotion()->addVector($motion->multiply($impact)->add(0, $vertical, 0)));
		}

		$this->flagForDespawn();
	}

	protected function onHitBlock(Block $blockHit, RayTraceResult $hitResult) : void{
		$source = $this->getPosition();

		NetworkBroadcastUtils::broadcastPackets($this->getWorld()->getPlayers(), [
			PlaySoundPacket::create(
				"wind_charge.burst",
				$this->getPosition()->x,
				$this->getPosition()->y,
				$this->getPosition()->z,
				1.0,
				1.0
			)
		]);
		$this->getWorld()->addParticle($this->getPosition(), new HugeExplodeParticle());
		
		$scale = 2.5;
		$minX = (int) floor($source->x - $scale - 1);
		$maxX = (int) ceil($source->x - $scale + 1);
		$minY = (int) floor($source->y - $scale - 1);
		$maxY = (int) ceil($source->y - $scale + 1);
		$minZ = (int) floor($source->z - $scale - 1);
		$maxZ = (int) ceil($source->z - $scale + 1);

		$bound = new AxisAlignedBB($minX, $minY, $minZ, $maxX, $maxY, $maxZ);
		$list = $source->getWorld()->getNearbyEntities($bound);

		foreach($list as $entity){
			$entityPos = $entity->getPosition();
			$distance = $entityPos->distance($source) / $scale;
			$motion = $entityPos->subtractVector($source)->normalize();
			$impact = (1 - $distance) * 1.5;

			if($impact <= 0) continue;

			($distance <= 1) ? $vertical = 0 : $vertical = 0.75;
			$entity->setMotion($entity->getMotion()->addVector($motion->multiply($impact)->add(0, $vertical, 0)));
		}

		$this->flagForDespawn();
	}
}