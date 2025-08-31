<?php

namespace core\staff\uis\actions\warn;

use core\utils\PlaySound;
use core\AtPlayer as Player;

use core\ui\windows\CustomForm;
use core\ui\elements\customForm\{
	Dropdown,
	Label,
	Input,
	Toggle
};

use core\Core;
use core\staff\entry\WarnEntry;
use core\staff\entry\WarnManager;
use core\utils\TextFormat;

class AddWarnUi extends CustomForm {

	public int $offset = 0;

	public function __construct(string $error = "", public $username = "") {
		parent::__construct("Warn Player");
		if ($error != "") {
			$this->addElement(new Label(TextFormat::RED . $error . TextFormat::WHITE . PHP_EOL . PHP_EOL));
			$this->offset = 1;
		}
		$this->addElement(new Input("Username", "sn3akrr", $username));
		$this->addElement(new Dropdown("Reason", array_keys(WarnManager::WTYPEMAP)));
		$this->addElement(new Toggle("Silent", false));
	}

	public function handle($response, Player $player) {
		$username = $response[0 + $this->offset];
		$reason = array_keys(WarnManager::WTYPEMAP)[$response[1 + $this->offset]] ?? "";
		$type = WarnManager::WTYPEMAP[$reason] ?? WarnEntry::TYPE_CHAT;
		$severe = WarnManager::RTOSEV[$reason] ?? false;
		$silent = $response[2 + $this->offset];

		if ($reason == "") {
			$player->showModal(new AddWarnUi("Invalid reason selected! (Key: " . $response[1 + $this->offset] . ")", $username));
			return;
		}

		Core::getInstance()->getStaff()->warn($username, $player, $reason, null, $type, $severe);
		$player->sendMessage(TextFormat::GN . $username . " was warned!");
		if (!$silent) {
			Core::announceToSS(TextFormat::RI . TextFormat::YELLOW . $username . TextFormat::RED . " has been " . TextFormat::BOLD . TextFormat::DARK_RED . "WARNED " . TextFormat::RESET . TextFormat::RED . "for: " . $reason, "random.anvil_land");
		}
	}
}
