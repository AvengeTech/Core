<?php

namespace core\network\server\ui;

use core\AtPlayer as Player;
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;

class SelectQueueUi extends SimpleForm {

	public function __construct(Player $player) {
		parent::__construct("Navigator", "Select a server to view it's queue!");

		$this->addButton(new Button("Lobbies"));
		$this->addButton(new Button("SkyBlock"));
		$this->addButton(new Button("Prison"));
	}

	public function handle($response, Player $player) {
		if ($response == 0) {
			$player->showModal(new JoinQueueUi($player, "lobby", true));
			return;
		}
		if ($response == 1) {
			$player->showModal(new JoinQueueUi($player, "skyblock", true));

			return;
		}
		if ($response == 2) {
			$player->showModal(new JoinQueueUi($player, "prison", true));
			return;
		}
	}
}
