<?php

namespace core\gadgets\entity;

use pocketmine\entity\{
	Entity,
	EntitySizeInfo,
	Location
};
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;

class Cake extends Entity {

	public int $aliveTicks = 0;

	public static function getNetworkTypeId(): string {
		return "core:gadget.cake";
	}

	protected function getInitialDragMultiplier(): float {
		return 0.0;
	}

	protected function getInitialGravity(): float {
		return 0.1;
	}

	public function __construct(Location $location, ?CompoundTag $nbt = null) {
		parent::__construct($location, $nbt);
		$this->setScale(1.75);
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

		if ($this->aliveTicks >= 180) {
			$this->flagForDespawn();
			return false;
		}

		return true;
	}

	protected function getInitialSizeInfo(): EntitySizeInfo {
		return new EntitySizeInfo(0, 0, 0);
	}
}
