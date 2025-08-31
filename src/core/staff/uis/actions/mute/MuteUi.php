<?php

namespace core\staff\uis\actions\mute;

use core\AtPlayer;
use core\staff\uis\PanelUi;
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class MuteUi extends SimpleForm {

	public function __construct(string $message = "", bool $error = true) {
		parent::__construct(
			"Warn Menu",
			($message != "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") .
				"What would you like to do?"
		);

		$this->addButton(new Button("Mute Player"));
		$this->addButton(new Button("View Mutes"));
		$this->addButton(new Button("Go back"));
	}

	public function handle($response, AtPlayer $player) {
		match ($response) {
			0 => $player->showModal(new AddMuteUi()),
			1 => $player->showModal(new ViewMutesUi()),
			default => $player->showModal(new PanelUi()),
		};
	}

}