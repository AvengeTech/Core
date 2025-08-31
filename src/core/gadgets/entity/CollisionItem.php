<?php

namespace core\gadgets\entity;

use pocketmine\entity\Location;
use pocketmine\entity\object\ItemEntity;
use pocketmine\item\Item;
use pocketmine\player\Player;

class CollisionItem extends ItemEntity {

	public float $spawned;

	const COLLIDE_DELAY = 0.2;

	public function __construct(Location $loc, Item $item, public \Closure $onCollide) {
		parent::__construct($loc, $item);
		$this->spawned = microtime(true);
	}

	public function isMergeable(ItemEntity $item): bool {
		return false;
	}

	public function canSaveWithChunk(): bool {
		return false;
	}

	public function onCollideWithPlayer(Player $player): void {
		if (microtime(true) < $this->spawned + self::COLLIDE_DELAY) return;

		($this->onCollide)($player, $this);
		$this->flagForDespawn();
	}
}
