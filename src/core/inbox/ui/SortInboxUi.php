<?php

namespace core\inbox\ui;

use core\AtPlayer as Player;
use core\ui\windows\CustomForm;
use core\ui\elements\customForm\{
	Dropdown
};
use core\inbox\Sort;

class SortInboxUi extends CustomForm {

	public function __construct(Player $player, ?Sort $sort = null) {
		parent::__construct("Sort inbox");
		$sort = ($sort == null ? new Sort() : $sort);

		$this->addElement(new Dropdown("Type", ["ALL", "GLOBAL", "HERE"]));
		$this->addElement(new Dropdown("Status", ["ALL", "UNREAD", "READ"]));
		$this->addElement(new Dropdown("Sort by", ["NONE", "NEWEST", "OLDEST"]));
	}

	public function handle($response, Player $player) {
		$type = $response[0] - 1;
		$status = $response[1] - 1;
		$sort = $response[2] - 1;

		$sc = new Sort();
		$sc->type = $type;
		$sc->status = $status;
		$sc->sort = $sort;

		$player->showModal(new InboxUi($player, $sc));
	}
}
