<?php

namespace core\utils\entity;

use pocketmine\entity\object\FallingBlock;
use pocketmine\math\Vector3;

class TempFallingBlock extends FallingBlock {

	public int $aliveTicks = 0;

	protected function entityBaseTick(int $tickDiff = 1): bool {
		if ($this->closed) {
			return false;
		}

		$this->aliveTicks += $tickDiff;

		if (!$this->isFlaggedForDespawn()) {
			if (($this->onGround && $this->aliveTicks >= 10) || $this->aliveTicks >= 80) {
				$this->flagForDespawn();
				$this->teleport(new Vector3(0, 0, 0));
				$hasUpdate = true;
			}
		}

		$hasUpdate = $hasUpdate ?: parent::entityBaseTick($tickDiff);

		return $hasUpdate;
	}

	public function canSaveWithChunk(): bool {
		return false;
	}
}
