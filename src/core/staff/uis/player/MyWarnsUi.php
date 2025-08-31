<?php

namespace core\staff\uis\player;

use core\AtPlayer as Player;
use core\staff\entry\WarnEntry;
use core\staff\entry\WarnManager;
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\user\User;
use core\utils\TextFormat;

class MyWarnsUi extends SimpleForm {

	/** @var WarnEntry[] */
	public array $visibleWarns = [];

	/** @param WarnEntry[] $warns */
	public function __construct(public array $warns, public User $user) {
		foreach ($warns as $warn) {
			if ($warn->isRevoked()) continue;
			$this->visibleWarns[] = $warn;
			$reason = $warn->getReason();
			$this->addButton(new Button(TextFormat::YELLOW . (strlen($reason) > 32 ? substr($reason, 0, 32) . "..." : $reason) . PHP_EOL . TextFormat::DARK_GRAY . $warn->getFormattedWhen()));
		}

		parent::__construct(
			"Your warns",
			"You have a total of " . TextFormat::RED . count($this->warns) . TextFormat::WHITE . " warns. Select one from the list below to view more details!"
		);
	}

	public function handle($response, Player $player) {
		foreach ($this->visibleWarns as $key => $warn) {
			if ($response == $key) {
				$player->showModal(new ViewWarnUi($warn, $this->warns));
				return;
			}
		}
	}
}
