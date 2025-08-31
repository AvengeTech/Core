<?php

namespace core\chat\antispam;

use core\{
	Core,
	AtPlayer as Player
};
use core\chat\Chat;

class AntiSpam {

	public $plugin;

	public $delays = [
		0 => 5,
		"default" => 5,
		"endermite" => 5,
		"blaze" => 4,
		"ghast" => 3,
		"enderman" => 3,
		"wither" => 2,
		"enderdragon" => 0,

		"youtuber" => 4,
		"youtuber+" => 3,
		"youtuber++" => 2,
		"youtuber+++" => 0,

		"builder" => 0,
		"artist" => 0,
		"developer" => 0,
		"trainee" => 0,
		"jr_mod" => 0,
		"mod" => 0,
		"sr_mod" => 0,
		"head_mod" => 0,
		"manager" => 0,
		"owner" => 0,
	];

	public $talked = [];

	public function __construct(Core $plugin) {
		$this->plugin = $plugin;
	}

	public function getPlugin() {
		return $this->plugin;
	}

	public function getChat() : Chat {
		return $this->plugin->getChat();
	}

	public function setTalked(Player $player) {
		$this->talked[$player->getName()] = time();
	}

	public function canTalkIn(Player $player) {
		return - ((time() - $this->talked[$player->getName()]) - $this->delays[$player->getRank()]);
	}

	public function talked(Player $player) {
		if (!isset($this->talked[$player->getName()])) return false;
		if (time() - $this->talked[$player->getName()] >= $this->delays[$player->getRank()]) return false;
		return true;
	}
}
