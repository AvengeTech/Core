<?php

namespace core\etc\pieces\afk;

use core\{
	Core,
	AtPlayer as Player
};

class Afk {

	const AFK_TIME = 180;

	public $plugin;
	public $afk = [];

	public function __construct(Core $plugin) {
		$this->plugin = $plugin;
	}

	public function canKick(Player $player) {
		return $this->getTime($player) <= 0 && !$player->isFrozen();
	}

	public function hasTime(Player $player) {
		return isset($this->afk[$player->getName()]);
	}

	public function setTime(Player $player) {
		$this->afk[$player->getName()] = time() + self::AFK_TIME;
	}

	public function unsetTime(Player $player) {
		unset($this->afk[$player->getName()]);
	}

	public function getTime(Player $player) {
		return $this->afk[$player->getName()] - time();
	}
}
