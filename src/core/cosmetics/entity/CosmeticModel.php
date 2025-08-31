<?php

namespace core\cosmetics\entity;

use pocketmine\entity\{
	Human,
	Location,
	Skin
};

use core\cosmetics\cape\Cape;
use core\cosmetics\effect\{
	idle\IdleEffect,
	trail\TrailEffect
};
use core\AtPlayer as Player;

class CosmeticModel extends Human {

	public int $ticks = 0;

	public Player $player;

	public ?Cape $cape = null;

	public ?IdleEffect $idleEffect = null;
	public ?TrailEffect $trailEffect = null;

	public function getPlayer(): Player {
		return $this->player;
	}

	public function getCape(): ?Cape {
		return $this->cape;
	}

	public function setCape(?Cape $cape = null): void {
		$this->cape = $cape;
		if ($cape === null) {
		} else {
			$cape->apply($this);
		}
	}

	public function getIdle(): ?IdleEffect {
		return $this->idleEffect;
	}

	public function setIdle(?IdleEffect $idle = null): void {
		$this->idleEffect = $idle;
	}

	public function getTrail(): ?TrailEffect {
		return $this->trailEffect;
	}

	public function setTrail(?TrailEffect $trail = null): void {
		$this->trailEffect = $trail;
	}

	public function __construct(Location $location, Skin $skin, Player $player) {
		parent::__construct($location, $skin, null);
		if ($player->isLoaded()) {
			$this->player = $player;

			$session = $player->getSession()->getCosmetics();
			$this->setCape($session->getEquippedCape());
			$this->setIdle($session->getEquippedIdle());
			$this->setTrail($session->getEquippedTrail());
		}
	}

	public function entityBaseTick(int $tickDiff = 1): bool {
		$hasUpdate = parent::entityBaseTick($tickDiff);
		if ($hasUpdate) {
			$this->ticks += $tickDiff;
			if ($this->ticks % 5 === 0) {
				$this->getIdle()?->activate($this);
				$this->getTrail()?->activate($this);
			}
			return true;
		}
		return false;
	}

	public function getEffectViewers(): array {
		return [$this->getPlayer()];
	}
}
