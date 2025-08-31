<?php

namespace core\staff\uis\actions;

use pocketmine\{
	Server
};

use core\AtPlayer as Player;
use core\ui\windows\CustomForm;
use core\ui\elements\customForm\{
	Label,
	Input,
};
use core\utils\TextFormat;

class KickUi extends CustomForm {

	public function __construct($error = "") {
		parent::__construct("Kick Player");

		$this->addElement(new Label($error == "" ? "" : TextFormat::RED . "Error: " . $error));
		$this->addElement(new Input("Username", "sn3akrr"));
		$this->addElement(new Input("Reason", "Bullying"));
	}

	public function handle($response, Player $player) {
		$username = $response[1];
		$reason = $response[2];

		$kicking = Server::getInstance()->getPlayerByPrefix($username);
		if (!$kicking instanceof Player) {
			$player->showModal(new KickUi("Player by username '" . $username . "' not found."));
			return;
		}

		if ($reason == "") {
			$player->showModal(new KickUi("A reason must be provided!"));
			return;
		}

		$kicking->kick(TextFormat::RED . "You were kicked by staff member: " . TextFormat::YELLOW . $player->getName() . TextFormat::RED . ". Reason: " . TextFormat::AQUA . $reason, false);
		$player->sendMessage(TextFormat::GN . "Successfully kicked " . $kicking->getName() . "!");
	}
}
