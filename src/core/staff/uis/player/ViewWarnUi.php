<?php

namespace core\staff\uis\player;

use core\AtPlayer as Player;
use core\staff\entry\{
	WarnManager,
	WarnEntry
};
use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;
use core\user\User;
use pocketmine\promise\Promise;

class ViewWarnUi extends SimpleForm {

	/** @param WarnEntry[] $warns */
	public function __construct(public WarnEntry $entry, public array $warns) {
		$byUser = $entry->getByUser();
		if ($byUser instanceof Promise) $byUser = new User(0, "Loading...");
		parent::__construct(
			"Warn Details",
			"Moderator: " . $byUser->getGamertag() . PHP_EOL .
				PHP_EOL .
				"Severe: " . ($this->entry->isSevere() ? "Yes" : "No") . PHP_EOL .
				PHP_EOL .
				"Type: " . $this->entry->getFormattedType() . PHP_EOL .
				"Reason: " . $this->entry->getReason() . PHP_EOL
		);
		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player) {
		$player->showModal(new MyWarnsUi($this->warns, $player->getUser()));
	}
}
