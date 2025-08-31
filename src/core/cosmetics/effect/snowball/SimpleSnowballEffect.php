<?php

namespace core\cosmetics\effect\snowball;

use pocketmine\entity\Entity;
use pocketmine\world\particle\Particle;
use pocketmine\world\sound\Sound;

use core\AtPlayer as Player;
use core\cosmetics\Cosmetics;
use core\cosmetics\entity\CosmeticModel;

abstract class SimpleSnowballEffect extends SnowballEffect {

	abstract public function getSound(): ?Sound;

	abstract public function getParticle(): Particle;

	public function activate(Player|CosmeticModel $player): void {
		if (($sound = $this->getSound()) !== null && ($player instanceof Player || $player->getOwningEntity() instanceof Player))
			$player->getPosition()->getWorld()->addSound($player->getPosition(), $sound, Cosmetics::getEffectViewers($player instanceof Player ? $player : $player->getOwningEntity(), $player->getPosition()));
	}

	public function tick(Entity $entity): void {
		$this->ticks++;
		if ($this->ticks % 2 == 0)
			$entity->getPosition()->getWorld()->addParticle($entity->getPosition(), $this->getParticle(), $viewers = Cosmetics::getEffectViewers($entity->getOwningEntity(), $entity->getPosition()));
	}
}
