<?php

namespace core\gadgets\entity;

use pocketmine\entity\{
	Entity,
	EntitySizeInfo
};
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class CakeBomb extends Entity {

	public int $aliveTicks = 0;

	public static function getNetworkTypeId(): string {
		return EntityIds::SHULKER_BULLET;
	}

	protected function getInitialDragMultiplier(): float {
		return 0.0;
	}

	protected function getInitialGravity(): float {
		return 0.1;
	}

	public function canSaveWithChunk(): bool {
		return false;
	}

	public function attack(EntityDamageEvent $source): void {
		$source->cancel();
	}

	public function entityBaseTick(int $tickDiff = 1): bool {
		$hasUpdate = parent::entityBaseTick($tickDiff);
		$this->aliveTicks++;

		if ($this->onGround) {
			$entity = new Cake($this->getLocation());
			$entity->spawnToAll();
			$this->flagForDespawn();
			return false;
		}

		return true;
	}

	protected function getInitialSizeInfo(): EntitySizeInfo {
		return new EntitySizeInfo(0.1, 0.1, 0.1);
	}
}
