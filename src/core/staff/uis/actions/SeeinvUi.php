<?php

namespace core\staff\uis\actions;

use core\AtPlayer as Player;
use core\ui\windows\CustomForm;
use core\ui\elements\customForm\{
	Label,
	Dropdown
};

use core\Core;
use core\utils\TextFormat;

class SeeinvUi extends CustomForm {

	public $players = [];

	public function __construct($error = "") {
		parent::__construct("Seeinv");
		$this->addElement(new Label($error == "" ? "" : TextFormat::RED . "Error: " . $error));

		$players = Core::getInstance()->getServer()->getOnlinePlayers();
		foreach ($players as $player) {
			$this->players[] = $player->getName();
		}

		$this->addElement(new Dropdown("Player?", $this->players));
	}

	public function handle($response, Player $player) {
		$p = Core::getInstance()->getServer()->getPlayerExact($this->players[$response[1]]);
		if (!$p instanceof Player) {
			$player->showModal(new SeeinvUi("Player no longer online!"));
			return;
		}
		$p->getSeeInv()->doOpen($player);
	}
}
