<?php

namespace core\cosmetics\entity;

use pocketmine\entity\{
	Entity,
	Location
};
use pocketmine\entity\projectile\Arrow as PMArrow;
use pocketmine\nbt\tag\CompoundTag;

use core\AtPlayer as Player;

class Arrow extends PMArrow {

	public bool $active = false;

	public function __construct(Location $location, ?Entity $shootingEntity, bool $critical, ?CompoundTag $nbt = null) {
		parent::__construct($location, $shootingEntity, $critical, $nbt);
		if ($shootingEntity instanceof Player) {
			if (($sb = ($cs = $shootingEntity->getSession()->getCosmetics())->getEquippedSnowball()) !== null) {
				if (!$cs->hasMaxProjectileEffects()) {
					$sb->activate($shootingEntity);
					$this->active = true;
					$cs->addProjectile();
				}
			}
		}
	}

	protected function entityBaseTick(int $tickDiff = 1): bool {
		$hasUpdate = parent::entityBaseTick($tickDiff);
		if (
			$this->active &&
			($player = $this->getOwningEntity()) !== null && $player instanceof Player &&
			$player->isConnected()
		) {
			if ($this->isCollided) {
				$this->active = false;
				$player->getSession()->getCosmetics()->takeProjectile();
				return true;
			}
			$player->getSession()->getCosmetics()->getEquippedArrow()?->tick($this);
		}
		return $hasUpdate;
	}

	protected function onDispose(): void {
		parent::onDispose();
		if ($this->active && ($player = $this->getOwningEntity()) !== null && $player instanceof Player) {
			$player->getSession()->getCosmetics()->takeProjectile();
		}
	}

	public function canSaveWithChunk(): bool {
		return false;
	}
}
