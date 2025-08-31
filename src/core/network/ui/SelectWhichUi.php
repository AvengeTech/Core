<?php

namespace core\network\ui;

use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;
use core\{
	Core,
	AtPlayer as Player
};
use core\utils\TextFormat;
use core\rank\Structure as RS;

class SelectWhichUi extends SimpleForm {

	public $type;
	public $servers = [];

	public function __construct(Player $player, string $type) {
		parent::__construct("Which one?", "Select which server you'd like to connect to!");
		$servers = Core::getInstance()->getNetwork()->getServerManager()->getServersByType($type);
		foreach ($servers as $key => $server) {
			if (($server->isSubServer() || stristr($server->getIdentifier(), "archive")) || $server->isPrivate() && !$player->isStaff()) {
				unset($servers[$key]);
			}
		}
		$this->servers = array_values($servers);
		foreach ($this->servers as $server) {
			$this->addButton(new Button($server->getId() . PHP_EOL . ($server->isOnline() ? $server->getPlayerCount() . " players online!" : TextFormat::RED . "Offline")));
		}
	}

	public function handle($response, Player $player) {
		foreach ($this->servers as $key => $server) {
			if ($response == $key) {
				if (!$server->isOnline()) {
					$player->sendMessage(TextFormat::RI . "This server is offline, try again later!");
					return;
				}
				if ($server->isRestricted() && $server->getRestricted() > RS::RANK_HIERARCHY[$player->getRank()] && !$server->onWhitelist($player)) {
					$player->sendMessage(TextFormat::RI . "This server is restricted! You cannot access it without " . $server->restricted . " rank or higher!");
					return false;
				}

				if (!$server->canTransfer($player)) {
					$player->sendMessage(TextFormat::RI . "Cannot connect to server; Either full or private!");
					return;
				}
				if ($player->isTransferring()) {
					$player->sendMessage(TextFormat::RI . "You are already connecting to a server!");
					return;
				}
				$server->transfer($player, TextFormat::GI . "Connected to " . TextFormat::AQUA . $server->getId());
				break;
			}
		}
	}
}
