<?php

namespace core\utils\entity;

use pocketmine\entity\object\ExperienceOrb;

class TempExperienceOrb extends ExperienceOrb {

	public int $maxAge = 120;
	public int $age = 0;

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
		$player = $this->getTargetPlayer();
		if ($player !== null && $player->getXpManager()->canPickupXp()) {
			$player->getXpManager()->onPickupXp($this->getXpValue());
			$this->flagForDespawn();
			return true;
		}
		return parent::entityBaseTick($tickDiff);
	}

	public function canSaveWithChunk(): bool {
		return false;
	}
}
