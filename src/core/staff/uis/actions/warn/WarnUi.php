<?php

namespace core\staff\uis\actions\warn;

use core\AtPlayer as Player;
use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;
use core\staff\uis\PanelUi;
use core\utils\TextFormat;

class WarnUi extends SimpleForm {

	public function __construct(string $message = "", bool $error = true) {
		parent::__construct(
			"Warn Menu",
			($message != "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") .
				"What would you like to do?"
		);

		$this->addButton(new Button("Warn player"));
		$this->addButton(new Button("View warns"));
		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player) {
		if ($response == 0) {
			$player->showModal(new AddWarnUi());
			return;
		}
		if ($response == 1) {
			$player->showModal(new ViewWarnsUi());
			return;
		}
		$player->showModal(new PanelUi());
	}
}
