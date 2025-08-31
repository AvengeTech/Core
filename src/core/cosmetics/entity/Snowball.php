<?php

namespace core\cosmetics\entity;

use pocketmine\entity\{
	Entity,
	Location
};
use pocketmine\entity\projectile\Snowball as PMSnowball;
use pocketmine\nbt\tag\CompoundTag;

use core\AtPlayer as Player;

class Snowball extends PMSnowball {

	public bool $active = false;

	public function __construct(Location $location, ?Entity $shootingEntity, ?CompoundTag $nbt = null) {
		parent::__construct($location, $shootingEntity, $nbt);
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
			$player->getSession()->getCosmetics()->getEquippedSnowball()?->tick($this);
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
