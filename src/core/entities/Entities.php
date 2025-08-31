<?php

namespace core\entities;

use core\{
	Core,
	AtPlayer as Player
};
use core\entities\bots\Bots;
use core\entities\floatingtext\FloatingText;

class Entities {

	public CustomEntityRegistry $customEntityRegistry;

	public Bots $bots;
	public FloatingText $floatingtext;

	public function __construct(public Core $plugin) {
		$this->customEntityRegistry = new CustomEntityRegistry();

		$this->bots = new Bots($plugin);
		$this->floatingtext = new FloatingText($plugin);
	}

	public function tick(): void {
		$this->getBots()->tick();
		$this->getFloatingText()->tick();
	}

	/**
	 * Trigger world change events for packet-based entities
	 * @param Player $player
	 * @param string $newlevel
	 */
	public function changeLevel(Player $player, string $newlevel): void {
		$this->getBots()->changeLevel($player, $newlevel);
		$this->getFloatingText()->changeLevel($player, $newlevel);
	}

	public function onJoin(Player $player): void {
		$this->getBots()->onJoin($player);
		$this->getFloatingText()->onJoin($player);
	}

	public function onQuit(Player $player): void {
		$this->getBots()->onQuit($player);
		$this->getFloatingText()->onQuit($player);
	}

	public function getCustomEntityRegistry(): CustomEntityRegistry {
		return $this->customEntityRegistry;
	}

	public function getBots(): Bots {
		return $this->bots;
	}

	public function getFloatingText(): FloatingText {
		return $this->floatingtext;
	}
}
