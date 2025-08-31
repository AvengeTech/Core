<?php

namespace core\utils\entity;

use pocketmine\entity\object\ItemEntity;
use pocketmine\player\Player;

class TempItemEntity extends ItemEntity {

	public int $age = 0;
	public int $maxAge = 600;

	public function getMaxAge(): int {
		return $this->maxAge;
	}

	public function setMaxAge(int $age): void {
		$this->maxAge = $age;
	}

	protected function entityBaseTick(int $tickDiff = 1): bool {
		$this->age += $tickDiff;
		if ($this->age > $this->maxAge) {
			$this->flagForDespawn();
			return true;
		}
		return parent::entityBaseTick($tickDiff);
	}

	public function isMergeable(ItemEntity $entity): bool {
		return false;
	}
	public function canSaveWithChunk(): bool {
		return false;
	}
	public function onCollideWithPlayer(Player $player): void {
	}
}
